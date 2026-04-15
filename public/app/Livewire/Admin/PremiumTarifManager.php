<?php

namespace App\Livewire\Admin;

use App\Models\PremiumTariff;
use App\Models\PremiumTransaction;
use Livewire\Component;

class PremiumTarifManager extends Component
{
    public string $listingType = 'usaha';

    public string $tab = 'tariffs'; // tariffs | transactions
    public string $transactionFilter = 'needs_review'; // needs_review | all | pending | active | cancelled

    public ?int $editingId = null;
    public bool $showForm = false;

    public string $planName = '';
    public int $durationDays = 365;
    public int $price = 25000;
    public bool $isActive = true;

    public function openCreate(string $type): void
    {
        $this->listingType = $type;
        $this->editingId = null;
        $this->showForm = true;
        $this->planName = '';
        $this->durationDays = 365;
        $this->price = 25000;
        $this->isActive = true;
    }

    public function openEdit(int $id): void
    {
        $tariff = PremiumTariff::findOrFail($id);
        $this->editingId = $tariff->id;
        $this->listingType = $tariff->listing_type;
        $this->planName = (string) ($tariff->plan_name ?? '');
        $this->durationDays = (int) $tariff->duration_days;
        $this->price = (int) $tariff->price;
        $this->isActive = (bool) $tariff->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'listingType' => 'required|in:usaha,lapak,mencari_kerja,lowongan,proyek',
            'planName' => 'nullable|string|max:255',
            'durationDays' => 'required|integer|min:1|max:3650',
            'price' => 'required|integer|min:0|max:1000000000',
            'isActive' => 'boolean',
        ]);

        $payload = [
            'listing_type' => $this->listingType,
            'plan_name' => $this->planName !== '' ? $this->planName : null,
            'duration_days' => $this->durationDays,
            'price' => $this->price,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            PremiumTariff::findOrFail($this->editingId)->update($payload);
        } else {
            PremiumTariff::create($payload);
        }

        $this->showForm = false;
        $this->editingId = null;
        session()->flash('success', 'Tarif premium berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        PremiumTariff::findOrFail($id)->delete();
        session()->flash('success', 'Tarif premium berhasil dihapus.');
    }

    public function markActiveToPending(int $transactionId): void
    {
        $tx = PremiumTransaction::findOrFail($transactionId);

        if ($tx->status !== 'active') {
            session()->flash('warning', 'Transaksi ini bukan premium aktif.');
            return;
        }

        $tx->status = 'pending';
        $tx->premium_expires_at = null;
        $tx->admin_reviewed_at = now();
        $tx->save();

        session()->flash('success', 'Transaksi premium ditandai kembali ke pending.');
    }

    public function cancelTransaction(int $transactionId): void
    {
        $tx = PremiumTransaction::findOrFail($transactionId);

        if ($tx->status === 'cancelled') {
            session()->flash('warning', 'Transaksi premium sudah dibatalkan.');
            return;
        }

        $tx->status = 'cancelled';
        $tx->premium_expires_at = null;
        $tx->admin_reviewed_at = null;
        $tx->save();

        session()->flash('success', 'Transaksi premium dibatalkan.');
    }

    public function render()
    {
        $tariffs = PremiumTariff::query()
            ->where('listing_type', $this->listingType)
            ->orderByDesc('is_active')
            ->orderByDesc('duration_days')
            ->get();

        $transactionsQuery = PremiumTransaction::with(['listing', 'user', 'premiumTariff'])
            ->where('listing_type', $this->listingType)
            ->orderByDesc('id');

        $transactionsQuery->when($this->transactionFilter === 'needs_review', function ($q) {
            // Transactions active but not reviewed, plus pending ones.
            $q->where(function ($inner) {
                $inner->where('status', 'pending')
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'active')->whereNull('admin_reviewed_at');
                    });
            });
        });

        $transactionsQuery->when($this->transactionFilter === 'pending', fn ($q) => $q->where('status', 'pending'));
        $transactionsQuery->when($this->transactionFilter === 'active', fn ($q) => $q->where('status', 'active'));
        $transactionsQuery->when($this->transactionFilter === 'cancelled', fn ($q) => $q->where('status', 'cancelled'));

        if ($this->transactionFilter === 'all') {
            // no extra where
        }

        $transactions = $transactionsQuery->get();

        return view('livewire.admin.premium-tarif-manager', [
            'tariffs' => $tariffs,
            'transactions' => $transactions,
        ])->layout('layouts.main');
    }
}

