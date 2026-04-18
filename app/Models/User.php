<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'whatsapp',
        'wa_otp1',
        'wa_otp1_lookup',
        'wa_otp1_expires_at',
        'wa_otp2',
        'wa_otp2_expires_at',
        'wa_login_token',
        'wa_login_token_expires_at',
        'is_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'wa_otp1',
        'wa_otp2',
        'wa_login_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'password'                   => 'hashed',
            'is_verified'                => 'boolean',
            'wa_otp1_expires_at'         => 'datetime',

            'wa_otp2_expires_at'         => 'datetime',
            'wa_login_token_expires_at'  => 'datetime',
        ];
    }

    /**
     * Mutator: always store whatsapp in normalized international format.
     */
    public function setWhatsappAttribute(?string $value): void
    {
        $this->attributes['whatsapp'] = self::normalizeWhatsappNumber($value);
    }

    /**
     * Normalize a raw phone string to international digits (e.g. 0812 → 62812).
     */
    public static function normalizeWhatsappNumber(?string $input): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $input) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    public function favorites()
    {
        return $this->belongsToMany(\App\Models\Listing::class, 'favorites', 'user_id', 'listing_id')->withTimestamps();
    }

    public function getProfilePhoto()
    {
        $dir = public_path('images-contoh');
        if (is_dir($dir)) {
            $files = glob($dir . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);
            if (count($files) > 0) {
                // Use deterministic random based on ID
                $index = $this->id % count($files);
                return asset('images-contoh/' . basename($files[$index]));
            }
        }
        return "https://ui-avatars.com/api/?name=" . urlencode($this->name) . "&background=0ea5e9&color=fff";
    }
}
