@extends('admin.layout')

@section('admin_content')
<div class="dashboard-header">
    <div>
        <h1>Kelola Kategori</h1>
        <p style="color: var(--text-muted);">Daftar kategori listing di BatamCraig.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">+ Kategori Baru</a>
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
                <th>Ikon</th>
                <th>Nama</th>
                <th>Slug</th>
                <th>Urutan</th>
                <th>Total Listing</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
            <tr>
                <td><i class="fa-solid fa-{{ $category->icon }}" style="font-size: 1.2rem; color: var(--primary);"></i></td>
                <td style="font-weight: 600;">{{ $category->name }}</td>
                <td>{{ $category->slug }}</td>
                <td>{{ $category->sort_order }}</td>
                <td>{{ $category->listings_count }}</td>
                <td>
                    <div style="display: flex; gap: 15px;">
                        <a href="{{ route('admin.categories.edit', $category->id) }}" style="color: var(--primary);"><i class="fa-solid fa-pen"></i></a>
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;">
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
