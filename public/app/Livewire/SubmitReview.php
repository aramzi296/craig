<?php

namespace App\Livewire;

use App\Models\Listing;
use App\Models\ListingReview;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SubmitReview extends Component
{
    public $listingId;
    public $rating = 5;
    public $comment = '';
    public $hasReviewed = false;
    public $listingOwner = false;

    protected $rules = [
        'rating' => 'required|integer|min:3|max:5',
        'comment' => 'required|string|min:5|max:1000',
    ];

    public function mount($listingId)
    {
        $this->listingId = $listingId;
        $listing = Listing::findOrFail($listingId);
        
        if (Auth::check()) {
            if ($listing->user_id === Auth::id() && !Auth::user()->isAdmin()) {
                $this->listingOwner = true;
            } else {
                $this->hasReviewed = ListingReview::where('listing_id', $this->listingId)
                    ->where('user_id', Auth::id())
                    ->exists() && !Auth::user()->isAdmin();
            }
        }
    }

    public function saveReview()
    {
        \Illuminate\Support\Facades\Log::info('SubmitReview: saveReview called, rating = ' . $this->rating . ', comment = ' . $this->comment);
        
        $this->validate();

        try {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            if (($this->listingOwner || $this->hasReviewed) && !Auth::user()->isAdmin()) {
                return;
            }

            ListingReview::create([
                'listing_id' => $this->listingId,
                'user_id' => Auth::id(),
                'author_name' => Auth::user()->name ?? 'User',
                'rating' => $this->rating,
                'comment' => $this->comment,
            ]);

            $this->hasReviewed = true;
            session()->flash('reviewMsg', 'Ulasan Anda berhasil ditambahkan! Terima kasih.');
            
            $listing = Listing::find($this->listingId);
            return redirect()->route($listing->type === 'lapak' ? 'lapak.listing.show' : 'usaha.listing.show', $listing->slug);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SubmitReview error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            session()->flash('reviewMsg', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.submit-review');
    }
}
