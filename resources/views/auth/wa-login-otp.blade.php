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
            <h1>Verifikasi Dua Kode OTP</h1>
            <p class="wa-login-subtitle">
                Masukkan dua kode OTP yang telah dikirimkan ke WhatsApp
                <strong>{{ '****' . substr($phone, -4) }}</strong>
            </p>
        </div>

        {{-- Countdown --}}
        <div class="otp-countdown" id="otp-countdown">
            <i class="fa-regular fa-clock"></i>
            OTP berlaku selama <span id="countdown-timer">10:00</span>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('wa-login.verify-otp') }}" method="POST" id="otp-form">
            @csrf

            {{-- OTP 1 --}}
            <div class="form-group">
                <label for="otp1">
                    <span class="otp-label-badge otp1-badge">1</span>
                    OTP Pertama
                </label>
                <div class="otp-input-wrap">
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
                    <button type="button" class="otp-toggle-btn" onclick="toggleOtp('otp1', this)" aria-label="Tampilkan/sembunyikan OTP 1">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                @error('otp1')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- OTP 2 --}}
            <div class="form-group" style="margin-top: 18px;">
                <label for="otp2">
                    <span class="otp-label-badge otp2-badge">2</span>
                    OTP Kedua
                </label>
                <div class="otp-input-wrap">
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
                    <button type="button" class="otp-toggle-btn" onclick="toggleOtp('otp2', this)" aria-label="Tampilkan/sembunyikan OTP 2">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                @error('otp2')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-wa btn-block" style="margin-top: 28px;" id="otp-submit-btn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Verifikasi &amp; Masuk
            </button>
        </form>

        {{-- Resend instruction --}}
        <div class="otp-resend-info">
            <i class="fa-brands fa-whatsapp"></i>
            OTP kedaluwarsa?
            Kirim kata kunci <code>login</code> kembali ke WhatsApp kami, lalu
            <a href="{{ route('wa-login') }}">masukkan nomor WA Anda lagi</a>.
        </div>

        <div class="auth-footer" style="margin-top: 20px;">
            <a href="{{ route('wa-login') }}">← Ganti nomor WhatsApp</a>
            &nbsp;|&nbsp;
            <a href="{{ route('login') }}">Login dengan email</a>
        </div>

    </div>
</div>

<style>
.wa-otp-card { max-width: 440px; }

.wa-otp-header { text-align: center; margin-bottom: 24px; }

.wa-otp-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
    color: #fff;
    font-size: 1.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    box-shadow: 0 4px 16px rgba(99,102,241,.35);
}

.wa-otp-card h1 { font-size: 1.55rem; font-weight: 700; margin: 0 0 8px; }

.wa-login-subtitle { color: var(--text-muted, #94a3b8); font-size: 0.9rem; line-height: 1.5; margin: 0; }

.otp-countdown {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 0.88rem;
    color: #1e40af;
    margin-bottom: 20px;
    font-weight: 600;
}

#countdown-timer { font-variant-numeric: tabular-nums; }

.otp-label-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 700;
    margin-right: 6px;
}

.otp1-badge { background: #dbeafe; color: #1d4ed8; }
.otp2-badge { background: #ede9fe; color: #6d28d9; }

.otp-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.otp-input {
    font-size: 1.5rem;
    letter-spacing: 0.5rem;
    text-align: center;
    font-weight: 700;
    padding-right: 48px;
    font-variant-numeric: tabular-nums;
}

.otp-toggle-btn {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted, #94a3b8);
    padding: 4px;
    transition: color .2s;
}

.otp-toggle-btn:hover { color: var(--primary, #0ea5e9); }

.field-error {
    font-size: 0.82rem;
    color: #b91c1c;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.otp-resend-info {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    padding: 12px 16px;
    margin-top: 24px;
    font-size: 0.85rem;
    color: #166534;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    line-height: 1.6;
}

.otp-resend-info i { color: #25D366; flex-shrink: 0; margin-top: 2px; }
.otp-resend-info code {
    background: #dcfce7;
    padding: 1px 5px;
    border-radius: 4px;
    font-weight: 700;
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

.btn-wa {
    background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);
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
    box-shadow: 0 4px 14px rgba(99,102,241,.35);
    text-decoration: none;
}

.btn-wa:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99,102,241,.45);
}

.btn-block { display: flex; width: 100%; justify-content: center; }
</style>

<script>
// ── OTP show/hide toggle ──────────────────────────────────────────────────
function toggleOtp(fieldId, btn) {
    const input = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    if (input.type === 'text') {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
}

// ── Auto-advance cursor from OTP1 to OTP2 when 6 digits entered ──────────
document.getElementById('otp1').addEventListener('input', function () {
    if (this.value.replace(/\D/g, '').length >= 6) {
        document.getElementById('otp2').focus();
    }
});

// ── Allow only digits in OTP inputs ──────────────────────────────────────
['otp1','otp2'].forEach(function (id) {
    document.getElementById(id).addEventListener('keydown', function (e) {
        if (e.key.length === 1 && !/\d/.test(e.key) && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
        }
    });
});

// ── Countdown timer (10 min) ──────────────────────────────────────────────
(function () {
    let total = 10 * 60; // seconds
    const el  = document.getElementById('countdown-timer');
    const wrap = document.getElementById('otp-countdown');

    function tick() {
        if (total <= 0) {
            el.textContent = '00:00';
            wrap.style.background  = '#fef2f2';
            wrap.style.borderColor = '#fecaca';
            wrap.style.color       = '#b91c1c';
            return;
        }
        total--;
        const m = String(Math.floor(total / 60)).padStart(2, '0');
        const s = String(total % 60).padStart(2, '0');
        el.textContent = m + ':' + s;

        if (total < 120) {
            wrap.style.background  = '#fff7ed';
            wrap.style.borderColor = '#fed7aa';
            wrap.style.color       = '#c2410c';
        }
        setTimeout(tick, 1000);
    }
    setTimeout(tick, 1000);
})();
</script>
@endsection
