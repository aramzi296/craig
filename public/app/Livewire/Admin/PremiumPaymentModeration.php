<?php

namespace App\Livewire\Admin;

use App\Models\PremiumTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class PremiumPaymentModeration extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $rejection_reason;
    public $selected_id;

    public function approve($id)
    {
        $tx = PremiumTransaction::findOrFail($id);
        $tx->update([
            'status' => 'active',
            'admin_reviewed_at' => now(),
            'rejection_reason' => null
        ]);

        session()->flash('success', 'Pembayaran premium berhasil dikonfirmasi.');
    }

    public function openRejectModal($id)
    {
        $this->selected_id = $id;
        $this->rejection_reason = '';
        $this->dispatch('show-reject-modal');
    }

    public function reject()
    {
        $this->validate([
            'rejection_reason' => 'required|min:5',
        ]);

        $tx = PremiumTransaction::findOrFail($this->selected_id);
        $tx->update([
            'status' => 'rejected',
            'admin_reviewed_at' => now(),
            'rejection_reason' => $this->rejection_reason
        ]);

        $this->dispatch('hide-reject-modal');
        session()->flash('success', 'Pembayaran premium telah ditolak.');
    }

    public function render()
    {
        $transactions = PremiumTransaction::with(['user', 'listing', 'premiumTariff'])
            ->whereIn('status', ['waiting_confirmation', 'active', 'rejected'])
            ->latest()
            ->paginate(15);

        return view('livewire.admin.premium-payment-moderation', [
            'transactions' => $transactions,
        ])->layout('layouts.main');
    }
}
