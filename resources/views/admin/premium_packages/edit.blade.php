@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Edit Paket Premium</h1>
    <p style="color: var(--text-muted);">Ubah informasi paket: {{ $package->name }}</p>
</div>

<div class="glass" style="max-width: 600px; padding: 40px; border-radius: var(--radius);">
    <form action="{{ route('admin.premium_packages.update', $package->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="name">Nama Paket</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $package->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="price">Harga (Rp)</label>
            <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', (int)$package->price) }}" required>
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="duration_days">Durasi (Hari)</label>
            <input type="number" name="duration_days" id="duration_days" class="form-control @error('duration_days') is-invalid @enderror" value="{{ old('duration_days', $package->duration_days) }}" required>
            @error('duration_days')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 30px;">
            <label for="is_active">Status Aktif</label>
            <select name="is_active" id="is_active" class="form-control">
                <option value="1" {{ $package->is_active ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ !$package->is_active ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Perbarui Paket</button>
            <a href="{{ route('admin.premium_packages') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
        </div>
    </form>
</div>
@endsection
