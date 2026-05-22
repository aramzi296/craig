@extends($layout)

@section('title', 'Aktivasi Usaha Sebatam – ' . config('app.name'))

@section($section)
<div class="activation-container" style="display: flex; justify-content: center; align-items: center; min-height: 70vh; padding: 20px 10px;">
    <div class="activation-card" style="background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); padding: 40px; max-width: 520px; width: 100%; border: 1px solid #e2e8f0; text-align: center; position: relative; overflow: hidden; animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- Background gradient subtle glow --}}
        <div style="position: absolute; top: -50px; left: -50px; width: 150px; height: 150px; background: rgba(37, 211, 102, 0.1); filter: blur(50px); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -50px; right: -50px; width: 150px; height: 150px; background: rgba(14, 165, 233, 0.1); filter: blur(50px); border-radius: 50%;"></div>

        {{-- Icon --}}
        <div class="activation-icon-wrap" style="width: 80px; height: 80px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; box-shadow: 0 8px 16px rgba(37, 211, 102, 0.15); animation: pulseGreen 2s infinite;">
            <i class="fa-brands fa-whatsapp" style="color: #25D366; font-size: 2.8rem;"></i>
        </div>

        {{-- Title --}}
        <h2 style="font-size: 1.6rem; font-weight: 800; color: #1e293b; margin-bottom: 10px;">Pendaftaran Usaha Berhasil!</h2>
        <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px;">
            Usaha Anda <strong style="color: #0f172a;">"{{ $listing->title }}"</strong> telah berhasil didaftarkan di sistem kami.
        </p>

        {{-- OTP Box --}}
        <div class="otp-box-container" style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 25px 20px; margin-bottom: 30px; position: relative;">
            <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 8px;">Kode Aktivasi Anda</div>
            <div class="activation-code-text" style="font-size: 3.2rem; font-weight: 900; letter-spacing: 0.2em; color: #0f172a; text-indent: 0.2em; text-shadow: 1px 1px 0 #fff, 2px 2px 0 #cbd5e1;">{{ $otp }}</div>
            <div style="font-size: 0.82rem; color: #94a3b8; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i class="fa-solid fa-clock"></i> Berlaku selama masa proses aktivasi
            </div>
        </div>

        {{-- Guide Steps --}}
        <div style="text-align: left; background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 12px; padding: 20px; margin-bottom: 30px;">
            <h4 style="font-weight: 700; color: #15803d; font-size: 0.95rem; margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-info"></i> Langkah-langkah Aktivasi:
            </h4>
            <ol style="margin: 0; padding-left: 20px; color: #166534; font-size: 0.88rem; line-height: 1.6; display: flex; flex-direction: column; gap: 10px;">
                <li>
                    Kirim pesan WhatsApp berisi tulisan:<br>
                    <code style="background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 4px; font-weight: 800; font-family: monospace; font-size: 0.95rem; border: 1px solid #bbf7d0;">Aktivasi {{ $otp }}</code>
                </li>
                <li>
                    Kirim pesan tersebut ke WhatsApp Bot kami di nomor: 
                    <strong style="color: #14532d;">{{ $botNumber }}</strong>.
                </li>
                <li>
                    Kirim pesan ini khusus menggunakan nomor WhatsApp terdaftar Anda:<br>
                    <strong style="color: #14532d;">{{ $whatsapp }}</strong>.
                </li>
            </ol>
        </div>

        {{-- Action Button --}}
        <a href="https://wa.me/{{ $botNumber }}?text=Aktivasi%20{{ $otp }}" target="_blank" class="btn-wa-activate" style="display: flex; align-items: center; justify-content: center; gap: 10px; background: linear-gradient(135deg, #25D366 0%, #1aa856 100%); color: white; border: none; padding: 16px 28px; border-radius: 12px; font-weight: 700; font-size: 1.05rem; text-decoration: none; box-shadow: 0 8px 24px rgba(37, 211, 102, 0.35); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
            <i class="fa-brands fa-whatsapp" style="font-size: 1.4rem;"></i>
            Aktivasi Lewat WhatsApp
        </a>

        <div style="margin-top: 25px;">
            <a href="{{ route('home') }}" style="color: #64748b; font-size: 0.88rem; text-decoration: none; font-weight: 600; transition: color 0.2s;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>

<style>
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulseGreen {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.4);
    }
    70% {
        box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
    }
}

.btn-wa-activate:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(37, 211, 102, 0.5) !important;
}

.btn-wa-activate:active {
    transform: translateY(-1px);
}
</style>
@endsection
