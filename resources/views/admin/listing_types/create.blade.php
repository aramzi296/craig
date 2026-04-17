@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Tambah Tipe Listing</h1>
    <p style="color: var(--text-muted);">Buat label tipe baru untuk listing.</p>
</div>

<div class="glass" style="max-width: 600px; padding: 40px; border-radius: var(--radius);">
    <form action="{{ route('admin.listing_types.store') }}" method="POST">
        @csrf
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="name">Nama Tipe</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Dijual, Jasa, Dicari" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 25px;">
            <label for="sort_order">Urutan Tampilan</label>
            <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}" min="0" required>
            <small style="color: var(--text-muted); display: block; margin-top: 8px;">Semakin kecil angkanya, semakin awal ditampilkan.</small>
            @error('sort_order')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 30px;">

            <label for="color">Warna Label (Hex)</label>
            <div style="display: flex; gap: 15px; align-items: center;">
                <input type="color" name="color" id="color" value="{{ old('color', '#0ea5e9') }}" style="width: 60px; height: 50px; padding: 4px; border-radius: 8px; border: 1px solid var(--border);">
                <input type="text" id="color_text" class="form-control" value="{{ old('color', '#0ea5e9') }}" placeholder="#000000" maxlength="7" style="flex-grow: 1;">
            </div>
            <small style="color: var(--text-muted); display: block; margin-top: 8px;">Warna ini akan digunakan sebagai latar belakang label pada kartu iklan.</small>
            @error('color')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Simpan Tipe</button>
            <a href="{{ route('admin.listing_types') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
        </div>
    </form>
</div>

<script>
    const colorInput = document.getElementById('color');
    const colorText = document.getElementById('color_text');
    
    colorInput.addEventListener('input', (e) => {
        colorText.value = e.target.value.toUpperCase();
    });
    
    colorText.addEventListener('input', (e) => {
        if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
            colorInput.value = e.target.value;
        }
    });
</script>
@endsection
