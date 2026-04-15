<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function listings()
    {
        return $this->belongsToMany(Listing::class, 'category_listing')
            ->withTimestamps();
    }

    public function getBreadcrumbAttribute(): string
    {
        $parts = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($parts, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $parts);
    }

    /**
     * Scope: hanya kategori untuk tipe listing tertentu.
     *
     * - forType('kerja')  → hanya kategori kerja
     * - forType(null)     → semua kecuali yang eksklusif ke 'kerja'
     *
     * Usage:
     *   Category::forType('kerja')->whereNull('parent_id')->get();
     */
    public function scopeForType($query, ?string $type)
    {
        if ($type === 'usaha') {
            return $query->where(function ($q) {
                $q->whereNull('listing_type')->orWhere('listing_type', 'usaha');
            });
        }

        if ($type === null) {
            // Halaman Beranda (tanpa filter khusus) -> semua
            return $query;
        }

        return $query->where('listing_type', $type);
    }

    /** Shortcut: hanya kategori kerja */
    public function scopeForKerja($query)
    {
        return $this->scopeForType($query, 'kerja');
    }

    /** Shortcut: kategori umum (bukan kerja) */
    public function scopeGeneral($query)
    {
        return $this->scopeForType($query, null);
    }
}
