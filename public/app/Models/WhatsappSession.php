<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'current_step',
        'payload',
        'last_activity',
    ];

    protected $casts = [
        'payload' => 'array',
        'last_activity' => 'datetime',
    ];
}
