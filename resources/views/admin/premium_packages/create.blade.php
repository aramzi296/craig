@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Tambah Paket Premium</h1>
    <p style="color: var(--text-muted);">Buat definisi paket baru untuk pengguna.</p>
</div>

<div class="glass" style="max-width: 600px; padding: 40px; border-radius: var(--radius);">
    <form action="{{ route('admin.premium_packages.store') }}" method="POST">
        @csrf
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="name">Nama Paket</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Paket Bulanan, Paket Sultan" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="price">Harga (Rp)</label>
            <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" placeholder="Contoh: 50000" required>
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 30px;">
            <label for="duration_days">Durasi (Hari)</label>
            <input type="number" name="duration_days" id="duration_days" class="form-control @error('duration_days') is-invalid @enderror" value="{{ old('duration_days') }}" placeholder="Contoh: 30" required>
            @error('duration_days')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Simpan Paket</button>
            <a href="{{ route('admin.premium_packages') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
        </div>
    </form>
</div>
@endsection
