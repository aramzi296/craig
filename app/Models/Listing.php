<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Listing extends Model
{
    use Searchable;
    protected $fillable = [
        'user_id', 'district_id', 'subdistrict_id', 'title', 'slug', 'activation_code', 'description', 
        'address', 'price', 'is_featured', 'is_premium', 'is_active', 'features', 
        'whatsapp_visibility', 'comment_visibility', 'expires_at', 'website', 'meta',
        'listing_rank',
    ];

    protected $casts = [
        'features' => 'array',
        'meta' => 'array',
        'is_featured' => 'boolean',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected static function booted()
    {
        $clearCache = function () {
            try {
                $redisStore = \Illuminate\Support\Facades\Cache::store('redis');
                $redisStore->forget('tags:approved_with_listings');
                
                // Hapus juga Hash pencarian di Redis secara langsung
                \Illuminate\Support\Facades\Redis::connection('cache')->del('laravel-cache-tags:searches');
            } catch (\Exception $e) {
                // Prevent app from crashing if Redis connection fails
            }
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class);
    }


    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function approvedTags()
    {
        return $this->belongsToMany(Tag::class)->whereRaw('is_approved = true');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_listing');
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
        return $this->getImageUrl();
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

    public function updateSearchableField()
    {
        // No longer needed, Scout handles indexing automatically
    }

    public function toSearchableArray()
    {
        $whatsapp = $this->user ? $this->user->whatsapp : null;
        $whatsapp0 = $whatsapp && str_starts_with($whatsapp, '62') ? '0' . substr($whatsapp, 2) : $whatsapp;

        $waSuffixes = [];
        if ($whatsapp0) {
            $len = strlen($whatsapp0);
            for ($i = 0; $i < $len; $i++) {
                $waSuffixes[] = substr($whatsapp0, $i);
            }
        }
        if ($whatsapp && $whatsapp !== $whatsapp0) {
            $len = strlen($whatsapp);
            for ($i = 0; $i < $len; $i++) {
                $waSuffixes[] = substr($whatsapp, $i);
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'is_active' => (bool) $this->is_active,
            'district_id' => $this->district_id,
            'categories' => $this->categories->pluck('id')->toArray(),
            'tags' => $this->tags->pluck('id')->toArray(),
            'owner_name' => $this->user ? $this->user->name : null,
            'owner_whatsapp' => $whatsapp,
            'owner_whatsapp_0' => $whatsapp0,
            'owner_whatsapp_suffixes' => $waSuffixes,
        ];
    }
}

