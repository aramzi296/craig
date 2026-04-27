<?php

namespace App\Observers;

use App\Models\Listing;
use App\Models\ListingType;

class ListingObserver
{
    /**
     * Handle the Listing "creating" event.
     */
    public function creating(Listing $listing): void
    {
        // ── Set expires_at ────────────────────────────────────────────────────
        if (!$listing->expires_at) {
            $typeId = $listing->listing_type_id;
            $type = ListingType::find($typeId);

            $isPremium = ($type && $type->slug === 'premium') || $listing->is_premium;

            $days = $isPremium
                ? get_setting('expire_iklan_premium', 30)
                : get_setting('expire_iklan', 30);

            $listing->expires_at = now()->addDays((int)$days);
        }

        // ── Set listing_rank ──────────────────────────────────────────────────
        $typeId = $listing->listing_type_id;
        $type = $typeId ? ListingType::find($typeId) : null;
        $isPremium = ($type && $type->slug === 'premium') || $listing->is_premium;

        if ($isPremium) {
            // Iklan premium selalu mendapat rank 100 (tampil paling atas)
            $listing->listing_rank = 100;
        } else {
            // Iklan gratis: cari listing_rank tertinggi milik user ini (iklan gratis),
            // lalu tambah 1000. Jika belum ada iklan gratis, mulai dari 1000.
            $lastRank = Listing::where('user_id', $listing->user_id)
                ->whereRaw('is_premium = false')
                ->max('listing_rank');

            $listing->listing_rank = $lastRank ? $lastRank + 1000 : 1000;
        }
    }
}
