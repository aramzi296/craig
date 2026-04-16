@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Daftar Akun</h2>
        <p>Bergabung dengan komunitas BatamCraig</p>

        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px;">Daftar</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
        </div>
        <div class="mt-6 p-4 bg-yellow-100 border border-yellow-300 text-yellow-800 rounded">
            <strong>Perhatian:</strong> Untuk registrasi menggunakan WhatsApp, cukup kirim pesan "login" ke nomor admin.
            <div class="mt-4">
                @php
    $whatsappAdminNumber = env('WHATSAPP_ADMIN_NUMBER', '6282172292230');
@endphp
<a href="https://wa.me/{{ $whatsappAdminNumber }}?text=login" target="_blank" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold px-5 py-3 rounded">
    Kirim WhatsApp: login
</a>
            </div>
        </div>
    </div>
</div>
@endsection
