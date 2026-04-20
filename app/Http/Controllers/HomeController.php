<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        
        $query = \App\Models\Listing::query()->where('is_active', true)->notExpired();

        $premiumListings = (clone $query)->where('is_premium', true)->latest()->take(6)->get();

        $recentListings = $query->latest()->paginate(12);

        return view('home', compact('listingTypes', 'premiumListings', 'recentListings'));
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

        // Filter by Category
        if ($request->filled('category')) {
            $category = \App\Models\Category::where('slug', $request->category)->first();
            if ($category) {
                $query->whereHas('categories', function($q) use ($category) {
                    $q->where('categories.id', $category->id);
                });
            }
        }

        // Filter by Type (Slug or ID)
        if ($request->filled('type')) {
            $type = \App\Models\ListingType::where('slug', $request->type)->orWhere('id', $request->type)->first();
            if ($type) {
                $query->where('listing_type_id', $type->id);
            }
        }

        // Filter by Location
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        $listings = $query->latest()->paginate(20);
        
        $categories = \App\Models\Category::orderBy('name')->get();
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        $locations = ['Batam Centre', 'Nagoya', 'Sekupang', 'Batu Ampar', 'Bengkong', 'Sei Beduk', 'Nongsa', 'Sagulung', 'Batu Aji'];

        return view('listings.search', compact('listings', 'categories', 'listingTypes', 'locations'));
    }

    public function show($slug)
    {
        $listing = \App\Models\Listing::with(['categories', 'listingType', 'photos', 'user', 'comments.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->notExpired()
            ->firstOrFail();
        
        // Increment view count
        $listing->increment('views_count');

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
}
