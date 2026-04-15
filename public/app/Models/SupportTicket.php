<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_viewed_by_admin' => 'boolean',
        'is_viewed_by_user' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(SupportMessage::class, 'last_message_id'); // If I want to track it
    }
}
