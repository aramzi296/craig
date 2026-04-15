<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $categories = \App\Models\Category::orderBy('sort_order')->get();
        $selectedCategory = null;

        $query = \App\Models\Listing::query()->where('is_active', true);

        if ($request->has('category')) {
            $selectedCategory = \App\Models\Category::where('slug', $request->category)->first();
            if ($selectedCategory) {
                $query->whereHas('categories', function($q) use ($selectedCategory) {
                    $q->where('categories.id', $selectedCategory->id);
                });
            }
        }

        $featuredListings = (clone $query)->where('is_featured', true)->latest()->take(6)->get();
        $recentListings = $query->latest()->paginate(12);

        return view('home', compact('categories', 'featuredListings', 'recentListings', 'selectedCategory'));
    }

    public function show($slug)
    {
        $listing = \App\Models\Listing::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $relatedListings = \App\Models\Listing::whereHas('categories', function($q) use ($listing) {
                $q->whereIn('categories.id', $listing->categories->pluck('id'));
            })
            ->where('id', '!=', $listing->id)
            ->where('is_active', true)
            ->latest()
            ->take(4)
            ->get();

        return view('listings.show', compact('listing', 'relatedListings'));
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
