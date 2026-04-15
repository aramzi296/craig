<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;

class ListingIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $showOtpModal = false;
    public $selectedListingCode = '';
    public $selectedListingId = null;
    public $selectedListingWhatsapp = '';

    public function showActivationModal($listingId)
    {
        $listing = Listing::findOrFail($listingId);
        $this->selectedListingId = $listingId;
        
        // Generate random 6-digit OTP
        $otp = (string) random_int(100000, 999999);
        
        // Store in meta
        $meta = $listing->meta ?? [];
        $meta['lapak_otp'] = $otp;
        $meta['lapak_otp_expires_at'] = now()->addMinutes(30)->toDateTimeString();
        $listing->update(['meta' => $meta]);

        $this->selectedListingCode = $otp;
        $this->selectedListingWhatsapp = $listing->whatsapp;
        $this->showOtpModal = true;
    }

    public function closeModal()
    {
        $this->showOtpModal = false;
    }


    public function delete($id)
    {
        $listing = Listing::findOrFail($id);
        
        // Ensure user owns the listing
        if ($listing->user_id === Auth::id()) {
            $listing->delete();
            session()->flash('success', 'Listing berhasil dihapus.');
        } else {
            session()->flash('error', 'Akses ditolak.');
        }
    }

    public function render()
    {
        $userId = Auth::id();
        $listings = Listing::with('categories', 'district')
                        ->where('user_id', $userId)
                        ->latest()
                        ->paginate(10);

        return view('livewire.user.listing-index', [
            'listings' => $listings,
            'heading' => 'Semua Listing Anda',
            'createRouteName' => 'go-online',
            'editRouteName' => 'user.listing.edit',
            'listingType' => 'all',
        ])->layout('layouts.main');
    }
}
