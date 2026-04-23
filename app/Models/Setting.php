<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    public static function get($key, $default = null)
    {
        try {
            $setting = \Illuminate\Support\Facades\Cache::rememberForever("setting.{$key}", function() use ($key) {
                return self::where('key', $key)->first();
            });

            if ($setting) {
                return $setting->value;
            }
        } catch (\Throwable $e) {
            // In case table doesn't exist yet during some commands
        }
        
        return $default;
    }
}
