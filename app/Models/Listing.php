<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Listing extends Model
{
    protected $fillable = [
        'user_id', 'listing_type_id', 'district_id', 'title', 'slug', 'activation_code', 'description', 
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


    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function approvedTags()
    {
        return $this->belongsToMany(Tag::class)->whereRaw('is_approved = true');
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
        $tags = $this->tags()->pluck('name')->implode(' ');
        $district = $this->district ? $this->district->name : '';
        
        // Gabungkan semua bidang
        $text = $this->title . ' ' . $this->description . ' ' . $tags . ' ' . $district;
        
        // Ganti karakter separator yang sering menyatukan kata agar bisa dicari terpisah
        // Contoh: "Barang/Jasa" menjadi "Barang Jasa"
        $text = str_replace(['/', '\\', '-', '_'], ' ', $text);
        
        $this->searchable = trim($text);
        $this->saveQuietly();
    }

    public function scopeSearch($query, $term)
    {
        if (empty($term)) return $query;

        // Bersihkan term dari karakter yang bisa mengganggu
        $cleanTerm = str_replace(['/', '\\'], ' ', $term);
        $keywords = explode(' ', $cleanTerm);

        return $query->where(function($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $word = trim($keyword);
                if ($word !== '') {
                    // Menggunakan ILIKE untuk substring matching (agar "kontrak" bisa menemukan "kontrakan")
                    // Ini bekerja baik di PostgreSQL dan SQLite (dengan bantuan Laravel abstraction)
                    $q->where('searchable', 'ilike', '%' . $word . '%');
                }
            }
        });
    }
}

