<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * WaLoginController — Login tanpa password menggunakan dua OTP WhatsApp.
 *
 * Alur:
 *  1. User kirim "login" ke WA bot → bot kirim OTP1 + OTP2 + link /wa-login
 *  2. GET  /wa-login  → tampilkan form minta OTP1 + OTP2
 *  3. POST /wa-login  → cari user via SHA-256(OTP1), verifikasi OTP2 bcrypt,
 *                       hapus OTP, login user
 */
class WaLoginController extends Controller
{
    // ─── GET /wa-login — tampilkan form OTP ─────────────────────────────────

    public function index()
    {
        return view('auth.wa-login');
    }

    // ─── POST /wa-login — verifikasi OTP, login user ───────────────────────
    public function verify(Request $request)
    {
        $request->validate([
            'whatsapp' => ['required', 'string'],
            'otp'      => ['required', 'digits:6'],
        ], [
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'otp.required'      => 'Kode OTP wajib diisi.',
            'otp.digits'        => 'Kode OTP harus 6 digit angka.',
        ]);

        $whatsappRaw = $request->input('whatsapp');
        $otpInput    = $request->input('otp');

        $whatsapp = User::normalizeWhatsappNumber($whatsappRaw);
        $lookup   = hash('sha256', $otpInput);

        $user = User::where('whatsapp', $whatsapp)
            ->where('wa_otp1_lookup', $lookup)
            ->first();

        if (!$user) {
            Log::warning('WA Login failed: lookup failed', ['phone' => $whatsapp]);
            return back()
                ->withErrors(['otp' => 'Kode OTP tidak valid atau nomor salah.'])
                ->withInput();
        }

        // ── Cek expire OTP ───────────────────────────────────────────────
        if (!$user->wa_otp1_expires_at || $user->wa_otp1_expires_at->isPast()) {
            // Bersihkan OTP kedaluwarsa
            $user->update([
                'wa_otp1'            => null,
                'wa_otp1_lookup'     => null,
                'wa_otp1_expires_at' => null,
            ]);
            return back()
                ->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Kirim *otp* kembali ke WhatsApp kami.'])
                ->withInput();
        }

        // ── Verifikasi bcrypt OTP ────────────────────────────────────────
        if (!Hash::check($otpInput, $user->wa_otp1)) {
            Log::warning('WA Login failed: bcrypt mismatch', ['user_id' => $user->id]);
            return back()
                ->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.'])
                ->withInput();
        }

        // ── Valid — bersihkan OTP, login user ────────────────────
        $user->update([
            'wa_otp1'                   => null,
            'wa_otp1_lookup'            => null,
            'wa_otp1_expires_at'        => null,
            'wa_otp2'                   => null,
            'wa_otp2_expires_at'        => null,
            'wa_login_token'            => null,
            'wa_login_token_expires_at' => null,
        ]);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        Log::info('WA Login success: user logged in via OTP', ['user_id' => $user->id]);

        return redirect()->intended(route('dashboard'));
    }
}
