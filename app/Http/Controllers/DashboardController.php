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
        $premiumListings = $ownListings->where('is_premium', true)->count();
        
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

        return view('dashboard', compact('listings', 'totalListings', 'activeListings', 'premiumListings', 'tab', 'tableTitle'));
    }

    public function premiumUpgrade($listing_id)
    {
        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($listing_id);

        if ($listing->is_premium) {
            return redirect()->route('dashboard')->with('error', 'Iklan ini sudah berstatus Premium.');
        }

        if ($listing->hasPendingPremiumRequest()) {
            return redirect()->route('dashboard')->with('error', 'Iklan ini sedang dalam proses verifikasi Premium oleh admin.');
        }

        $packages = \App\Models\PremiumPackage::where('is_active', true)->orderBy('price')->get();
        $uniqueCode = rand(100, 999);
        
        return view('listings.premium_upgrade', compact('listing', 'packages', 'uniqueCode'));
    }

    public function processPremiumRequest(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'package_id' => 'required|exists:premium_packages,id',
            'unique_code' => 'required|integer|min:0',
        ]);

        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($request->listing_id);

        if ($listing->is_premium || $listing->hasPendingPremiumRequest()) {
            return redirect()->route('dashboard')->with('error', 'Permintaan premium tidak dapat diproses.');
        }

        \App\Models\PremiumRequest::create([

            'user_id' => auth()->id(),
            'listing_id' => $listing->id,
            'package_id' => $request->package_id,
            'unique_code' => $request->unique_code,
            'status' => 'pending',
        ]);


        return redirect()->route('dashboard.premium.thankyou');
    }
}


