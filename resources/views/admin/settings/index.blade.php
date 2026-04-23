@extends('admin.layout')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700;">Parameter Aplikasi</h1>
        <p style="color: var(--text-muted);">Kelola pengaturan sistem Sebatam.</p>
    </div>
    <a href="{{ route('admin.settings.create') }}" class="btn btn-primary">+ Tambah Parameter</a>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div class="glass" style="padding: 30px; border-radius: var(--radius);">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama Parameter</th>
                <th>Nilai</th>
                <th>Keterangan</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($settings as $setting)
            <tr>
                <td style="font-weight: 600; font-family: monospace; color: var(--primary);">{{ $setting->key }}</td>
                <td>
                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $setting->value }}">
                        {{ $setting->value }}
                    </div>
                </td>
                <td style="color: var(--text-muted); font-size: 0.9rem;">{{ $setting->description }}</td>
                <td>
                    <div style="display: flex; gap: 15px; align-items: center; justify-content: flex-end;">
                        <a href="{{ route('admin.settings.edit', $setting->id) }}" style="color: var(--accent);" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                        <form action="{{ route('admin.settings.destroy', $setting->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus parameter ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;" title="Hapus">
                                <i class="fa-solid fa-trash"></i>
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
