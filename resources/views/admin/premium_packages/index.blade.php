@extends('admin.layout')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700;">Paket Premium</h1>
        <p style="color: var(--text-muted);">Definisikan harga dan durasi paket untuk iklan berbayar.</p>
    </div>
    <a href="{{ route('admin.premium_packages.create') }}" class="btn btn-primary" style="padding: 12px 25px;">
        <i class="fa-solid fa-plus"></i> Tambah Paket
    </a>
</div>

<div class="glass" style="padding: 0; overflow: hidden; border-radius: var(--radius);">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama Paket</th>
                <th>Harga</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($packages as $package)
            <tr>
                <td style="font-weight: 600;">{{ $package->name }}</td>
                <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                <td>{{ $package->duration_days }} Hari</td>
                <td>
                    <span class="badge {{ $package->is_active ? 'badge-success' : 'badge-pending' }}">
                        {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 10px;">
                        <a href="{{ route('admin.premium_packages.edit', $package->id) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
                            Edit
                        </a>
                        <form action="{{ route('admin.premium_packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Hapus paket ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem; color: #ef4444; border-color: #fecaca;">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
