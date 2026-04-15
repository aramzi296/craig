<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab');
        
        // Base stats
        $ownListings = \App\Models\Listing::where('user_id', $user->id)->get();
        $totalListings = $ownListings->count();
        $activeListings = $ownListings->where('is_active', true)->count();
        $featuredListings = $ownListings->where('is_featured', true)->count();
        
        // Listing display based on tab
        if ($tab == 'favorites') {
            $listings = $user->favorites()->with('categories')->latest()->get();
            $tableTitle = 'Iklan Favorit Saya';
        } elseif ($tab == 'my-listings') {
            $listings = \App\Models\Listing::where('user_id', $user->id)->with('categories')->latest()->get();
            $tableTitle = 'Kelola Iklan Saya';
        } else {
            // Overview (Home)
            $listings = \App\Models\Listing::where('user_id', $user->id)->with('categories')->latest()->take(5)->get();
            $tableTitle = 'Iklan Terbaru Saya';
        }

        return view('dashboard', compact('listings', 'totalListings', 'activeListings', 'featuredListings', 'tab', 'tableTitle'));
    }
}
