<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginOtpMail;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        // Generate OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        $user->update([
            'email_otp' => Hash::make($otp),
            'email_otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new LoginOtpMail($otp));

        $request->session()->put('login_user_id', $user->id);
        $request->session()->put('login_remember', $request->boolean('remember'));

        return redirect()->route('login.otp');
    }

    public function showOtpForm(Request $request)
    {
        if (!$request->session()->has('login_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.login-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits'   => 'Kode OTP harus 6 digit angka.',
        ]);

        $userId = $request->session()->get('login_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi login telah berakhir.']);
        }

        $user = User::find($userId);

        if (!$user || !$user->email_otp_expires_at || $user->email_otp_expires_at->isPast()) {
            if ($user) {
                $user->update(['email_otp' => null, 'email_otp_expires_at' => null]);
            }
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Silakan login kembali.']);
        }

        if (!Hash::check($request->otp, $user->email_otp)) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        // OTP Valid
        $user->update(['email_otp' => null, 'email_otp_expires_at' => null]);
        
        $remember = $request->session()->pull('login_remember', false);
        $request->session()->forget('login_user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended('dashboard');
    }

    public function showRegister(Request $request)
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'name' => strip_tags($request->name)
        ]);

        $request->validate([
            'name'     => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\.\,\-\'\&]+$/'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'name.regex'        => 'Nama hanya boleh berisi huruf, angka, dan tanda baca dasar.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'password.min'      => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat! Selamat datang, ' . $user->name . '.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
