<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Listing;

class CommentController extends Controller
{
    public function store(Request $request, $listingId)
    {
        $listing = Listing::findOrFail($listingId);

        // Check if comments are allowed
        if ($listing->comment_visibility == 0) {
            return back()->with('error', 'Komentar dinonaktifkan untuk postingan ini.');
        }

        if ($listing->comment_visibility == 1 && !auth()->check()) {
            return back()->with('error', 'Anda harus login untuk menulis komentar.');
        }

        $maxChars = get_setting('max_karakter_komentar', 250);
        $request->validate([
            'content' => 'required|string|max:' . $maxChars,
        ], [
            'content.required' => 'Komentar tidak boleh kosong.',
            'content.max' => 'Komentar maksimal ' . $maxChars . ' karakter.',
        ]);

        Comment::create([
            'listing_id' => $listing->id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return back()->with('success', 'Komentar Anda berhasil diposting.');
    }
}
