@extends('layouts.dashboard')

@section('dashboard_content')
    <div class="dashboard-header">
        <h1>Profil Saya</h1>
    </div>


    <div class="glass" style="padding: 30px; border-radius: var(--radius); max-width: 600px;">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 25px; text-align: center;">
                <div style="position: relative; display: inline-block;">
                    <img src="{{ $user->getProfilePhoto() }}" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 3px solid var(--primary);" alt="">
                    <label for="photo" style="position: absolute; bottom: 15px; right: 0; background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: var(--shadow);">
                        <i class="fa-solid fa-camera"></i>
                    </label>
                </div>
                <input type="file" name="photo" id="photo" style="display: none;" onchange="previewImage(this)">
                <p style="font-size: 0.85rem; color: var(--text-muted);">Klik ikon kamera untuk mengubah foto profil.</p>
                @error('photo')
                    <div style="color: #ef4444; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="name" style="display: block; font-weight: 700; margin-bottom: 8px;">Nama Lengkap</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div style="color: #ef4444; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; font-weight: 700; margin-bottom: 8px;">Email</label>
                <input type="email" id="email" class="form-control" value="{{ $user->email }}" disabled style="background: #f1f5f9; cursor: not-allowed;">
                <small class="text-muted">Email tidak dapat diubah untuk saat ini.</small>
            </div>

            <div style="margin-bottom: 30px;">
                <label for="whatsapp" style="display: block; font-weight: 700; margin-bottom: 8px;">Nomor WhatsApp</label>
                <input type="text" name="whatsapp" id="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp', $user->whatsapp) }}" required>
                <small class="text-muted">Gunakan format 08xxx atau 628xxx.</small>
                @error('whatsapp')
                    <div style="color: #ef4444; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px;">Simpan Perubahan</button>
        </form>
    </div>

    {{-- Ganti Password Section --}}
    <div class="dashboard-header" style="margin-top: 40px;">
        <h2>Keamanan Akun</h2>
    </div>

    @if(session('success_password'))
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 25px; max-width: 600px;">
            {{ session('success_password') }}
        </div>
    @endif

    <div class="glass" style="padding: 30px; border-radius: var(--radius); max-width: 600px; margin-bottom: 40px;">
        <form action="{{ route('profile.password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 25px;">
                Gunakan fitur ini untuk membuat atau mengganti password Anda. Password ini digunakan jika Anda ingin masuk menggunakan alamat email.
            </p>

            <div style="margin-bottom: 20px;">
                <label for="password" style="display: block; font-weight: 700; margin-bottom: 8px;">Password Baru</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Minimal 8 karakter">
                @error('password')
                    <div style="color: #ef4444; font-size: 0.8rem; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 30px;">
                <label for="password_confirmation" style="display: block; font-weight: 700; margin-bottom: 8px;">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Ulangi password baru">
            </div>

            <button type="submit" class="btn btn-outline" style="width: 100%; padding: 15px; border-color: var(--primary); color: var(--primary);">Perbarui Password</button>
        </form>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    input.parentElement.querySelector('img').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
