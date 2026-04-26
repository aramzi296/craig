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
        $totalViews = $ownListings->sum('views_count');
        
        // Listing display based on tab
        if ($tab == 'favorites') {
            $listings = $user->favorites()->with('categories')->withCount('comments')->latest()->paginate(10)->withQueryString();
            $tableTitle = 'Iklan Favorit Saya';
        } elseif ($tab == 'my-listings') {
            $listings = \App\Models\Listing::where('user_id', $user->id)->with('categories')->withCount('comments')->latest()->paginate(10)->withQueryString();
            $tableTitle = 'Kelola Iklan Saya';
        } else {
            // Overview (Home)
            $listings = \App\Models\Listing::where('user_id', $user->id)->with('categories')->withCount('comments')->latest()->paginate(5)->withQueryString();
            $tableTitle = 'Iklan Terbaru Saya';
        }

        // Unused Premium Packages
        $unusedPremiumRequests = \App\Models\PremiumRequest::where('user_id', $user->id)
            ->whereNull('listing_id')
            ->whereIn('status', ['pending', 'active'])
            ->with('package')
            ->get();

        return view('dashboard', compact(
            'listings', 
            'totalListings', 
            'activeListings', 
            'premiumListings', 
            'totalViews', 
            'tab', 
            'tableTitle',
            'unusedPremiumRequests'
        ));
    }

    public function premiumUpgrade($listing_id = null)
    {
        $listing = null;
        if ($listing_id) {
            $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($listing_id);

            $hasActivePremium = \App\Models\PremiumRequest::where('listing_id', $listing->id)
                ->whereRaw('status = \'active\'')
                ->where('expires_at', '>', now())
                ->exists();

            if ($hasActivePremium) {
                return redirect()->route('dashboard')->with('error', 'Iklan ini sudah memiliki paket Premium yang aktif.');
            }

            if ($listing->hasPendingPremiumRequest()) {
                return redirect()->route('dashboard')->with('error', 'Iklan ini sedang dalam proses verifikasi Premium oleh admin.');
            }
        }

        $packages = \App\Models\PremiumPackage::whereRaw('is_active = true')->orderBy('price')->get();
        $uniqueCode = rand(100, 999);
        
        return view('listings.premium_upgrade', compact('listing', 'packages', 'uniqueCode'));
    }

    public function processPremiumRequest(Request $request)
    {
        $request->validate([
            'listing_id' => 'nullable|exists:listings,id',
            'package_id' => 'required|exists:premium_packages,id',
            'unique_code' => 'required|integer|min:0',
        ]);

        if ($request->listing_id) {
            $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($request->listing_id);

            $hasExistingRequest = \App\Models\PremiumRequest::where('listing_id', $listing->id)
                ->whereIn('status', ['pending', 'active'])
                ->exists();

            if ($hasExistingRequest) {
                return redirect()->route('dashboard')->with('error', 'Permintaan premium untuk iklan ini sudah ada atau sedang diproses.');
            }
        }

        \App\Models\PremiumRequest::create([
            'user_id' => auth()->id(),
            'listing_id' => $request->listing_id,
            'package_id' => $request->package_id,
            'unique_code' => $request->unique_code,
            'status' => 'pending',
        ]);

        if ($request->listing_id) {
            \App\Models\Listing::where('id', $request->listing_id)->update(['is_premium' => \DB::raw('true')]);
        }

        return redirect()->route('dashboard.premium.thankyou');
    }

    public function applyPremiumRequest(Request $request)
    {
        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'premium_request_id' => 'required|exists:premium_requests,id',
        ]);

        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($request->listing_id);
        $premiumRequest = \App\Models\PremiumRequest::where('user_id', auth()->id())
            ->where('id', $request->premium_request_id)
            ->where('status', 'active')
            ->whereNull('listing_id')
            ->firstOrFail();

        // Check if listing already has active/pending premium
        if ($listing->is_premium || $listing->hasPendingPremiumRequest()) {
            return redirect()->route('dashboard')->with('error', 'Iklan ini sudah premium atau sedang dalam proses.');
        }

        $premiumRequest->update([
            'listing_id' => $listing->id,
            'expires_at' => now()->addDays($premiumRequest->package->duration_days)
        ]);

        $listing->update(['is_premium' => \DB::raw('true')]);

        return redirect()->route('dashboard')->with('success', 'Paket Premium berhasil diterapkan pada iklan Anda.');
    }
}
