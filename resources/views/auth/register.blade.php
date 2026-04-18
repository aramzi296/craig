@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 500px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin-bottom: 5px;">Daftar Akun</h2>
            <p>Pilih metode pendaftaran yang Anda sukai</p>
        </div>

        <!-- WhatsApp Method (Recommended) -->
        <div class="wa-registration-cta" style="background: linear-gradient(135deg, #25d366 0%, #128c7e 100%); padding: 25px; border-radius: 16px; color: white; margin-bottom: 30px; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(37, 211, 102, 0.2);">
            <div style="position: relative; z-index: 2;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="background: rgba(255,255,255,0.2); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Daftar Instan via WhatsApp</h3>
                </div>
                <p style="text-align: left; color: rgba(255,255,255,0.9); font-size: 0.9rem; margin-bottom: 20px; line-height: 1.4;">
                    Cara tercepat! Cukup kirim pesan <strong>"login"</strong> ke Bot kami. Akun Anda akan otomatis terbuat atau masuk.
                </p>
                @php
                    $whatsappAdminNumber = env('WHATSAPP_ADMIN_NUMBER', '6282172292230');
                @endphp
                <a href="https://wa.me/{{ $whatsappAdminNumber }}?text=login" target="_blank" class="btn" style="background: white; color: #128c7e; width: 100%; border: none; font-weight: 700; padding: 12px;">
                    <i class="fab fa-whatsapp" style="margin-right: 8px;"></i> Kirim "login" Sekarang
                </a>
            </div>
            <!-- Decorative circle -->
            <div style="position: absolute; right: -20px; top: -20px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; z-index: 1;"></div>
        </div>

        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; color: var(--text-muted); font-size: 0.85rem;">
            <hr style="flex: 1; border: none; border-top: 1px solid var(--border);">
            <span>ATAU DAFTAR MANUAL</span>
            <hr style="flex: 1; border: none; border-top: 1px solid var(--border);">
        </div>

        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="email@contoh.com" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi password" required>
            </div>


            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem;">Buat Akun Baru</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
        </div>
    </div>
</div>
@endsection

