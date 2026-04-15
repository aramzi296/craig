@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Kelola Pengguna</h1>
    <p style="color: var(--text-muted);">Daftar semua pengguna yang terdaftar di BatamCraig.</p>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('error') }}
    </div>
@endif

<div class="glass" style="padding: 30px; border-radius: var(--radius);">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Peran</th>
                <th>Bergabung</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td style="font-weight: 600;">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <span class="badge {{ $user->is_admin ? 'badge-success' : '' }}" style="background: {{ $user->is_admin ? '#dbeafe' : '#f1f5f9' }}; color: {{ $user->is_admin ? '#1e40af' : '#475569' }};">
                        {{ $user->is_admin ? 'Administrator' : 'Pengguna Umum' }}
                    </span>
                </td>
                <td>{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <form action="{{ route('admin.users.toggle-admin', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: var(--primary); cursor: pointer; padding: 0;" title="Ganti Peran">
                                <i class="fa-solid fa-user-shield"></i>
                            </button>
                        </form>
                        <a href="{{ route('admin.users.edit', $user->id) }}" style="color: var(--accent);" title="Edit Profil"><i class="fa-solid fa-user-pen"></i></a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini? Semua iklan milik pengguna ini juga akan terhapus.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;" title="Hapus Akun">
                                <i class="fa-solid fa-user-xmark"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top: 20px;">
        {{ $users->links() }}
    </div>
</div>
@endsection
