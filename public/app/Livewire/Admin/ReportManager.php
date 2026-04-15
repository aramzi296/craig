<?php

namespace App\Livewire\Admin;

use App\Models\Listing;
use App\Models\ListingReport;
use Livewire\Component;
use Livewire\WithPagination;

class ReportManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $status = 'pending';

    public function resolveReport($id)
    {
        $report = ListingReport::findOrFail($id);
        $report->update(['status' => 'resolved']);
        session()->flash('message', 'Laporan telah ditandai sebagai selesai.');
    }

    public function deleteListing($listingId, $reportId)
    {
        $listing = Listing::findOrFail($listingId);
        $listing->delete();

        $report = ListingReport::findOrFail($reportId);
        $report->update(['status' => 'resolved']); // Otomatis resolved karena listing sudah dihapus

        session()->flash('message', 'Listing berhasil dihapus dan laporan ditandai selesai.');
    }

    public function dismissReport($id)
    {
        $report = ListingReport::findOrFail($id);
        $report->update(['status' => 'dismissed']);
        session()->flash('message', 'Laporan diabaikan.');
    }

    public function render()
    {
        $reports = ListingReport::with(['listing', 'user'])
            ->when($this->status, function($q) {
                if ($this->status !== 'all') {
                    $q->where('status', $this->status);
                }
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.report-manager', [
            'reports' => $reports
        ])->layout('layouts.main');
    }
}
