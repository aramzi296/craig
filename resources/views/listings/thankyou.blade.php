@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 600px; margin: 50px auto; text-align: center; padding: 30px; background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
    
    <div style="font-size: 4rem; color: #10b981; margin-bottom: 20px;">
        <i class="fa-regular fa-circle-check"></i>
    </div>
    
    <h2 style="margin-bottom: 15px; font-weight: 700; color: #1e293b;">Terima Kasih!</h2>
    
    <p style="color: #475569; line-height: 1.6; margin-bottom: 25px; font-size: 1.1rem;">
        Data informasi usaha Anda telah berhasil kami simpan. Saat ini listing Anda sedang dalam tahap antrean dan menunggu verifikasi oleh tim admin kami.
    </p>
    
    <div style="background-color: #f1f5f9; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
        <p style="margin: 0; color: #334155; font-size: 0.95rem;">
            Proses peninjauan biasanya memakan waktu maksimal 1x24 jam. Kami akan menayangkan listing Anda segera setelah disetujui.
        </p>
    </div>

    <div>
        <a href="{{ route('home') }}" class="btn btn-primary" style="padding: 12px 25px; font-weight: 600;">Kembali ke Beranda</a>
        @auth
            <a href="{{ route('dashboard') }}" class="btn" style="padding: 12px 25px; font-weight: 600; color: #3b82f6; background-color: #eff6ff; margin-left: 10px;">Lihat Dasbor Saya</a>
        @endauth
    </div>
</div>
@endsection
