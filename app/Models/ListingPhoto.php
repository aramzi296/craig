<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingPhoto extends Model
{
    protected $fillable = [
        'listing_id',
        'photo_path',
        'thumbnail_path',
        'file_type',
        'file_size',
        'collection',
        'ik_file_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function getUrl()
    {
        if (str_starts_with($this->photo_path, 'http')) {
            return $this->photo_path;
        }
        return asset('storage/' . ltrim($this->photo_path, '/'));
    }

    public function getThumbnailUrl()
    {
        if (str_starts_with($this->thumbnail_path, 'http')) {
            return $this->thumbnail_path;
        }
        return asset('storage/' . ltrim($this->thumbnail_path, '/'));
    }
}
