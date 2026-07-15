@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 450px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin-bottom: 5px;">Verifikasi OTP</h2>
            <p>Masukkan 6 digit kode OTP yang telah dikirimkan ke email Anda.</p>
        </div>

        <form action="{{ route('login.otp.verify') }}" method="POST">
            @csrf
            
            @if(session('error'))
                <div class="alert alert-danger" style="color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    {{ session('error') }}
                </div>
            @endif

            <div class="form-group">
                <label for="otp">Kode OTP</label>
                <input type="text" name="otp" id="otp" class="form-control @error('otp') is-invalid @enderror" placeholder="Contoh: 123456" required autofocus maxlength="6" style="letter-spacing: 5px; font-size: 1.5rem; text-align: center;">
                @error('otp')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 1rem; margin-top: 15px;">Verifikasi & Masuk</button>
        </form>

        <div class="auth-footer" style="margin-top: 25px;">
            <a href="{{ route('login') }}">&larr; Kembali ke halaman login</a>
        </div>
    </div>
</div>
@endsection
