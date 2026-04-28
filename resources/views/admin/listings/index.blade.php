@extends('admin.layout')

@section('admin_content')
<div class="dashboard-header">
    <div>
        <h1>Kelola Listing</h1>
        <p style="color: var(--text-muted);">Kelola semua iklan yang terpasang di {{ config('app.name') }}.</p>
    </div>
    <a href="{{ route('admin.listings.create') }}" class="btn btn-primary">+ Listing Baru</a>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div class="glass" style="padding: 20px; border-radius: var(--radius); margin-bottom: 30px;">
    <form action="{{ route('admin.listings') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Cari Iklan</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul, deskripsi, atau lokasi..." class="form-control" style="padding: 10px 15px;">
        </div>
        
        <div style="width: 200px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Tipe Listing</label>
            <select name="listing_type_id" class="form-control" style="padding: 10px 15px;">
                <option value="">Semua Tipe</option>
                @foreach($listingTypes as $type)
                    <option value="{{ $type->id }}" {{ request('listing_type_id') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="width: 150px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Status</label>
            <select name="status" class="form-control" style="padding: 10px 15px;">
                <option value="">Semua Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.listings') }}" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center;">Reset</a>
        </div>
    </form>
</div>

<div class="glass" style="padding: 30px; border-radius: var(--radius);">
    <table class="data-table">
        <thead>
            <tr>
                <th>Iklan</th>
                <th>Pemilik</th>
                <th>Tipe / Kategori</th>
                <th>Harga</th>
                <th style="text-align:center;">Rank</th>
                <th style="text-align:center;">Expire</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listings as $listing)
            <tr>
                <td>
                    <div style="font-weight: 600;">{{ $listing->title }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fa-solid fa-location-dot"></i> {{ $listing->district?->name ?? 'Batam' }}</div>
                </td>
                <td>{{ $listing->user->name }}</td>
                <td>
                    <div style="font-weight: 600;">{{ $listing->listingType->name ?? '-' }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                        {{ $listing->categories->pluck('name')->join(', ') }}
                    </div>
                </td>
                <td>
                    @if($listing->price && $listing->price > 0)
                        Rp {{ number_format($listing->price, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td style="text-align:center;">
                    @php
                        $rank = $listing->listing_rank ?? 0;
                        $rankColor = $rank <= 100 ? '#f59e0b' : ($rank <= 500 ? '#0ea5e9' : '#94a3b8');
                    @endphp
                    <span style="display:inline-block; background: {{ $rankColor }}22; color: {{ $rankColor }}; border: 1px solid {{ $rankColor }}44; border-radius: 6px; padding: 3px 10px; font-weight: 700; font-size: 0.82rem;">
                        #{{ number_format($rank, 0, ',', '.') }}
                    </span>
                </td>
                <td style="text-align:center; font-size: 0.82rem;">
                    @if($listing->expires_at)
                        @if($listing->isExpired())
                            <span style="color:#ef4444; font-weight:600;" title="{{ $listing->expires_at->format('d/m/Y') }}">
                                <i class="fa-solid fa-circle-xmark" style="margin-right:3px;"></i>
                                {{ $listing->expires_at->format('d M Y') }}
                            </span>
                        @else
                            <span style="color:#22c55e; font-weight:600;" title="Berakhir: {{ $listing->expires_at->format('d/m/Y') }}">
                                <i class="fa-solid fa-clock" style="margin-right:3px;"></i>
                                {{ $listing->expires_at->format('d M Y') }}
                            </span>
                        @endif
                    @else
                        <span style="color:var(--text-muted);">—</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $listing->is_active ? 'badge-success' : 'badge-pending' }}">
                        {{ $listing->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <form action="{{ route('admin.listings.toggle', $listing->id) }}" method="POST">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: var(--primary); cursor: pointer; padding: 0;" title="Toggle Status">
                                <i class="fa-solid fa-power-off"></i>
                            </button>
                        </form>
                        <a href="{{ route('admin.listings.edit', $listing->id) }}" style="color: var(--accent);" title="Edit"><i class="fa-solid fa-pen"></i></a>
                        <form action="{{ route('admin.listings.destroy', $listing->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus listing ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;" title="Hapus">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fa-solid fa-magnifying-glass" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    Tidak ada iklan yang ditemukan dengan kriteria tersebut.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: 20px;">
        {{ $listings->links() }}
    </div>
</div>
@endsection
