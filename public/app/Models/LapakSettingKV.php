<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LapakSettingKV extends Model
{
    protected $table = 'app_settings';

    protected $guarded = [];

    public $timestamps = true;

    public static function getString(string $key, ?string $default = null): ?string
    {
        $val = static::query()->where('setting_key', $key)->value('setting_value');
        return $val !== null ? (string) $val : $default;
    }

    public static function getInt(string $key, int $default): int
    {
        $val = static::query()->where('setting_key', $key)->value('setting_value');
        if ($val === null) {
            return $default;
        }

        return (int) $val;
    }

    public static function set(string $key, string $value): void
    {
        static::query()->updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}

