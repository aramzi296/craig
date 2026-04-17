@extends('admin.layout')

@section('admin_content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700;">Tipe Listing</h1>
        <p style="color: var(--text-muted);">Manajemen label tipe listing (Dijual, Disewakan, Jasa, dll).</p>
    </div>
    <a href="{{ route('admin.listing_types.create') }}" class="btn btn-primary" style="padding: 12px 25px;">
        <i class="fa-solid fa-plus"></i> Tambah Tipe
    </a>
</div>

<div class="glass" style="padding: 0; overflow: hidden; border-radius: var(--radius);">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama Tipe</th>
                <th>Urutan</th>
                <th>Slug</th>
                <th>Warna Label</th>

                <th>Preview</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($listingTypes as $type)
            <tr>
                <td style="font-weight: 600;">{{ $type->name }}</td>
                <td><span style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px;">{{ $type->sort_order }}</span></td>
                <td><code>{{ $type->slug }}</code></td>
                <td><code>{{ $type->color }}</code></td>

                <td>
                    <span style="background: {{ $type->color }}; color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                        {{ $type->name }}
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 10px;">
                        <a href="{{ route('admin.listing_types.edit', $type->id) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">
                            Edit
                        </a>
                        <form action="{{ route('admin.listing_types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Hapus tipe ini? Semua listing dengan tipe ini akan kehilangan label tipenya.')">
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
