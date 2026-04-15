@extends('layouts.dashboard')

@section('dashboard_content')
    @if(session('success'))
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="dashboard-header">
        <h1>Halo, {{ auth()->user()->name }}!</h1>
        <a href="{{ route('listings.create') }}" class="btn btn-primary">+ Buat Iklan Baru</a>
    </div>

    @if(!$tab)
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Iklan</div>
            <div class="stat-value">{{ $totalListings }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Iklan Aktif</div>
            <div class="stat-value">{{ $activeListings }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Iklan Premium</div>
            <div class="stat-value">{{ $featuredListings }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Dilihat</div>
            <div class="stat-value">124</div>
        </div>
    </div>
    @endif

    <div class="glass" style="padding: 30px; border-radius: var(--radius); margin-top: 40px;">
        <h2 style="font-size: 1.2rem; margin-bottom: 20px;">{{ $tableTitle }}</h2>
        @if($listings->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Iklan</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listings as $listing)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="{{ $listing->getThumbnailUrl() }}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;" alt="">
                            <div>
                                <div style="font-weight: 600;"><a href="{{ route('listings.show', $listing->slug) }}">{{ $listing->title }}</a></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $listing->location }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $listing->categories->pluck('name')->join(', ') }}</td>
                    <td>Rp {{ number_format($listing->price, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $listing->is_active ? 'badge-success' : 'badge-pending' }}">
                            {{ $listing->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            @if($tab == 'favorites')
                                <form action="{{ route('listings.favorite', $listing->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;" title="Hapus dari Favorit">
                                        <i class="fa-solid fa-heart-circle-xmark"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('listings.toggle', $listing->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; color: var(--primary); cursor: pointer; padding: 0;" title="Ganti Status">
                                        <i class="fa-solid fa-power-off"></i>
                                    </button>
                                </form>
                                <a href="{{ route('listings.edit', $listing->id) }}" style="color: var(--accent);"><i class="fa-solid fa-pen"></i></a>
                                <form action="{{ route('listings.destroy', $listing->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus iklan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i class="fa-solid fa-box-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
            <p>Anda belum memiliki iklan. Mulai berjualan sekarang!</p>
            <a href="{{ route('listings.create') }}" class="btn btn-outline" style="margin-top: 20px;">Pasang Iklan Pertama</a>
        </div>
        @endif
    </div>
@endsection
