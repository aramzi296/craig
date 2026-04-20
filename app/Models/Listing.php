<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'user_id', 'listing_type_id', 'title', 'slug', 'description', 
        'price', 'location', 'is_featured', 'is_premium', 'is_active', 'features', 
        'whatsapp_visibility', 'comment_visibility'
    ];

    protected $casts = [
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function listingType()
    {
        return $this->belongsTo(ListingType::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'listing_id', 'user_id')->withTimestamps();
    }

    public function photos()
    {
        return $this->hasMany(ListingPhoto::class);
    }

    public function getImageUrl()
    {
        $photo = $this->photos()->where('collection', 'foto_fitur')->first();
        if ($photo) {
            return asset('storage/' . $photo->photo_path);
        }
        return null;
    }

    public function getThumbnailUrl()
    {
        $photo = $this->photos()->where('collection', 'foto_fitur')->first();
        if ($photo) {
            return asset('storage/' . $photo->thumbnail_path);
        }
        return null;
    }

    public function hasPendingPremiumRequest()
    {
        return \App\Models\PremiumRequest::where('listing_id', $this->id)
            ->where('status', 'pending')
            ->exists();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }
}

