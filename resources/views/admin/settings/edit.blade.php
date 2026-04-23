@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <a href="{{ route('admin.settings') }}" style="color: var(--text-muted); text-decoration: none; display: flex; align-items: center; gap: 5px; margin-bottom: 15px;">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
    </a>
    <h1 style="font-size: 2rem; font-weight: 700;">Edit Parameter</h1>
    <p style="color: var(--text-muted);">Ubah nilai parameter <strong>{{ $setting->key }}</strong>.</p>
</div>

<div class="glass" style="padding: 40px; border-radius: var(--radius); max-width: 800px;">
    <form action="{{ route('admin.settings.update', $setting->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nama Parameter (Key)</label>
            <input type="text" name="key" value="{{ old('key', $setting->key) }}" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; outline: none; font-family: monospace; background: #f8fafc;" readonly>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">Nama parameter tidak dapat diubah untuk menjaga integritas sistem.</p>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Nilai (Value)</label>
            <textarea name="value" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; outline: none; min-height: 100px; font-family: inherit;">{{ old('value', $setting->value) }}</textarea>
            @error('value') <p style="color: #ef4444; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</p> @enderror
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600;">Keterangan</label>
            <input type="text" name="description" value="{{ old('description', $setting->description) }}" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; outline: none; font-family: inherit;">
            @error('description') <p style="color: #ef4444; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-weight: 600;">Simpan Perubahan</button>
    </form>
</div>
@endsection
