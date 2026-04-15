<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaMessage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'raw_json' => 'array',
    ];
}
