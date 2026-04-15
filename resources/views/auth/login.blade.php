@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Selamat Datang</h2>
        <p>Masuk ke akun BatamCraig Anda</p>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px;">Masuk</button>
        </form>

        <div style="display:flex;align-items:center;gap:12px;margin:20px 0;color:#94a3b8;font-size:.85rem;">
            <div style="flex:1;height:1px;background:#e2e8f0;"></div>
            <span>atau</span>
            <div style="flex:1;height:1px;background:#e2e8f0;"></div>
        </div>

        <a href="{{ route('wa-login') }}"
           style="display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:13px;border-radius:10px;background:linear-gradient(135deg,#25D366,#1aa856);color:#fff;font-weight:700;text-decoration:none;font-size:.97rem;box-shadow:0 4px 14px rgba(37,211,102,.3);transition:transform .15s,box-shadow .15s;"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(37,211,102,.45)'"
           onmouseout="this.style.transform='';this.style.boxShadow='0 4px 14px rgba(37,211,102,.3)'">
            <svg viewBox="0 0 48 48" width="22" height="22" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="24" cy="24" r="24" fill="rgba(255,255,255,0.2)"/>
                <path d="M34.6 13.4A14.7 14.7 0 0 0 24 9C16.3 9 10 15.3 10 23c0 2.5.7 4.9 1.9 7L10 39l9.3-2.4A14.9 14.9 0 0 0 24 38c7.7 0 14-6.3 14-15 0-4-.8-7.2-3.4-9.6ZM24 35.3c-2.2 0-4.3-.6-6.2-1.6l-.4-.3-5.5 1.4 1.5-5.3-.3-.5A12.2 12.2 0 0 1 11.7 23C11.7 16.2 17.2 10.7 24 10.7c3.3 0 6.4 1.3 8.7 3.6A12.2 12.2 0 0 1 36.3 23c0 6.8-5.5 12.3-12.3 12.3Z" fill="white"/>
            </svg>
            Login via WhatsApp OTP
        </a>

        <div class="auth-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar Sekarang</a>
        </div>
    </div>
</div>
@endsection
