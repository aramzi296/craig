@extends('layouts.app')

@section('content')
<div class="container" style="padding: 100px 20px; text-align: center; min-height: 70vh; display: flex; align-items: center; justify-content: center;">
    <div class="glass" style="max-width: 600px; padding: 60px 40px; border-radius: 30px; box-shadow: var(--shadow-lg);">
        <div style="background: #f0fdf4; width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; border: 4px solid #bbf7d0;">
            <i class="fa-solid fa-check" style="font-size: 3.5rem; color: #22c55e;"></i>
        </div>
        
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 20px; color: var(--text);">Terima Kasih!</h1>
        <p style="font-size: 1.15rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 40px;">
            Konfirmasi pembayaran Anda telah kami terima. Admin kami akan segera melakukan verifikasi dan mengaktifkan fitur <strong>Premium</strong> untuk iklan Anda dalam waktu maksimal 1x24 jam.
        </p>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            <a href="{{ route('dashboard') }}" class="btn btn-primary" style="padding: 16px; border-radius: 12px; font-weight: 700; font-size: 1.1rem;">
                Kembali ke Dashboard
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline" style="padding: 12px; border-radius: 12px; font-weight: 600;">
                Lihat Situs
            </a>
        </div>
    </div>
</div>
@endsection
