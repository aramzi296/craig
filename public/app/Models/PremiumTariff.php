<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PremiumTariff extends Model
{
    protected $table = 'premium_tariffs';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

