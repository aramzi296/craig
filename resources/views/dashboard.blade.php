@extends('layouts.dashboard')

@section('dashboard_content')
    @if(session('success'))
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="dashboard-header">
        <h1>
            Halo, {{ auth()->user()->name }}!
            @if(auth()->user()->is_verified)
                <i class="fa-solid fa-circle-check verified-badge" title="Akun Terverifikasi"></i>
            @endif
        </h1>
        <a href="{{ route('listings.create') }}" class="btn btn-primary">+ Buat Iklan Baru</a>
    </div>

    @if(!$tab)
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Iklan</div>
            <div class="stat-value">{{ $totalListings }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Sisa Kuota Iklan</div>
            <div class="stat-value" style="color: var(--primary);">{{ auth()->user()->ads_quota }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Iklan Aktif</div>
            <div class="stat-value">{{ $activeListings }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Dilihat</div>
            <div class="stat-value">{{ number_format($totalViews ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>
    @endif

    @if(isset($unusedPremiumRequests) && $unusedPremiumRequests->count() > 0)
    <div class="glass" style="padding: 25px; border-radius: var(--radius); margin-top: 30px; border-left: 4px solid #f59e0b;">
        <h2 style="font-size: 1.1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-crown" style="color: #f59e0b;"></i>
            Paket Premium Tersedia
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
            @foreach($unusedPremiumRequests as $req)
                <div style="background: rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                    <div style="font-weight: 700; color: #f59e0b;">{{ $req->package->name }}</div>
                    <div style="font-size: 0.8rem; margin-top: 5px;">
                        Status: 
                        @if($req->status === 'active')
                            <span style="color: #22c55e; font-weight: 600;">Siap Digunakan</span>
                        @else
                            <span style="color: #f59e0b; font-weight: 600;">Menunggu Verifikasi Admin</span>
                        @endif
                    </div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 5px;">ID: PREM-{{ $req->id }}</div>
                    
                    @if($req->status === 'active')
                        <a href="{{ route('listings.create', ['premium_request_id' => $req->id]) }}" class="btn btn-primary btn-sm" style="margin-top: 10px; width: 100%; text-align: center; font-size: 0.8rem; padding: 8px;">Gunakan Sekarang</a>
                    @endif
                </div>
            @endforeach
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 15px;">
            * Paket di atas dapat Anda gunakan saat membuat iklan baru. 
            @if($unusedPremiumRequests->where('status', 'pending')->count() > 0)
                Mohon tunggu verifikasi admin untuk paket yang masih tertunda.
            @endif
        </p>
    </div>
    @endif

    <div class="glass" style="padding: 30px; border-radius: var(--radius); margin-top: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.2rem; margin: 0;">{{ $tableTitle }}</h2>
            @if(!$tab)
                <a href="{{ route('dashboard', ['tab' => 'my-listings']) }}" style="font-size: 0.9rem; color: var(--primary); font-weight: 600;">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
            @endif
        </div>
        @if($listings->count() > 0)
        <div style="overflow-x: auto; margin: 0 -15px; padding: 0 15px;">
            <table class="data-table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Iklan</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Komentar</th>
                        <th>Dilihat</th>
                        <th>Status</th>
                        <th>Kadaluarsa</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($listings as $listing)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                @if($listing->getThumbnailUrl())
                                    <img src="{{ $listing->getThumbnailUrl() }}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;" alt="">
                                @endif
                                <div>
                                    <div style="font-weight: 600;">
                                        <a href="{{ route('listings.show', $listing->slug) }}">{{ $listing->title }}</a>
                                        @if($listing->is_premium)
                                            <span class="badge badge-premium" style="font-size: 0.65rem; margin-left: 5px;">PREMIUM</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $listing->district->name ?? 'Batam' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $listing->categories->take(3)->pluck('name')->join(', ') }}</td>

                        <td>
                            @if($listing->price && $listing->price > 0)
                                Rp {{ number_format($listing->price, 0, ',', '.') }}
                            @else
                                <span style="color: var(--text-muted); font-style: italic;">Hubungi Kami</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('listings.show', $listing->slug) }}#comments-section" style="display: flex; align-items: center; gap: 6px; color: {{ $listing->comments_count > 0 ? 'var(--primary)' : 'var(--text-muted)' }}; font-weight: {{ $listing->comments_count > 0 ? '700' : '400' }};">
                                <i class="fa-{{ $listing->comments_count > 0 ? 'solid' : 'regular' }} fa-comment"></i>
                                <span>{{ $listing->comments_count }}</span>
                            </a>
                        </td>
                        <td>{{ number_format($listing->views_count, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $listing->is_active ? 'badge-success' : 'badge-pending' }}">
                                {{ $listing->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            @if($listing->expires_at)
                                <div style="font-weight: 600;">{{ $listing->expires_at->format('d/m/Y') }}</div>
                                <div style="font-size: 0.7rem; color: {{ $listing->expires_at->isPast() ? '#ef4444' : 'var(--text-muted)' }};">
                                    {{ $listing->expires_at->diffForHumans() }}
                                </div>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 15px; align-items: center; justify-content: flex-end;">
                                @if($tab == 'favorites')
                                    <form action="{{ route('listings.favorite', $listing->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;" title="Hapus dari Favorit">
                                            <i class="fa-solid fa-heart-circle-xmark"></i>
                                        </button>
                                    </form>
                                @else
                                    @if(!$listing->is_premium && !$listing->hasPendingPremiumRequest())
                                        <a href="{{ route('dashboard.premium.upgrade', $listing->id) }}" style="color: #ea580c;" title="Upgrade ke Premium">
                                            <i class="fa-solid fa-crown"></i>
                                        </a>
                                    @endif

                                    @if($listing->hasPendingPremiumRequest())
                                        <span class="badge badge-pending" style="font-size: 0.65rem; margin-right: 10px;">PROSES PREMIUM...</span>
                                    @endif

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
        </div>

        <div style="margin-top: 25px;">
            {{ $listings->links('vendor.pagination.simple-custom') }}
        </div>

        @else
        <div style="text-align: center; padding: 40px; color: var(--text-muted);">
            <i class="fa-solid fa-box-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
            <p>Anda belum memiliki iklan. Mulai berjualan sekarang!</p>
            <a href="{{ route('listings.create') }}" class="btn btn-outline" style="margin-top: 20px;">Pasang Iklan Pertama</a>
        </div>
        @endif
    </div>
@endsection
