<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaMessage extends Model
{
    protected $fillable = [
        'from_number',
        'to_number',
        'message',
        'raw_json',
    ];

    protected $casts = [
        'raw_json' => 'array',
    ];
}
