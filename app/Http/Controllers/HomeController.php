<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        if ($request->filled('q')) {
            $listings = \App\Models\Listing::search($request->q)
                ->query(fn($q) => $q->with(['district', 'listingType', 'photos']));

            if ($request->filled('location')) {
                $listings->where('district_id', (int) $request->location);
            }

            if ($request->filled('type')) {
                $type = \App\Models\ListingType::where('slug', $request->type)->orWhere('id', $request->type)->first();
                if ($type) {
                    $listings->where('listing_type_id', (int) $type->id);
                }
            }

            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    // Meilisearch handles array filtering with '=' if configured as filterable
                    $listings->where('category_ids', (int) $category->id);
                }
            }

            $recentListings = $listings->paginate(12);
            $premiumListings = collect(); 
        } else {
            $query = \App\Models\Listing::query()->whereRaw('is_active = true')->notExpired();

            // Filter by District
            if ($request->filled('location')) {
                $query->where('district_id', $request->location);
            }

            // Filter by Type (Slug or ID)
            if ($request->filled('type')) {
                $type = \App\Models\ListingType::where('slug', $request->type)->orWhere('id', $request->type)->first();
                if ($type) {
                    $query->where('listing_type_id', $type->id);
                }
            }

            // Filter by Category
            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    $query->whereHas('categories', function($q) use ($category) {
                        $q->where('categories.id', $category->id);
                    });
                }
            }

            $premiumListings = (clone $query)->whereRaw('is_premium = true')->latest()->take(6)->get();

            $recentListings = $query->with('district')
                ->orderBy('created_at', 'desc')
                ->orderBy('is_premium', 'desc')
                ->orderBy('listing_rank', 'asc')
                ->paginate(12);
        }

        return view('home', compact('premiumListings', 'recentListings'));
    }

    public function search(\Illuminate\Http\Request $request)
    {
        if ($request->filled('q')) {
            $listings = \App\Models\Listing::search($request->q)
                ->query(fn($q) => $q->with(['district', 'listingType', 'photos']));

            if ($request->filled('location')) {
                $listings->where('district_id', (int) $request->location);
            }

            if ($request->filled('type')) {
                $type = \App\Models\ListingType::where('slug', $request->type)->orWhere('id', $request->type)->first();
                if ($type) {
                    $listings->where('listing_type_id', (int) $type->id);
                }
            }

            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    $listings->where('category_ids', (int) $category->id);
                }
            }

            $listings = $listings->paginate(20);
        } else {
            $query = \App\Models\Listing::query()->whereRaw('is_active = true')->notExpired();

            // Filter by District
            if ($request->filled('location')) {
                $query->where('district_id', $request->location);
            }

            // Filter by Type (Slug or ID)
            if ($request->filled('type')) {
                $type = \App\Models\ListingType::where('slug', $request->type)->orWhere('id', $request->type)->first();
                if ($type) {
                    $query->where('listing_type_id', $type->id);
                }
            }

            // Filter by Category
            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    $query->whereHas('categories', function($q) use ($category) {
                        $q->where('categories.id', $category->id);
                    });
                }
            }

            $listings = $query->orderBy('created_at', 'desc')
                ->orderBy('is_premium', 'desc')
                ->orderBy('listing_rank', 'asc')
                ->paginate(20);
        }

        // Fetch categories that have at least one active, non-expired listing AND are approved
        $categories = \App\Models\Category::whereRaw('is_approved = true')
            ->whereHas('listings', function($q) {
                $q->whereRaw('is_active = true')->notExpired();
            })->orderBy('name')->get();
        
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        $districts = \App\Models\District::orderBy('name')->get();

        return view('listings.search', compact('listings', 'categories', 'listingTypes', 'districts'));
    }

    public function show($slug)
    {
        // Find listing first to get ID
        $listingId = \App\Models\Listing::where('slug', $slug)->value('id');
        
        if ($listingId) {
            // Check if this IP has viewed this listing in the last 1 hour
            $recentView = \App\Models\ListingView::where('listing_id', $listingId)
                ->where('ip_address', request()->ip())
                ->where('created_at', '>', now()->subHour())
                ->exists();

            if (!$recentView) {
                // Record view in separate table for analytics
                \App\Models\ListingView::create([
                    'listing_id' => $listingId,
                    'ip_address' => request()->ip()
                ]);

                // Increment cache in listings table WITHOUT updating updated_at timestamp
                \Illuminate\Support\Facades\DB::table('listings')
                    ->where('id', $listingId)
                    ->increment('views_count');
            }
        }

        $code = request()->query('code');

        $query = \App\Models\Listing::with(['categories', 'listingType', 'photos', 'user', 'comments.user', 'district'])
            ->withCount('views')
            ->where('slug', $slug);

        if ($code) {
            $query->where('activation_code', $code);
        } else {
            $query->whereRaw('is_active = true')->notExpired();
        }

        $listing = $query->firstOrFail();

        $relatedListings = \App\Models\Listing::with(['categories', 'listingType'])
            ->whereHas('categories', function($q) use ($listing) {
                $q->whereIn('categories.id', $listing->categories->pluck('id'));
            })
            ->where('id', '!=', $listing->id)
            ->whereRaw('is_active = true')
            ->notExpired()
            ->latest()
            ->take(6)
            ->get();

        $sidebarPremiumListings = \App\Models\Listing::with(['categories', 'listingType'])
            ->whereRaw('is_premium = true')
            ->whereRaw('is_active = true')
            ->notExpired()
            ->where('id', '!=', $listing->id)
            ->inRandomOrder()
            ->take(5)
            ->get();

        return view('listings.show', compact('listing', 'relatedListings', 'sidebarPremiumListings'));
    }

    public function categories()
    {
        $categories = \App\Models\Category::whereRaw('is_approved = true')->orderBy('name')->get();

        
        $groupedCategories = $categories->groupBy(function ($item) {
            return strtoupper(substr($item->name, 0, 1));
        });

        return view('categories.index', compact('groupedCategories'));
    }

    public function userListings($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        $listings = \App\Models\Listing::query()
            ->where('user_id', $id)
            ->whereRaw('is_active = true')
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->orderBy('is_premium', 'desc')
            ->orderBy('listing_rank', 'asc')
            ->paginate(20);
            
        $categories = \App\Models\Category::whereRaw('is_approved = true')
            ->whereHas('listings', function($q) use ($id) {
                $q->where('user_id', $id)->whereRaw('is_active = true')->notExpired();
            })->orderBy('name')->get();
            
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        $districts = \App\Models\District::orderBy('name')->get();

        return view('listings.search', [
            'listings' => $listings,
            'categories' => $categories,
            'listingTypes' => $listingTypes,
            'districts' => $districts,
            'user' => $user,
            'isUserPage' => true
        ]);
    }

    public function bacaSaya()
    {
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        return view('baca-saya', compact('listingTypes'));
    }

    public function submitContact(Request $request, \App\Services\WhatsappService $whatsappService)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $adminNumber2 = config('services.whatsapp.admin_number_2');
        
        $text = "*PESAN KONTAK BARU*\n\n";
        $text .= "*Nama:* " . $data['name'] . "\n";
        $text .= "*WA:* " . $data['whatsapp'] . "\n";
        $text .= "*Pesan:* " . $data['message'];

        $response = $whatsappService->sendMessage($adminNumber2, $text);

        if ($response) {
            return back()->with('success', 'Pesan Anda berhasil dikirim ke Admin. Kami akan segera menghubungi Anda.');
        }

        return back()->with('error', 'Gagal mengirim pesan. Silakan coba hubungi kami langsung via WhatsApp.');
    }
}
