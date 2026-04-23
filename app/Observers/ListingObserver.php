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
        if (!$listing->expires_at) {
            $typeId = $listing->listing_type_id;
            $type = ListingType::find($typeId);
            
            $isPremium = ($type && $type->slug === 'premium') || $listing->is_premium;
            
            $days = $isPremium 
                ? get_setting('expire_iklan_premium', 30) 
                : get_setting('expire_iklan', 30);
            
            $listing->expires_at = now()->addDays($days);
        }
    }
}
