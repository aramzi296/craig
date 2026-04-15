<?php

namespace App\Livewire\User;

use App\Models\ListingReview;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class MyReviewIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function delete($id)
    {
        $review = ListingReview::where('user_id', Auth::id())->findOrFail($id);
        $review->delete();
        session()->flash('message', 'Ulasan Anda berhasil dihapus.');
    }

    public function render()
    {
        $reviews = ListingReview::with('listing')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.user.my-review-index', [
            'reviews' => $reviews
        ])->layout('layouts.main');
    }
}
