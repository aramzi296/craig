<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Listing extends Model implements HasMedia
{
    use InteractsWithMedia, \Laravel\Scout\Searchable;

    protected $guarded = [];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'is_draft'    => 'boolean',
        'is_verified' => 'boolean',
        'meta'        => 'array',
    ];

    /**
     * Prepare the data that will be indexed by MeiliSearch.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'subdistrict_id' => $this->subdistrict_id,
            'subdistrict_name' => $this->subdistrict?->name,
            'district_id' => $this->district_id,
            'district_name' => $this->district?->name,
            'category_ids' => $this->categories->pluck('id')->toArray(),
            'categories_names' => $this->categories->pluck('name')->toArray(),
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'is_featured' => (bool) $this->is_featured,
            'is_active' => (bool) $this->is_active,
            'is_draft' => (bool) $this->is_draft,
            'created_at' => $this->created_at->timestamp,
        ];
    }

    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['subdistrict', 'district', 'categories']);
    }

    public function scopeBlog($query)
    {
        return $query->where('type', 'blog');
    }



    public function listingType()
    {
        return $this->belongsTo(ListingType::class, 'type', 'name');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_listing')
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsToMany(Category::class, 'category_listing')
            ->limit(1);
    }

    public function getCategoryAttribute()
    {
        if ($this->relationLoaded('category')) {
            return $this->getRelation('category')->first();
        }

        if ($this->relationLoaded('categories')) {
            return $this->categories->first();
        }

        return $this->categories()->first();
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class);
    }

    public function reviews()
    {
        return $this->hasMany(ListingReview::class);
    }

    public function reports()
    {
        return $this->hasMany(ListingReport::class);
    }

    public function claims()
    {
        return $this->hasMany(ListingClaim::class);
    }

    /**
     * Register Spatie Media Library collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('gallery')
            ->useDisk('public');
    }

    /**
     * Register Image Conversions.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->format('webp')
            ->performOnCollections('featured', 'gallery')
            ->nonQueued();

        $this->addMediaConversion('tiny_thumb')
            ->width(50)
            ->height(50)
            ->sharpen(5)
            ->format('webp')
            ->performOnCollections('featured', 'gallery')
            ->nonQueued();
    }

    public function whatsappUrl(): ?string
    {
        if (is_array($this->meta) && isset($this->meta['show_whatsapp']) && $this->meta['show_whatsapp'] == false) {
            return null;
        }

        $raw = trim((string) $this->whatsapp);
        if (! $raw) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $raw);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '62')) {
            return 'https://wa.me/'.$digits;
        }
        if (str_starts_with($digits, '0')) {
            return 'https://wa.me/62'.substr($digits, 1);
        }

        return 'https://wa.me/62'.$digits;
    }

    public function mapsUrl(): ?string
    {
        if (! $this->address) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.urlencode($this->address);
    }

}
