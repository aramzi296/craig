<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumRequest extends Model
{
    protected $fillable = ['user_id', 'listing_id', 'package_id', 'unique_code', 'status', 'expires_at'];


    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function package()
    {
        return $this->belongsTo(PremiumPackage::class, 'package_id');
    }
}

