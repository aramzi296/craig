<?php

namespace App\Livewire\User;

use App\Models\Listing;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class UsahaListingIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected string $listingType = 'usaha';


    public function delete($id)
    {
        $listing = Listing::findOrFail($id);

        // Ensure user owns the listing
        if ($listing->user_id === Auth::id()) {
            $listing->delete();
            session()->flash('success', 'Listing berhasil dihapus.');
            $this->dispatch('swal', ['title' => 'Listing berhasil dihapus.', 'type' => 'toast', 'icon' => 'success']);
        } else {
            session()->flash('error', 'Akses ditolak.');
            $this->dispatch('swal', ['title' => 'Akses ditolak.', 'type' => 'toast', 'icon' => 'error']);
        }
    }

    public function render()
    {
        $userId = Auth::id();

        $listings = Listing::with('categories', 'district')
            ->where('user_id', Auth::id())
            ->where('type', $this->listingType)
            ->latest()
            ->paginate(10);

        return view('livewire.user.listing-index', [
            'listings' => $listings,
            'heading' => 'Daftar Usaha Anda',
            'createRouteName' => 'user.usaha.listing.form',
            'editRouteName' => 'user.usaha.listing.edit',
            'listingType' => $this->listingType,
        ])->layout('layouts.main');
    }
}

