<?php

namespace App\Livewire\User;

use App\Models\PremiumTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BillingIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function cancelTransaction($id)
    {
        $tx = PremiumTransaction::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $tx->status = 'cancelled';
        $tx->save();

        session()->flash('success', 'Transaksi berhasil dibatalkan.');
        $this->dispatch('swal', ['title' => 'Transaksi dibatalkan.', 'icon' => 'success']);
    }

    public function render()
    {
        $transactions = PremiumTransaction::with(['listing', 'premiumTariff'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.user.billing-index', [
            'transactions' => $transactions,
        ])->layout('layouts.main');
    }
}
