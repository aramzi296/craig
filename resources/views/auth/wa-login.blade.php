@extends('layouts.app')

@section('title', 'Login via WhatsApp – BatamCraig')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card wa-login-card">

        {{-- WA Icon --}}
        <div class="wa-icon-wrap">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="24" cy="24" r="24" fill="#25D366"/>
                <path d="M34.6 13.4A14.7 14.7 0 0 0 24 9C16.3 9 10 15.3 10 23c0 2.5.7 4.9 1.9 7L10 39l9.3-2.4A14.9 14.9 0 0 0 24 38c7.7 0 14-6.3 14-15 0-4-.8-7.2-3.4-9.6ZM24 35.3c-2.2 0-4.3-.6-6.2-1.6l-.4-.3-5.5 1.4 1.5-5.3-.3-.5A12.2 12.2 0 0 1 11.7 23C11.7 16.2 17.2 10.7 24 10.7c3.3 0 6.4 1.3 8.7 3.6A12.2 12.2 0 0 1 36.3 23c0 6.8-5.5 12.3-12.3 12.3Zm6.7-9.2c-.4-.2-2.3-1.1-2.6-1.2-.4-.2-.6-.2-.9.2-.3.4-1 1.2-1.3 1.5-.2.3-.5.3-.9.1a10.6 10.6 0 0 1-3.1-1.9 11.7 11.7 0 0 1-2.2-2.7c-.2-.4 0-.6.2-.8l.6-.7.4-.6a.5.5 0 0 0 0-.5l-1.2-2.9c-.3-.7-.6-.6-.9-.6h-.7c-.3 0-.7.1-1 .4-.4.4-1.4 1.3-1.4 3.2s1.4 3.7 1.6 4c.2.3 2.8 4.2 6.7 5.9 4 1.7 4 1.1 4.7 1.1.7 0 2.3-.9 2.6-1.8.3-.9.3-1.7.2-1.8-.1-.1-.3-.2-.7-.3Z" fill="white"/>
            </svg>
        </div>

        <h1>Login via WhatsApp</h1>

        {{-- Error dari redirect --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        {{-- Step-by-step guide --}}
        <div class="wa-steps">
            <div class="wa-step">
                <div class="wa-step-num">1</div>
                <div class="wa-step-body">
                    <strong>Kirim kata kunci ke WhatsApp kami</strong>
                    <p>Buka WhatsApp dan kirim pesan: <code>login</code></p>
                </div>
            </div>
            <div class="wa-step-arrow"><i class="fa-solid fa-arrow-down"></i></div>
            <div class="wa-step">
                <div class="wa-step-num">2</div>
                <div class="wa-step-body">
                    <strong>Terima dua kode OTP &amp; link</strong>
                    <p>Sistem mengirimkan OTP Pertama, OTP Kedua, dan link login ke WhatsApp Anda (berlaku 10 menit).</p>
                </div>
            </div>
            <div class="wa-step-arrow"><i class="fa-solid fa-arrow-down"></i></div>
            <div class="wa-step">
                <div class="wa-step-num">3</div>
                <div class="wa-step-body">
                    <strong>Buka link &amp; masukkan dua OTP</strong>
                    <p>Klik link dari WA → masukkan OTP Pertama dan OTP Kedua → masuk ke akun Anda!</p>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="auth-divider"><span>atau</span></div>

        <a href="{{ route('login') }}" class="btn btn-outline btn-block">
            <i class="fa-solid fa-envelope"></i>
            Login dengan Email &amp; Password
        </a>

        <div class="auth-footer" style="margin-top: 20px; text-align: center;">
            Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
        </div>

    </div>
</div>

<style>
.wa-login-card { max-width: 440px; }

.wa-icon-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}

.wa-icon-wrap svg {
    width: 64px;
    height: 64px;
    filter: drop-shadow(0 4px 16px rgba(37,211,102,.35));
}

.wa-login-card h1 {
    font-size: 1.55rem;
    font-weight: 700;
    text-align: center;
    margin: 0 0 24px;
    color: var(--text);
}

.wa-steps {
    display: flex;
    flex-direction: column;
    gap: 0;
    margin: 4px 0 20px;
}

.wa-step {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    background: var(--surface-alt, #f8fafc);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 12px;
    padding: 14px 16px;
}

.wa-step-num {
    min-width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #25D366;
    color: #fff;
    font-weight: 700;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.wa-step-body strong { display: block; font-size: 0.93rem; color: var(--text); margin-bottom: 3px; }
.wa-step-body p { margin: 0; font-size: 0.83rem; color: var(--text-muted, #64748b); line-height: 1.5; }
.wa-step-body code {
    background: #dcfce7;
    color: #166534;
    padding: 1px 6px;
    border-radius: 5px;
    font-weight: 700;
    font-family: monospace;
}

.wa-step-arrow {
    text-align: center;
    color: #25D366;
    font-size: 0.8rem;
    padding: 4px 0;
    opacity: 0.7;
}

.alert {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.alert-danger { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }

.auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 20px 0;
    color: var(--text-muted, #94a3b8);
    font-size: 0.85rem;
}
.auth-divider::before, .auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border, #e2e8f0);
}

.btn-block { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; }
</style>
@endsection
