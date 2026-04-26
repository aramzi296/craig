<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Listing::query()->where('is_active', true)->notExpired();

        // Filter by Keyword
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

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

        $premiumListings = (clone $query)->where('is_premium', true)->latest()->take(6)->get();

        $recentListings = $query->with('district')->latest()->paginate(12);

        return view('home', compact('premiumListings', 'recentListings'));
    }

    public function search(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Listing::query()->where('is_active', true)->notExpired();

        // Filter by Keyword
        if ($request->filled('q')) {
            $keyword = $request->q;
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

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

        // Fetch categories that have at least one active, non-expired listing
        $categories = \App\Models\Category::whereHas('listings', function($q) {
            $q->where('is_active', true)->notExpired();
        })->orderBy('name')->get();

        // Filter by Category (Apply this LAST to the listings query only)
        if ($request->filled('category')) {
            $category = \App\Models\Category::where('slug', $request->category)->first();
            if ($category) {
                $query->whereHas('categories', function($q) use ($category) {
                    $q->where('categories.id', $category->id);
                });
                
                // Ensure the selected category stays in the list even if it has no results 
                // (though with this logic, it should have results if it's selected)
            }
        }

        $listings = $query->orderBy('is_premium', 'desc')->latest()->paginate(20);
        
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

        $listing = \App\Models\Listing::with(['categories', 'listingType', 'photos', 'user', 'comments.user', 'district'])
            ->withCount('views')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->notExpired()
            ->firstOrFail();

        $relatedListings = \App\Models\Listing::with(['categories', 'listingType'])
            ->whereHas('categories', function($q) use ($listing) {
                $q->whereIn('categories.id', $listing->categories->pluck('id'));
            })
            ->where('id', '!=', $listing->id)
            ->where('is_active', true)
            ->notExpired()
            ->latest()
            ->take(6)
            ->get();

        $sidebarPremiumListings = \App\Models\Listing::with(['categories', 'listingType'])
            ->where('is_premium', true)
            ->where('is_active', true)
            ->notExpired()
            ->where('id', '!=', $listing->id)
            ->inRandomOrder()
            ->take(5)
            ->get();

        return view('listings.show', compact('listing', 'relatedListings', 'sidebarPremiumListings'));
    }

    public function categories()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        
        $groupedCategories = $categories->groupBy(function ($item) {
            return strtoupper(substr($item->name, 0, 1));
        });

        return view('categories.index', compact('groupedCategories'));
    }

    public function bacaSaya()
    {
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        return view('baca-saya', compact('listingTypes'));
    }
}
