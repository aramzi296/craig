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

    // ─── POST /wa-login — verifikasi OTP1 + OTP2, login user ────────────────

    public function verify(Request $request)
    {
        $request->validate([
            'otp1' => ['required', 'digits:6'],
            'otp2' => ['required', 'digits:6'],
        ], [
            'otp1.required' => 'OTP Pertama wajib diisi.',
            'otp1.digits'   => 'OTP Pertama harus 6 digit angka.',
            'otp2.required' => 'OTP Kedua wajib diisi.',
            'otp2.digits'   => 'OTP Kedua harus 6 digit angka.',
        ]);

        $otp1Input = $request->input('otp1');
        $otp2Input = $request->input('otp2');

        // ── Cari user lewat SHA-256 dari OTP1 ────────────────────────────
        $lookup = hash('sha256', $otp1Input);

        $user = User::where('wa_otp1_lookup', $lookup)->first();

        if (!$user) {
            Log::warning('WA Login: OTP1 lookup failed (no match)');
            return back()
                ->withErrors(['otp1' => 'OTP Pertama tidak valid atau sudah kedaluwarsa.'])
                ->withInput();
        }

        // ── Cek expire OTP1 ───────────────────────────────────────────────
        if (!$user->wa_otp1_expires_at || $user->wa_otp1_expires_at->isPast()) {
            // Bersihkan OTP kedaluwarsa
            $user->update([
                'wa_otp1'        => null,
                'wa_otp1_lookup' => null,
                'wa_otp1_expires_at' => null,
                'wa_otp2'        => null,
                'wa_otp2_expires_at' => null,
            ]);
            return back()
                ->withErrors(['otp1' => 'Kode OTP sudah kedaluwarsa. Kirim *login* kembali ke WhatsApp kami.'])
                ->withInput();
        }

        // ── Verifikasi bcrypt OTP1 (konfirmasi, anti-collision) ───────────
        if (!Hash::check($otp1Input, $user->wa_otp1)) {
            Log::warning('WA Login: OTP1 bcrypt mismatch (SHA-256 collision?)', ['user_id' => $user->id]);
            return back()
                ->withErrors(['otp1' => 'OTP Pertama tidak valid.'])
                ->withInput();
        }

        // ── Cek expire OTP2 ───────────────────────────────────────────────
        if (!$user->wa_otp2 || !$user->wa_otp2_expires_at || $user->wa_otp2_expires_at->isPast()) {
            return back()
                ->withErrors(['otp2' => 'OTP Kedua sudah kedaluwarsa. Kirim *login* kembali ke WhatsApp kami.'])
                ->withInput();
        }

        // ── Verifikasi bcrypt OTP2 ────────────────────────────────────────
        if (!Hash::check($otp2Input, $user->wa_otp2)) {
            Log::warning('WA Login: OTP2 mismatch', ['user_id' => $user->id]);
            return back()
                ->withErrors(['otp2' => 'OTP Kedua tidak valid.'])
                ->withInput();
        }

        // ── Keduanya valid — bersihkan OTP, login user ────────────────────
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

        Log::info('WA Login: user logged in via dual OTP', ['user_id' => $user->id]);

        return redirect()->intended(route('dashboard'));
    }
}
