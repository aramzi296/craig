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
        'ik_file_id',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function getUrl()
    {
        if (str_starts_with($this->photo_path, '/listings/')) {
            return rtrim(config('services.imagekit.url_endpoint'), '/') . $this->photo_path . '?tr=w-1000';
        }
        return asset('storage/' . $this->photo_path);
    }

    public function getThumbnailUrl()
    {
        if (str_starts_with($this->thumbnail_path, '/listings/')) {
            return rtrim(config('services.imagekit.url_endpoint'), '/') . $this->thumbnail_path . '?tr=w-200,h-200,fo-auto';
        }
        return asset('storage/' . $this->thumbnail_path);
    }
}
