<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    protected $fillable = [
        'user_id', 'listing_type_id', 'district_id', 'title', 'slug', 'description', 
        'price', 'is_featured', 'is_premium', 'is_active', 'features', 
        'whatsapp_visibility', 'comment_visibility', 'expires_at', 'website',
        'listing_rank',
    ];

    protected $casts = [
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function district()
    {
        return $this->belongsTo(District::class);
    }


    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function approvedCategories()
    {
        return $this->belongsToMany(Category::class)->whereRaw('is_approved = true');
    }


    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
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
            return $photo->getUrl();
        }
        return null;
    }

    public function getThumbnailUrl()
    {
        $photo = $this->photos()->where('collection', 'foto_fitur')->first();
        if ($photo) {
            return $photo->getThumbnailUrl();
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

    public function views()
    {
        return $this->hasMany(ListingView::class);
    }
}

