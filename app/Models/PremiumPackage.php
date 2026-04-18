<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumPackage extends Model
{
    /** @use HasFactory<\Database\Factories\PremiumPackageFactory> */
    use HasFactory;

    protected $fillable = ['name', 'price', 'duration_days', 'is_active'];

    public function requests()
    {
        return $this->hasMany(PremiumRequest::class, 'package_id');
    }
}

