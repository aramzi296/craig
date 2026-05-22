<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Listing::query()->whereRaw('is_active = true')->notExpired();

        // Basic Filters
        if ($request->filled('location')) {
            $query->where('district_id', (int)$request->location);
        }

        if ($request->filled('category')) {
            $category = \App\Models\Category::where('slug', $request->category)->first();
            if ($category) {
                if ($category->parent_id === null) {
                    // Kategori Utama: ambil semua sub kategori di bawahnya
                    $subCategoryIds = $category->children()->pluck('id')->push($category->id);
                    $query->whereHas('categories', function($q) use ($subCategoryIds) {
                        $q->whereIn('categories.id', $subCategoryIds);
                    });
                } else {
                    // Sub Kategori spesifik
                    $query->whereHas('categories', function($q) use ($category) {
                        $q->where('categories.id', $category->id);
                    });
                }
            }
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->search($q);
            
            $recentListings = $query->with('district')
                ->orderBy('is_premium', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(24);
        } else {
            $recentListings = $query->with('district')
                ->orderBy('created_at', 'desc')
                ->orderBy('is_premium', 'desc')
                ->orderBy('listing_rank', 'asc')
                ->paginate(24);
        }

        return view('home', compact('recentListings'));
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

        $query = \App\Models\Listing::with(['categories.parent', 'tags', 'photos', 'user', 'comments.user', 'district'])
            ->withCount('views')
            ->where('slug', $slug);

        if ($code) {
            $query->where('activation_code', $code);
        } else {
            $query->whereRaw('is_active = true')->notExpired();
        }

        $listing = $query->firstOrFail();

        $relatedListings = \App\Models\Listing::with(['categories', 'tags'])
            ->whereHas('categories', function($q) use ($listing) {
                $q->whereIn('categories.id', $listing->categories->pluck('id'));
            })
            ->where('id', '!=', $listing->id)
            ->whereRaw('is_active = true')
            ->notExpired()
            ->latest()
            ->take(6)
            ->get();

        $sidebarPremiumListings = \App\Models\Listing::with(['categories', 'tags'])
            ->whereRaw('is_premium = true')
            ->whereRaw('is_active = true')
            ->notExpired()
            ->where('id', '!=', $listing->id)
            ->inRandomOrder()
            ->take(5)
            ->get();

        return view('listings.show', compact('listing', 'relatedListings', 'sidebarPremiumListings'));
    }

    public function categoriesDirectory()
    {
        $categories = \App\Models\Category::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->whereRaw('is_approved = true')
                  ->withCount(['listings' => function($query) {
                      $query->whereRaw('is_active = true')->notExpired();
                  }])
                  ->orderBy('sort_order');
            }])
            ->whereRaw('is_approved = true')
            ->orderBy('sort_order')
            ->get();

        return view('categories.directory', compact('categories'));
    }

    public function categories()
    {
        $categories = \App\Models\Tag::whereRaw('is_approved = true')
            ->whereHas('listings', function($q) {
                $q->whereRaw('is_active = true')->notExpired();
            })
            ->orderBy('name')
            ->get();
        return view('categories.index', compact('categories'));
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
            ->paginate(24);
            
        $categories = \App\Models\Category::whereRaw('is_approved = true')
            ->whereNotNull('parent_id')
            ->whereHas('listings', function($q) use ($id) {
                $q->where('user_id', $id)->whereRaw('is_active = true')->notExpired();
            })->orderBy('name')->get();
            
        $districts = \App\Models\District::orderBy('name')->get();

        return view('listings.search', [
            'listings' => $listings,
            'categories' => $categories,
            'districts' => $districts,
            'user' => $user,
            'isUserPage' => true
        ]);
    }

    public function tentang()
    {
        return view('tentang');
    }

    public function submitContact(Request $request, \App\Services\WhatsappService $whatsappService)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $whatsapp = \App\Models\User::normalizeWhatsappNumber($data['whatsapp']) ?? $data['whatsapp'];

        // Save to database
        \App\Models\ContactMessage::create([
            'name' => $data['name'],
            'whatsapp' => $whatsapp,
            'message' => $data['message'],
            'status' => 'unread',
        ]);

        $adminNumber2 = config('services.whatsapp.admin_number_2');
        
        $text = "*PESAN KONTAK BARU*\n\n";
        $text .= "*Nama:* " . $data['name'] . "\n";
        $text .= "*WA:* " . $whatsapp . "\n";
        $text .= "*Pesan:* " . $data['message'];

        // Send WhatsApp notification as a best-effort background action
        $whatsappService->sendMessage($adminNumber2, $text);

        return back()->with('success', 'Pesan Anda berhasil dikirim dan disimpan. Terima kasih telah menghubungi kami!');
    }
}
