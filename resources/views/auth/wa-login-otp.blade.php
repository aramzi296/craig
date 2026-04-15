@extends('layouts.app')

@section('title', 'Masukkan Kode OTP – Login WhatsApp')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card wa-otp-card">

        {{-- Header --}}
        <div class="wa-otp-header">
            <div class="wa-otp-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h1>Verifikasi OTP WhatsApp</h1>
            @if(!empty($maskedName))
                <p class="wa-login-subtitle">
                    Halo, <strong>{{ $maskedName }}</strong>! Masukkan dua kode OTP yang telah dikirim ke WhatsApp Anda.
                </p>
            @else
                <p class="wa-login-subtitle">
                    Masukkan dua kode OTP yang telah dikirim ke WhatsApp Anda.
                </p>
            @endif
        </div>

        {{-- Countdown timer --}}
        <div class="otp-countdown" id="otp-countdown">
            <i class="fa-regular fa-clock"></i>
            OTP berlaku selama&nbsp;<span id="countdown-timer">10:00</span>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-circle-exclamation" style="flex-shrink:0;margin-top:2px;"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('wa-login.verify-otp', $nonce) }}" method="POST" id="otp-form" autocomplete="off">
            @csrf

            {{-- ── OTP 1 ─────────────────────────────────────────────────── --}}
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
                    >
                    <button type="button" class="otp-eye" onclick="toggleOtp('otp1',this)" aria-label="Tampilkan OTP 1">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                @error('otp1')
                    <div class="field-error"><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- ── OTP 2 ─────────────────────────────────────────────────── --}}
            <div class="form-group" style="margin-top:18px;">
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
                    >
                    <button type="button" class="otp-eye" onclick="toggleOtp('otp2',this)" aria-label="Tampilkan OTP 2">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                @error('otp2')
                    <div class="field-error"><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-verify" id="otp-submit-btn" style="margin-top:28px;">
                <i class="fa-solid fa-right-to-bracket"></i>
                Verifikasi &amp; Masuk
            </button>
        </form>

        {{-- Resend hint --}}
        <div class="otp-hint">
            <i class="fa-brands fa-whatsapp"></i>
            OTP kedaluwarsa? Kirim kata kunci <code>login</code> kembali ke WhatsApp kami —
            sistem akan mengirim link &amp; OTP baru.
        </div>

        <div class="auth-footer" style="margin-top:18px; text-align:center; font-size:.85rem;">
            <a href="{{ route('login') }}">← Login dengan email</a>
        </div>

    </div>
</div>

<style>
.wa-otp-card { max-width: 420px; }

/* ── Header ─────────────────────────────────────────────────────────────── */
.wa-otp-header { text-align:center; margin-bottom:20px; }

.wa-otp-icon {
    width:64px; height:64px; border-radius:50%;
    background:linear-gradient(135deg,#3b82f6,#6366f1);
    color:#fff; font-size:1.8rem;
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 14px;
    box-shadow:0 4px 18px rgba(99,102,241,.35);
}

.wa-otp-card h1 { font-size:1.5rem; font-weight:700; margin:0 0 8px; }
.wa-login-subtitle { color:var(--text-muted,#94a3b8); font-size:.88rem; margin:0; line-height:1.5; }

/* ── Countdown ──────────────────────────────────────────────────────────── */
.otp-countdown {
    display:flex; align-items:center; justify-content:center; gap:8px;
    background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px;
    padding:10px 16px; margin-bottom:20px;
    font-size:.88rem; color:#1e40af; font-weight:600;
    transition: background .4s, color .4s, border-color .4s;
}
#countdown-timer { font-variant-numeric:tabular-nums; }

/* ── OTP inputs ─────────────────────────────────────────────────────────── */
.otp-badge {
    display:inline-flex; align-items:center; justify-content:center;
    width:22px; height:22px; border-radius:50%;
    font-size:.75rem; font-weight:700; margin-right:6px;
}
.badge-1 { background:#dbeafe; color:#1d4ed8; }
.badge-2 { background:#ede9fe; color:#6d28d9; }

.otp-wrap { position:relative; display:flex; align-items:center; }

.otp-input {
    font-size:1.6rem;
    letter-spacing:.55rem;
    text-align:center;
    font-weight:700;
    padding-right:48px !important;
    font-variant-numeric:tabular-nums;
}

.otp-eye {
    position:absolute; right:12px;
    background:none; border:none; cursor:pointer;
    color:var(--text-muted,#94a3b8);
    padding:4px; transition:color .2s;
}
.otp-eye:hover { color:var(--primary,#0ea5e9); }

.field-error {
    font-size:.82rem; color:#b91c1c;
    margin-top:5px; display:flex; align-items:center; gap:5px;
}

/* ── Submit button ──────────────────────────────────────────────────────── */
.btn-verify {
    width:100%; display:flex; align-items:center; justify-content:center; gap:10px;
    padding:14px 20px; border:none; border-radius:10px; cursor:pointer;
    background:linear-gradient(135deg,#6366f1,#3b82f6);
    color:#fff; font-weight:700; font-size:1rem;
    box-shadow:0 4px 14px rgba(99,102,241,.35);
    transition:transform .15s, box-shadow .15s;
    text-decoration:none;
}
.btn-verify:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(99,102,241,.45); }

/* ── Hint box ───────────────────────────────────────────────────────────── */
.otp-hint {
    display:flex; gap:10px; align-items:flex-start;
    background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px;
    padding:12px 14px; margin-top:22px;
    font-size:.83rem; color:#166534; line-height:1.6;
}
.otp-hint i { color:#25D366; flex-shrink:0; margin-top:2px; }
.otp-hint code { background:#dcfce7; padding:1px 5px; border-radius:4px; font-weight:700; }

/* ── Alert ──────────────────────────────────────────────────────────────── */
.alert { padding:12px 16px; border-radius:10px; margin-bottom:18px; font-size:.9rem; display:flex; align-items:flex-start; gap:10px; }
.alert-danger { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
</style>

<script>
// ── Show / hide OTP value ─────────────────────────────────────────────────
function toggleOtp(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
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

// ── Auto-advance from OTP1 → OTP2 when full ─────────────────────────────
document.getElementById('otp1').addEventListener('input', function () {
    if (this.value.length >= 6) document.getElementById('otp2').focus();
});

// ── Countdown (10 min) ───────────────────────────────────────────────────
(function () {
    var total = 10 * 60;
    var el    = document.getElementById('countdown-timer');
    var wrap  = document.getElementById('otp-countdown');

    function tick() {
        if (total <= 0) {
            el.textContent = '00:00';
            wrap.style.cssText += 'background:#fef2f2;border-color:#fecaca;color:#b91c1c;';
            return;
        }
        total--;
        var m = String(Math.floor(total / 60)).padStart(2, '0');
        var s = String(total % 60).padStart(2, '0');
        el.textContent = m + ':' + s;

        if (total < 120) {
            wrap.style.background   = '#fff7ed';
            wrap.style.borderColor  = '#fed7aa';
            wrap.style.color        = '#c2410c';
        }
        setTimeout(tick, 1000);
    }
    setTimeout(tick, 1000);
})();
</script>
@endsection
