<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * WaLoginController — Login tanpa email/password menggunakan dua OTP WhatsApp.
 *
 * Alur:
 *  1. User kirim "login" ke WA → bot kirim OTP1, OTP2, dan link /wa-login/{nonce}
 *  2. GET  /wa-login/{nonce}  → tampilkan form minta OTP1 + OTP2
 *  3. POST /wa-login/{nonce}  → verifikasi OTP1 & OTP2, login user
 *
 *  Nonce → user_id disimpan di cache (10 menit).
 *  OTP1 & OTP2 disimpan ter-hash di tabel users, juga expire 10 menit.
 */
class WaLoginController extends Controller
{
    // ─── Halaman info bila nonce tidak ada ──────────────────────────────────

    public function index()
    {
        return view('auth.wa-login');
    }

    // ─── GET /wa-login/{nonce} — form OTP ───────────────────────────────────

    public function showOtpForm(string $nonce)
    {
        // Validasi nonce di cache
        $userId = Cache::get('wa_login_nonce:' . $nonce);

        if (!$userId) {
            return redirect()->route('wa-login')
                ->withErrors(['nonce' => 'Link login tidak valid atau sudah kedaluwarsa. Kirim *login* kembali ke WhatsApp kami untuk mendapatkan link baru.']);
        }

        $user = User::find($userId);

        if (!$user) {
            Cache::forget('wa_login_nonce:' . $nonce);
            return redirect()->route('wa-login')
                ->withErrors(['nonce' => 'Akun tidak ditemukan.']);
        }

        // Cek OTP masih berlaku
        if (!$user->wa_otp1 || !$user->wa_otp1_expires_at || $user->wa_otp1_expires_at->isPast()) {
            Cache::forget('wa_login_nonce:' . $nonce);
            return redirect()->route('wa-login')
                ->withErrors(['nonce' => 'Kode OTP sudah kedaluwarsa. Kirim *login* kembali ke WhatsApp kami.']);
        }

        return view('auth.wa-login-otp', [
            'nonce'    => $nonce,
            'maskedName' => $this->maskName($user->name),
        ]);
    }

    // ─── POST /wa-login/{nonce} — verifikasi OTP ────────────────────────────

    public function verifyOtp(Request $request, string $nonce)
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

        // ── Cari user via nonce ────────────────────────────────────────────
        $userId = Cache::get('wa_login_nonce:' . $nonce);

        if (!$userId) {
            return redirect()->route('wa-login')
                ->withErrors(['nonce' => 'Sesi login sudah kedaluwarsa. Kirim *login* kembali ke WhatsApp kami.']);
        }

        $user = User::find($userId);

        if (!$user) {
            Cache::forget('wa_login_nonce:' . $nonce);
            return redirect()->route('wa-login')
                ->withErrors(['nonce' => 'Akun tidak ditemukan.']);
        }

        $otp1Input = $request->input('otp1');
        $otp2Input = $request->input('otp2');

        // ── Validasi OTP1 ──────────────────────────────────────────────────
        if (!$user->wa_otp1 || !$user->wa_otp1_expires_at || $user->wa_otp1_expires_at->isPast()) {
            return back()->withErrors([
                'otp1' => 'OTP Pertama sudah kedaluwarsa. Kirim *login* lagi ke WhatsApp kami untuk mendapatkan kode baru.',
            ]);
        }

        if (!Hash::check($otp1Input, $user->wa_otp1)) {
            Log::warning('WA Login: OTP1 mismatch', ['user_id' => $user->id]);
            return back()->withErrors(['otp1' => 'OTP Pertama tidak valid.'])->withInput();
        }

        // ── Validasi OTP2 ──────────────────────────────────────────────────
        if (!$user->wa_otp2 || !$user->wa_otp2_expires_at || $user->wa_otp2_expires_at->isPast()) {
            return back()->withErrors([
                'otp2' => 'OTP Kedua sudah kedaluwarsa. Kirim *login* lagi ke WhatsApp kami untuk mendapatkan kode baru.',
            ]);
        }

        if (!Hash::check($otp2Input, $user->wa_otp2)) {
            Log::warning('WA Login: OTP2 mismatch', ['user_id' => $user->id]);
            return back()->withErrors(['otp2' => 'OTP Kedua tidak valid.'])->withInput();
        }

        // ── Keduanya valid — hapus OTP & nonce, login user ────────────────
        Cache::forget('wa_login_nonce:' . $nonce);

        $user->update([
            'wa_otp1'                   => null,
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

    // ─── Helper ───────────────────────────────────────────────────────────

    /** Mask nama untuk tampilan: "Ahmad Budi" → "Ahmad B***" */
    private function maskName(string $name): string
    {
        $parts = explode(' ', $name, 2);
        $first = $parts[0];
        $rest  = isset($parts[1]) ? ' ' . substr($parts[1], 0, 1) . '***' : '';
        return $first . $rest;
    }
}
