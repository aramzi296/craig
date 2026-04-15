@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Edit Profil Pengguna</h1>
    <p style="color: var(--text-muted);">Ubah informasi dasar untuk pengguna: {{ $user->name }}</p>
</div>

<div class="glass" style="max-width: 600px; padding: 40px; border-radius: var(--radius);">
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div style="padding: 15px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; margin-top: 20px; color: #92400e; font-size: 0.85rem;">
            <i class="fa-solid fa-circle-info"></i> Password hanya dapat diubah oleh pengguna sendiri melalui fitur reset password atau profil pribadi mereka.
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Simpan Perubahan</button>
            <a href="{{ route('admin.users') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
        </div>
    </form>
</div>
@endsection
