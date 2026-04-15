@extends('layouts.app')

@section('title', 'Login via WhatsApp – BatamCraig')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card wa-login-card">

        {{-- Icon --}}
        <div class="wa-icon-wrap">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="24" cy="24" r="24" fill="#25D366"/>
                <path d="M34.6 13.4A14.7 14.7 0 0 0 24 9C16.3 9 10 15.3 10 23c0 2.5.7 4.9 1.9 7L10 39l9.3-2.4A14.9 14.9 0 0 0 24 38c7.7 0 14-6.3 14-15 0-4-.8-7.2-3.4-9.6ZM24 35.3c-2.2 0-4.3-.6-6.2-1.6l-.4-.3-5.5 1.4 1.5-5.3-.3-.5A12.2 12.2 0 0 1 11.7 23C11.7 16.2 17.2 10.7 24 10.7c3.3 0 6.4 1.3 8.7 3.6A12.2 12.2 0 0 1 36.3 23c0 6.8-5.5 12.3-12.3 12.3Zm6.7-9.2c-.4-.2-2.3-1.1-2.6-1.2-.4-.2-.6-.2-.9.2-.3.4-1 1.2-1.3 1.5-.2.3-.5.3-.9.1a10.6 10.6 0 0 1-3.1-1.9 11.7 11.7 0 0 1-2.2-2.7c-.2-.4 0-.6.2-.8l.6-.7.4-.6a.5.5 0 0 0 0-.5l-1.2-2.9c-.3-.7-.6-.6-.9-.6h-.7c-.3 0-.7.1-1 .4-.4.4-1.4 1.3-1.4 3.2s1.4 3.7 1.6 4c.2.3 2.8 4.2 6.7 5.9 4 1.7 4 1.1 4.7 1.1.7 0 2.3-.9 2.6-1.8.3-.9.3-1.7.2-1.8-.1-.1-.3-.2-.7-.3Z" fill="white"/>
            </svg>
        </div>

        <h1>Login via WhatsApp</h1>
        <p class="wa-login-subtitle">
            Masukkan nomor WhatsApp Anda. Sistem akan memverifikasi dan mengirimkan dua kode OTP.
        </p>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('wa-login.verify') }}" method="POST" id="wa-phone-form">
            @csrf
            <div class="form-group">
                <label for="phone">Nomor WhatsApp</label>
                <div class="input-prefix-wrap">
                    <span class="input-prefix">🇮🇩 +62</span>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        class="form-control with-prefix @error('phone') is-invalid @enderror"
                        placeholder="812xxxx"
                        value="{{ old('phone') }}"
                        required
                        autofocus
                        inputmode="numeric"
                        pattern="[0-9\+\-\s()]+"
                    >
                </div>
                <small class="form-hint">Contoh: 0812xxxx atau 62812xxxx</small>
            </div>

            <button type="submit" class="btn btn-wa btn-block" id="phone-submit-btn">
                <i class="fa-brands fa-whatsapp"></i>
                Cek & Kirim OTP via WhatsApp
            </button>
        </form>

        {{-- Divider --}}
        <div class="auth-divider"><span>atau</span></div>

        <div class="auth-alt-links">
            <a href="{{ route('login') }}" class="btn btn-outline btn-block">
                <i class="fa-solid fa-envelope"></i>
                Login dengan Email & Password
            </a>
        </div>

        <div class="auth-footer" style="margin-top: 24px;">
            Belum punya akun?
            <a href="{{ route('register') }}">Daftar sekarang</a>
            &nbsp;atau kirim kata kunci <strong>login</strong> ke WhatsApp kami.
        </div>

        {{-- Info box --}}
        <div class="wa-info-box">
            <i class="fa-solid fa-circle-info"></i>
            <div>
                <strong>Cara kerja:</strong>
                <ol>
                    <li>Kirim kata kunci <code>login</code> ke nomor WhatsApp kami.</li>
                    <li>Sistem mengirimkan dua kode OTP ke WhatsApp Anda.</li>
                    <li>Masukkan nomor WA Anda di sini, lalu isi kedua OTP.</li>
                    <li>Anda langsung masuk tanpa perlu email &amp; password!</li>
                </ol>
            </div>
        </div>

    </div>
</div>

<style>
.wa-login-card {
    max-width: 440px;
}

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
    font-size: 1.65rem;
    font-weight: 700;
    text-align: center;
    margin: 0 0 8px;
    color: var(--text);
}

.wa-login-subtitle {
    text-align: center;
    color: var(--text-muted, #94a3b8);
    font-size: 0.93rem;
    margin-bottom: 24px;
    line-height: 1.5;
}

.input-prefix-wrap {
    display: flex;
    align-items: stretch;
    border: 1.5px solid var(--border, #e2e8f0);
    border-radius: 10px;
    overflow: hidden;
    transition: border-color .2s;
}

.input-prefix-wrap:focus-within {
    border-color: #25D366;
    box-shadow: 0 0 0 3px rgba(37,211,102,.15);
}

.input-prefix {
    padding: 0 14px;
    background: var(--surface-alt, #f8fafc);
    color: var(--text-muted, #64748b);
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    white-space: nowrap;
    border-right: 1.5px solid var(--border, #e2e8f0);
}

.form-control.with-prefix {
    border: none !important;
    box-shadow: none !important;
    border-radius: 0 10px 10px 0;
    flex: 1;
}

.form-hint {
    font-size: 0.8rem;
    color: var(--text-muted, #94a3b8);
    margin-top: 5px;
    display: block;
}

.btn-wa {
    background: linear-gradient(135deg, #25D366 0%, #1aa856 100%);
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    border: none;
    border-radius: 10px;
    padding: 14px 20px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s;
    box-shadow: 0 4px 14px rgba(37,211,102,.35);
    text-decoration: none;
}

.btn-wa:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37,211,102,.45);
}

.btn-block { display: flex; width: 100%; justify-content: center; gap: 8px; }

.auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 20px 0;
    color: var(--text-muted, #94a3b8);
    font-size: 0.85rem;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border, #e2e8f0);
}

.alert {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 18px;
    font-size: 0.9rem;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.alert-danger {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.wa-info-box {
    display: flex;
    gap: 12px;
    background: var(--surface-alt, #f0fdf4);
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 14px 16px;
    margin-top: 24px;
    font-size: 0.85rem;
    color: #166534;
    line-height: 1.6;
}

.wa-info-box i { margin-top: 2px; flex-shrink: 0; color: #16a34a; }
.wa-info-box ol { margin: 4px 0 0 16px; padding: 0; }
.wa-info-box li { margin-bottom: 3px; }
.wa-info-box code {
    background: #dcfce7;
    padding: 1px 5px;
    border-radius: 4px;
    font-family: monospace;
    font-weight: 700;
}
</style>
@endsection
