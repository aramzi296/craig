<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle($id)
    {
        $user = auth()->user();
        $listing = \App\Models\Listing::findOrFail($id);

        if ($user->favorites()->where('listing_id', $id)->exists()) {
            $user->favorites()->detach($id);
            $message = 'Listing dihapus dari favorit.';
        } else {
            $user->favorites()->attach($id);
            $message = 'Listing ditambahkan ke favorit!';
        }

        return back()->with('success', $message);
    }
}
