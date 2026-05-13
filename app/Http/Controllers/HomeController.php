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
            $tag = \App\Models\Tag::where('slug', $request->category)->first();
            if ($tag) {
                $query->whereHas('tags', function($q) use ($tag) {
                    $q->where('tags.id', $tag->id);
                });
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


    public function listing(Request $request)
    {
        $query = \App\Models\Listing::query()->whereRaw('is_active = true')->notExpired();

        if ($request->filled('q')) {
            $query->search($request->q);
        }

        $selectedType = null;
        if ($request->filled('type')) {
            $selectedType = \App\Models\ListingType::find($request->type);
            if ($selectedType) {
                $query->where('listing_type_id', $selectedType->id);
            }
        }

        $listings = $query->with('district')
            ->withCount('photos')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('listings.listing', compact('listings', 'selectedType'));
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

        $query = \App\Models\Listing::with(['tags', 'listingType', 'photos', 'user', 'comments.user', 'district'])
            ->withCount('views')
            ->where('slug', $slug);

        if ($code) {
            $query->where('activation_code', $code);
        } else {
            $query->whereRaw('is_active = true')->notExpired();
        }

        $listing = $query->firstOrFail();

        $relatedListings = \App\Models\Listing::with(['tags', 'listingType'])
            ->whereHas('tags', function($q) use ($listing) {
                $q->whereIn('tags.id', $listing->tags->pluck('id'));
            })
            ->where('id', '!=', $listing->id)
            ->whereRaw('is_active = true')
            ->notExpired()
            ->latest()
            ->take(6)
            ->get();

        $sidebarPremiumListings = \App\Models\Listing::with(['tags', 'listingType'])
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
        $categories = \App\Models\Tag::whereRaw('is_approved = true')->orderBy('name')->get();
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
            
        $categories = \App\Models\Tag::whereRaw('is_approved = true')
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

    public function tentang()
    {
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        return view('tentang', compact('listingTypes'));
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
