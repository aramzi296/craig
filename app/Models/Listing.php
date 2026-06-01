<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Listing extends Model
{
    protected $fillable = [
        'user_id', 'district_id', 'subdistrict_id', 'title', 'slug', 'activation_code', 'description', 
        'address', 'price', 'is_featured', 'is_premium', 'is_active', 'features', 
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
        $tags = $this->tags()->pluck('name')->implode(' ');
        $categories = $this->categories()->pluck('name')->implode(' ');
        $district = $this->district ? $this->district->name : '';
        $subdistrict = $this->subdistrict ? $this->subdistrict->name : '';
        $address = $this->address ?? '';
        
        // Gabungkan semua bidang
        $text = $this->title . ' ' . $this->description . ' ' . $tags . ' ' . $categories . ' ' . $district . ' ' . $subdistrict . ' ' . $address;
        
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

        $driver = $query->getConnection()->getDriverName();

        return $query->where(function($q) use ($keywords, $driver) {
            foreach ($keywords as $keyword) {
                $word = trim($keyword);
                if ($word !== '') {
                    if ($driver === 'pgsql') {
                        // Di PostgreSQL, gunakan POSIX case-insensitive regex matching dengan word boundary \y
                        $q->whereRaw("searchable ~* ?", ['\y' . preg_quote($word, '/') . '\y']);
                    } elseif ($driver === 'sqlite') {
                        // Di SQLite (untuk testing), gunakan normalisasi concatenating space untuk pencarian kata utuh
                        $q->whereRaw("' ' || searchable || ' ' LIKE ?", ['% ' . $word . ' %']);
                    } else {
                        // Fallback aman untuk database lain
                        $q->where('searchable', 'like', '%' . $word . '%');
                    }
                }
            }
        });
    }
}

