<?php

namespace App\Livewire;

use App\Models\ListingReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReportListing extends Component
{
    public $listingId;
    public $reason = '';
    public $comment = '';
    public $showModal = false;
    public $isSubmitted = false;
    public $hideButton = false;

    protected $listeners = [
        'openReportModal' => 'openModal'
    ];

    public function openModal()
    {
        $this->showModal = true;
    }

    protected $rules = [
        'reason' => 'required|string',
        'comment' => 'nullable|string|max:500',
    ];

    public function mount($listingId)
    {
        $this->listingId = $listingId;
    }

    public function submitReport()
    {
        $this->validate();

        ListingReport::create([
            'listing_id' => $this->listingId,
            'user_id' => Auth::user()?->id,
            'reason' => $this->reason,
            'comment' => $this->comment,
        ]);

        $this->isSubmitted = true;
        $this->showModal = false;
        $this->reset(['reason', 'comment']);
        
        session()->flash('reportSuccess', 'Laporan Anda telah dikirim. Terima kasih atas partisipasi Anda.');
    }

    public function render()
    {
        return view('livewire.report-listing');
    }
}
