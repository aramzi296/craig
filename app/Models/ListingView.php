<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingView extends Model
{
    public $timestamps = false;
    protected $fillable = ['listing_id', 'ip_address', 'created_at'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?: now();
        });
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
