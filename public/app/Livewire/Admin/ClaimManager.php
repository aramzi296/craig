<?php

namespace App\Livewire\Admin;

use App\Models\Listing;
use App\Models\ListingClaim;
use Livewire\Component;
use Livewire\WithPagination;

class ClaimManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $status = 'pending';

    public function approveClaim($id)
    {
        $claim = ListingClaim::findOrFail($id);
        
        // 1. Update listing's user owner
        $listing = Listing::findOrFail($claim->listing_id);
        $listing->update(['user_id' => $claim->user_id]);

        // 2. Update claim status
        $claim->update(['status' => 'approved']);

        // 3. Mark other claims for same listing as rejected or notify? Let's just update this one.
        
        session()->flash('message', 'Klaim telah disetujui. Kepemilikan listing dipindahkan.');
    }

    public function rejectClaim($id)
    {
        $claim = ListingClaim::findOrFail($id);
        $claim->update(['status' => 'rejected']);
        session()->flash('message', 'Klaim ini ditolak.');
    }

    public function render()
    {
        $claims = ListingClaim::with(['listing', 'user'])
            ->when($this->status, function($q) {
                if ($this->status !== 'all') {
                    $q->where('status', $this->status);
                }
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.claim-manager', [
            'claims' => $claims
        ])->layout('layouts.main');
    }
}
