<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiumTransaction extends Model
{
    protected $table = 'premium_transactions';

    protected $guarded = [];

    protected $casts = [
        'premium_expires_at' => 'datetime',
        'admin_reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function premiumTariff(): BelongsTo
    {
        return $this->belongsTo(PremiumTariff::class, 'premium_tariff_id');
    }

    public function isActive(): bool
    {
        if (!in_array($this->status, ['active', 'waiting_confirmation'])) {
            return false;
        }

        return (bool) ($this->premium_expires_at && $this->premium_expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->status === 'active'
            && (bool) ($this->premium_expires_at && $this->premium_expires_at->isPast());
    }
}

