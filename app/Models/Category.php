<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order', 'is_approved'];

    protected $casts = [
        'is_approved' => 'boolean',
    ];


    public function listings()
    {
        return $this->belongsToMany(Listing::class);
    }
}
