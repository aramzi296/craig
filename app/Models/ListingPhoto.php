<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingPhoto extends Model
{
    protected $fillable = [
        'listing_id',
        'photo_path',
        'thumbnail_path',
        'collection',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
