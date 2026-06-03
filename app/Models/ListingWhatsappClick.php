<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingWhatsappClick extends Model
{
    public $timestamps = false;
    protected $fillable = ['listing_id', 'user_id', 'ip_address', 'user_agent', 'created_at'];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
