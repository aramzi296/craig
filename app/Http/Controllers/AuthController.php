<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->has('admin_login')) {
            return view('auth.login');
        }
        return redirect()->route('wa-login')->with('info', 'Login menggunakan email sementara ditutup. Silakan masuk menggunakan WhatsApp.');
    }

    public function login(\Illuminate\Http\Request $request)
    {
        return $this->showLogin();
    }

    public function showRegister()
    {
        return redirect()->route('wa-login')->with('info', 'Registrasi sementara dialihkan melalui WhatsApp. Kirim pesan "otp" ke bot kami.');
    }

    public function register(\Illuminate\Http\Request $request)
    {
        return $this->showRegister();
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
