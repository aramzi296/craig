@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Tambah Kategori</h1>
    <p style="color: var(--text-muted);">Buat kategori baru untukListing di BatamCraig.</p>
</div>

<div class="glass" style="max-width: 600px; padding: 40px; border-radius: var(--radius);">
    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nama Kategori</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Barang Elektronik" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="icon">Ikon (FontAwesome Name)</label>
            <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon') }}" placeholder="Contoh: laptop, camera, car" required>
            <small style="color: var(--text-muted);">Gunakan nama class FontAwesome tanpa prefix 'fa-'.</small>
            @error('icon')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="sort_order">Nomor Urut</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}" required>
            <small style="color: var(--text-muted);">Urutan kategori saat ditampilkan (semakin kecil semakin di atas).</small>
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Simpan Kategori</button>
            <a href="{{ route('admin.categories') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
        </div>
    </form>
</div>
@endsection
