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
