<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * WaLoginController — verifikasi dual OTP WhatsApp dan login tanpa email/password.
 *
 * Flow:
 *  GET  /wa-login        → tampilkan form input nomor WA
 *  POST /wa-login/verify → verifikasi fase 1: nomor WA, hasilkan formulir OTP
 *  GET  /wa-login/otp    → tampilkan form input OTP1 & OTP2
 *  POST /wa-login/otp    → verifikasi OTP1 & OTP2, buat wa_login_token, login user
 */
class WaLoginController extends Controller
{
    // ─── Show landing page ─────────────────────────────────────────────────

    public function index()
    {
        return view('auth.wa-login');
    }

    // ─── Show OTP form (after phone verified) ─────────────────────────────

    public function otpForm(Request $request)
    {
        $phone = $request->session()->get('wa_login_phone');
        if (!$phone) {
            return redirect()->route('wa-login')->withErrors(['phone' => 'Sesi tidak valid. Silakan masukkan nomor WhatsApp kembali.']);
        }

        return view('auth.wa-login-otp', compact('phone'));
    }

    // ─── POST: verify phone number, redirect to OTP form ──────────────────

    public function verifyPhone(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.min'      => 'Nomor WhatsApp tidak valid.',
        ]);

        $phone = $this->normalizePhone($request->input('phone'));

        $user = User::where('whatsapp', $phone)->first();

        if (!$user) {
            return back()->withErrors([
                'phone' => 'Nomor WhatsApp ini belum terdaftar. Kirim kata kunci *login* ke WhatsApp kami untuk mendaftar.',
            ])->withInput();
        }

        // Check if OTPs have been issued and are still valid
        if (!$user->wa_otp1 || !$user->wa_otp1_expires_at || $user->wa_otp1_expires_at->isPast()) {
            return back()->withErrors([
                'phone' => 'Kode OTP belum dikirim atau sudah kedaluwarsa. Kirim kata kunci *login* ke WhatsApp kami untuk mendapatkan OTP baru.',
            ])->withInput();
        }

        // Store phone in session for OTP form
        $request->session()->put('wa_login_phone', $phone);

        return redirect()->route('wa-login.otp');
    }

    // ─── POST: verify OTP1 + OTP2, login user ─────────────────────────────

    public function verifyOtp(Request $request)
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

        $phone = $request->session()->get('wa_login_phone');
        if (!$phone) {
            return redirect()->route('wa-login')->withErrors(['otp1' => 'Sesi tidak valid. Mulai ulang proses login.']);
        }

        $user = User::where('whatsapp', $phone)->first();

        if (!$user) {
            $request->session()->forget('wa_login_phone');
            return redirect()->route('wa-login')->withErrors(['phone' => 'Akun tidak ditemukan.']);
        }

        $otp1Input = $request->input('otp1');
        $otp2Input = $request->input('otp2');

        // ── Validate OTP1 ──────────────────────────────────────────────────
        if (!$user->wa_otp1 || !$user->wa_otp1_expires_at || $user->wa_otp1_expires_at->isPast()) {
            return back()->withErrors(['otp1' => 'OTP Pertama sudah kedaluwarsa. Kirim *login* lagi ke WhatsApp kami.']);
        }

        if (!Hash::check($otp1Input, $user->wa_otp1)) {
            Log::warning('WA Login: OTP1 mismatch', ['phone_sfx' => substr($phone, -4)]);
            return back()->withErrors(['otp1' => 'OTP Pertama tidak valid.'])->withInput();
        }

        // ── Validate OTP2 ──────────────────────────────────────────────────
        if (!$user->wa_otp2 || !$user->wa_otp2_expires_at || $user->wa_otp2_expires_at->isPast()) {
            return back()->withErrors(['otp2' => 'OTP Kedua sudah kedaluwarsa. Kirim *login* lagi ke WhatsApp kami.']);
        }

        if (!Hash::check($otp2Input, $user->wa_otp2)) {
            Log::warning('WA Login: OTP2 mismatch', ['phone_sfx' => substr($phone, -4)]);
            return back()->withErrors(['otp2' => 'OTP Kedua tidak valid.'])->withInput();
        }

        // ── Both OTPs valid – clear OTPs, login ───────────────────────────
        $user->update([
            'wa_otp1'                   => null,
            'wa_otp1_expires_at'        => null,
            'wa_otp2'                   => null,
            'wa_otp2_expires_at'        => null,
            'wa_login_token'            => null,
            'wa_login_token_expires_at' => null,
        ]);

        $request->session()->forget('wa_login_phone');

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        Log::info('WA Login: user logged in via dual OTP', ['user_id' => $user->id]);

        return redirect()->intended(route('dashboard'));
    }

    // ─── Helper ───────────────────────────────────────────────────────────

    private function normalizePhone(string $input): string
    {
        $digits = preg_replace('/\D/', '', $input) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }
}
