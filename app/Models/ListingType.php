<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingType extends Model
{
    protected $fillable = ['name', 'slug', 'color'];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
