@extends('layouts.app')

@section('title', 'Login via WhatsApp OTP – BatamCraig')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card wa-login-card">

        {{-- ── Header ─────────────────────────────────────────────────────── --}}
        <div class="wa-header">
            <div class="wa-icon">
                <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="24" cy="24" r="24" fill="#25D366"/>
                    <path d="M34.6 13.4A14.7 14.7 0 0 0 24 9C16.3 9 10 15.3 10 23c0 2.5.7 4.9 1.9 7L10 39l9.3-2.4A14.9 14.9 0 0 0 24 38c7.7 0 14-6.3 14-15 0-4-.8-7.2-3.4-9.6ZM24 35.3c-2.2 0-4.3-.6-6.2-1.6l-.4-.3-5.5 1.4 1.5-5.3-.3-.5A12.2 12.2 0 0 1 11.7 23C11.7 16.2 17.2 10.7 24 10.7c3.3 0 6.4 1.3 8.7 3.6A12.2 12.2 0 0 1 36.3 23c0 6.8-5.5 12.3-12.3 12.3Zm6.7-9.2c-.4-.2-2.3-1.1-2.6-1.2-.4-.2-.6-.2-.9.2-.3.4-1 1.2-1.3 1.5-.2.3-.5.3-.9.1a10.6 10.6 0 0 1-3.1-1.9 11.7 11.7 0 0 1-2.2-2.7c-.2-.4 0-.6.2-.8l.6-.7.4-.6a.5.5 0 0 0 0-.5l-1.2-2.9c-.3-.7-.6-.6-.9-.6h-.7c-.3 0-.7.1-1 .4-.4.4-1.4 1.3-1.4 3.2s1.4 3.7 1.6 4c.2.3 2.8 4.2 6.7 5.9 4 1.7 4 1.1 4.7 1.1.7 0 2.3-.9 2.6-1.8.3-.9.3-1.7.2-1.8-.1-.1-.3-.2-.7-.3Z" fill="white"/>
                </svg>
            </div>
            <div>
                <h1>Login via WhatsApp</h1>
                <p>Masukkan dua kode OTP yang dikirim ke WhatsApp Anda</p>
            </div>
        </div>

        {{-- ── Cara mendapatkan OTP ─────────────────────────────────────── --}}
        <div class="wa-how-to">
            <div class="wa-how-to-title">
                <i class="fa-solid fa-circle-info"></i>
                Belum punya kode OTP?
            </div>
            <p>
                Buka WhatsApp dan kirim pesan <code>login</code> ke nomor bot kami 
                (<a href="https://wa.me/{{ env('WHATSAPP_ADMIN_NUMBER', '6282172292230') }}?text=login" target="_blank" style="color: inherit; font-weight: 700; text-decoration: underline;">{{ env('WHATSAPP_ADMIN_NUMBER', '6282172292230') }}</a>).
                Sistem akan membalas dengan dua kode OTP yang berlaku <strong>10 menit</strong>.
            </p>
        </div>

        {{-- ── Errors ──────────────────────────────────────────────────────── --}}
        @if ($errors->any())
            <div class="wa-alert wa-alert-danger" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Form: OTP1 + OTP2 ───────────────────────────────────────────── --}}
        <form action="{{ route('wa-login.verify') }}" method="POST" id="wa-otp-form" autocomplete="off">
            @csrf

            {{-- OTP 1 --}}
            <div class="form-group">
                <label for="otp1">
                    <span class="otp-badge badge-1">1</span>
                    OTP Pertama
                </label>
                <div class="otp-wrap">
                    <input
                        type="text"
                        name="otp1"
                        id="otp1"
                        class="form-control otp-input @error('otp1') is-invalid @enderror"
                        placeholder="• • • • • •"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                        autofocus
                        value="{{ old('otp1') }}"
                    >
                    <button type="button" class="otp-eye" onclick="toggleOtp('otp1',this)" aria-label="Tampilkan OTP 1">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                @error('otp1')
                    <div class="field-error">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- OTP 2 --}}
            <div class="form-group" style="margin-top: 18px;">
                <label for="otp2">
                    <span class="otp-badge badge-2">2</span>
                    OTP Kedua
                </label>
                <div class="otp-wrap">
                    <input
                        type="text"
                        name="otp2"
                        id="otp2"
                        class="form-control otp-input @error('otp2') is-invalid @enderror"
                        placeholder="• • • • • •"
                        maxlength="6"
                        inputmode="numeric"
                        autocomplete="off"
                        required
                        value="{{ old('otp2') }}"
                    >
                    <button type="button" class="otp-eye" onclick="toggleOtp('otp2',this)" aria-label="Tampilkan OTP 2">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                @error('otp2')
                    <div class="field-error">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-wa-verify" id="wa-submit-btn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Verifikasi &amp; Masuk
            </button>
        </form>

        {{-- ── Divider + link email login ─────────────────────────────────── --}}
        <div class="wa-divider"><span>atau</span></div>

        <a href="{{ route('login') }}" class="btn btn-outline btn-block">
            <i class="fa-solid fa-envelope"></i>
            Login dengan Email &amp; Password
        </a>

        <div class="wa-footer">
            Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
        </div>

    </div>
</div>

<style>
/* ── Card ────────────────────────────────────────────────────────────────── */
.wa-login-card { max-width: 430px; }

/* ── Header ──────────────────────────────────────────────────────────────── */
.wa-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 22px;
}
.wa-icon {
    flex-shrink: 0;
    width: 56px;
    height: 56px;
}
.wa-icon svg {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 3px 10px rgba(37,211,102,.4));
}
.wa-header h1 {
    font-size: 1.4rem;
    font-weight: 700;
    margin: 0 0 3px;
    color: var(--text);
    line-height: 1.2;
}
.wa-header p {
    margin: 0;
    font-size: 0.84rem;
    color: var(--text-muted, #64748b);
}

/* ── How-to box ──────────────────────────────────────────────────────────── */
.wa-how-to {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 12px 15px;
    margin-bottom: 20px;
    font-size: 0.85rem;
    color: #166534;
    line-height: 1.55;
}
.wa-how-to-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-weight: 700;
    margin-bottom: 5px;
    color: #15803d;
}
.wa-how-to .fa-circle-info { color: #16a34a; }
.wa-how-to p { margin: 0; }
.wa-how-to code {
    background: #dcfce7;
    padding: 1px 6px;
    border-radius: 4px;
    font-weight: 700;
    font-family: monospace;
    font-size: .9em;
}

/* ── Alert ───────────────────────────────────────────────────────────────── */
.wa-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    border-radius: 10px;
    padding: 12px 15px;
    margin-bottom: 18px;
    font-size: 0.88rem;
    line-height: 1.5;
}
.wa-alert-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
}
.wa-alert i { flex-shrink: 0; margin-top: 2px; }

/* ── OTP inputs ──────────────────────────────────────────────────────────── */
.otp-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: .72rem;
    font-weight: 700;
    margin-right: 6px;
    vertical-align: middle;
}
.badge-1 { background: #dbeafe; color: #1d4ed8; }
.badge-2 { background: #ede9fe; color: #6d28d9; }

.otp-wrap { position: relative; display: flex; align-items: center; }

.otp-input {
    font-size: 1.6rem;
    letter-spacing: .6rem;
    text-align: center;
    font-weight: 700;
    padding-right: 50px !important;
    font-variant-numeric: tabular-nums;
    border-radius: 10px;
}
.otp-input:focus { border-color: #25D366; box-shadow: 0 0 0 3px rgba(37,211,102,.18); }

.otp-eye {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted, #94a3b8);
    padding: 4px;
    transition: color .2s;
    line-height: 1;
}
.otp-eye:hover { color: var(--primary, #0ea5e9); }

.field-error {
    font-size: .82rem;
    color: #b91c1c;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ── Submit button ───────────────────────────────────────────────────────── */
.btn-wa-verify {
    margin-top: 26px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    background: linear-gradient(135deg, #25D366 0%, #1aa856 100%);
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    box-shadow: 0 4px 14px rgba(37,211,102,.35);
    transition: transform .15s, box-shadow .15s;
}
.btn-wa-verify:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37,211,102,.5);
}

/* ── Divider & links ─────────────────────────────────────────────────────── */
.wa-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 22px 0;
    color: var(--text-muted, #94a3b8);
    font-size: .85rem;
}
.wa-divider::before,
.wa-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border, #e2e8f0);
}

.btn-block {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
}

.wa-footer {
    margin-top: 18px;
    text-align: center;
    font-size: .85rem;
    color: var(--text-muted, #64748b);
}
.wa-footer a { color: var(--primary, #0ea5e9); font-weight: 600; }
</style>

<script>
// ── Show / hide OTP value ─────────────────────────────────────────────────
function toggleOtp(id, btn) {
    var input = document.getElementById(id);
    var icon  = btn.querySelector('i');
    if (input.type === 'text') {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
}

// ── Digits only ───────────────────────────────────────────────────────────
['otp1', 'otp2'].forEach(function (id) {
    var el = document.getElementById(id);
    el.addEventListener('keydown', function (e) {
        if (e.key.length === 1 && !/\d/.test(e.key) && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
        }
    });
    el.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });
});

// ── Auto-advance: OTP1 full → focus OTP2 ─────────────────────────────────
document.getElementById('otp1').addEventListener('input', function () {
    if (this.value.length >= 6) {
        document.getElementById('otp2').focus();
    }
});
</script>
@endsection
