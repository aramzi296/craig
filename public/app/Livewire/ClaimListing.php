<?php

namespace App\Livewire;

use App\Models\Listing;
use App\Models\ListingClaim;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClaimListing extends Component
{
    public $listingId;
    public $evidence = '';
    public $hasClaimed = false;
    public $isOwner = false;
    public $showModal = false;

    protected $rules = [
        'evidence' => 'required|string|min:20|max:1000',
    ];

    public function mount($listingId)
    {
        $this->listingId = $listingId;
        $listing = Listing::findOrFail($listingId);
        
        if (Auth::check()) {
            if ($listing->user_id === Auth::id()) {
                $this->isOwner = true;
            } else {
                $this->hasClaimed = ListingClaim::where('listing_id', $listingId)
                    ->where('user_id', Auth::id())
                    ->where('status', 'pending')
                    ->exists();
            }
        }
    }

    public function submitClaim()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->isOwner || $this->hasClaimed) {
            return;
        }

        $this->validate();

        ListingClaim::create([
            'listing_id' => $this->listingId,
            'user_id' => Auth::id(),
            'evidence' => $this->evidence,
        ]);

        $this->hasClaimed = true;
        $this->showModal = false;
        $this->reset(['evidence']);
        
        session()->flash('claimSuccess', 'Permintaan klaim Anda telah dikirim. Admin akan segera meninjau bukti Anda.');
    }

    public function render()
    {
        $listing = Listing::find($this->listingId);
        return view('livewire.claim-listing', [
            'listingType' => $listing->type
        ]);
    }
}
