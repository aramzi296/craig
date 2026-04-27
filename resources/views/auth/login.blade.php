@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 450px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin-bottom: 5px;">Selamat Datang Kembali</h2>
            <p>Silakan masuk ke akun Sebatam Anda</p>
        </div>

        <!-- WhatsApp Method (Recommended) -->
        <div class="wa-registration-cta" style="background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); padding: 25px; border-radius: 16px; color: white; margin-bottom: 30px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(37, 211, 102, 0.2);">
            <div style="position: relative; z-index: 2;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Login Tanpa Password</h3>
                </div>
                <p style="text-align: left; color: rgba(255,255,255,0.9); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.4;">
                    Masuk lebih aman dan cepat menggunakan kode OTP yang dikirim ke WhatsApp Anda.
                </p>
                <a href="{{ route('wa-login') }}" class="btn" style="background: white; color: #128c7e; width: 100%; border: none; font-weight: 700; padding: 12px;">
                    <i class="fab fa-whatsapp" style="margin-right: 8px;"></i> Masuk via WhatsApp
                </a>
            </div>
            <div style="position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; z-index: 1;"></div>
        </div>

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:25px;color:var(--text-muted);font-size:.85rem;">
            <div style="flex:1;height:1px;background:var(--border);"></div>
            <span>ATAU MASUK MANUAL</span>
            <div style="flex:1;height:1px;background:var(--border);"></div>
        </div>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Email Anda" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password Anda" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 25px; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="remember" id="remember" style="width: auto; cursor: pointer;">
                <label for="remember" style="margin: 0; cursor: pointer; font-weight: 400;">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem;">Masuk Sekarang</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
        </div>
    </div>
</div>
@endsection

