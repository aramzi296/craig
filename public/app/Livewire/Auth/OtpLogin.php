<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class OtpLogin extends Component
{
    public $whatsapp = '';
    public $otp = '';

    public function render()
    {
        return view('livewire.auth.otp-login')->layout('layouts.main');
    }

    public function login()
    {
        $this->validate([
            'whatsapp' => ['required', 'string'],
            'otp'      => ['required', 'string'],
        ]);

        $normalized = User::normalizeWhatsappNumber($this->whatsapp);
        $user = User::where('whatsapp', $normalized)->first();

        if (!$user) {
            $this->addError('whatsapp', 'Nomor WhatsApp belum terdaftar.');
            return;
        }

        if (!$user->otp || !$user->otp_expires_at) {
            $this->addError('otp', 'Anda belum meminta OTP. Silakan kirim pesan "OTP" ke WhatsApp Admin.');
            return;
        }

        if ($user->otp_expires_at->isPast()) {
            $this->addError('otp', 'Kode OTP sudah kadaluarsa. Silakan minta ulang.');
            return;
        }

        if (!Hash::check($this->otp, $user->otp)) {
            $this->addError('otp', 'Kode OTP yang dimasukkan tidak valid.');
            return;
        }

        // OTP Valid. Reset OTP dan aktifkan akun jika belum aktif
        $user->update([
            'otp'                  => null,
            'otp_expires_at'       => null,
            'whatsapp_verified_at' => $user->whatsapp_verified_at ?? now(),
            'is_active'            => true,
            'wa_verify_failed_attempts' => 0,
        ]);

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}
