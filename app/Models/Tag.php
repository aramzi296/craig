<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order', 'is_approved', 'parent_id'];

    protected $casts = [
        'is_approved' => 'boolean',
        'parent_id' => 'integer',
    ];

    protected static function booted()
    {
        $clearCache = function () {
            try {
                $redisStore = \Illuminate\Support\Facades\Cache::store('redis');
                $redisStore->forget('tags:approved_with_listings');
                $redisStore->forget('tags:searches');
            } catch (\Exception $e) {
                // Prevent app from crashing if Redis connection fails
            }
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function parent()
    {
        return $this->belongsTo(Tag::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Tag::class, 'parent_id')->orderBy('sort_order');
    }

    public function listings()
    {
        return $this->belongsToMany(Listing::class);
    }
}
