@extends('layouts.dashboard')

@section('dashboard_content')

    <div class="dashboard-header">
        <h1>
            Halo, {{ auth()->user()->name }}!
            @if(auth()->user()->is_verified)
                <i class="fa-solid fa-circle-check verified-badge" title="Akun Terverifikasi"></i>
            @endif
        </h1>
        <div style="display: flex; gap: 10px;">
            {{-- Beli Paket Premium dinonaktifkan sementara --}}
            <a href="{{ route('listings.create') }}" class="btn btn-primary">+ Buat Iklan Baru</a>
        </div>
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

    {{-- Panel Paket Premium dinonaktifkan sementara --}}

    <div class="glass" style="padding: 30px; border-radius: var(--radius); margin-top: 40px; min-height: 450px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.2rem; margin: 0;">{{ $tableTitle }}</h2>
            @if(!$tab)
                <a href="{{ route('dashboard', ['tab' => 'my-listings']) }}" style="font-size: 0.9rem; color: var(--primary); font-weight: 600;">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
            @endif
        </div>
        @if($listings->count() > 0)
        <div class="table-container" style="overflow-x: auto; margin: 0 -15px; padding: 0 15px; min-height: 300px;">
            <table class="data-table" style="min-width: 600px; margin-bottom: 100px;">
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
                        <td>{{ $listing->categories->first()->name ?? '-' }}</td>

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
                        <td style="text-align: right;">
                            <div class="dropdown" style="display: inline-block; position: relative;">
                                <button onclick="toggleDropdown(event, 'dropdown-{{ $listing->id }}')" class="btn btn-secondary action-btn" style="padding: 6px 12px; font-size: 0.8rem; border-radius: 8px; background: white; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
                                    Aksi <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem;"></i>
                                </button>
                                <div id="dropdown-{{ $listing->id }}" class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 160px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid #e2e8f0; z-index: 1000; margin-top: 5px; padding: 8px 0; text-align: left;">
                                    
                                    <a href="{{ route('listings.show', $listing->slug) }}" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; text-decoration: none; font-size: 0.9rem;">
                                        <i class="fa-solid fa-eye" style="width: 16px; color: #64748b;"></i> Lihat Iklan
                                    </a>

                                    @if($tab != 'favorites')
                                        <a href="{{ route('listings.edit', $listing->id) }}" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; text-decoration: none; font-size: 0.9rem;">
                                            <i class="fa-solid fa-pen-to-square" style="width: 16px; color: #0ea5e9;"></i> Edit Iklan
                                        </a>

                                        <form action="{{ route('listings.toggle', $listing->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; cursor: pointer; font-size: 0.9rem; font-family: inherit;">
                                                <i class="fa-solid fa-power-off" style="width: 16px; color: {{ $listing->is_active ? '#ef4444' : '#22c55e' }};"></i>
                                                {{ $listing->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <div style="height: 1px; background: #f1f5f9; margin: 5px 0;"></div>

                                        <form action="{{ route('listings.destroy', $listing->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus iklan ini?')" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #ef4444; cursor: pointer; font-size: 0.9rem; font-family: inherit;">
                                                <i class="fa-solid fa-trash" style="width: 16px;"></i> Hapus Iklan
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('listings.favorite', $listing->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #ef4444; cursor: pointer; font-size: 0.9rem; font-family: inherit;">
                                                <i class="fa-solid fa-heart-circle-xmark" style="width: 16px;"></i> Hapus Favorit
                                            </button>
                                        </form>
                                    @endif
                                </div>
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

    @section('scripts')
    <script>
        function toggleDropdown(event, id) {
            event.stopPropagation();
            
            const menu = document.getElementById(id);
            const isOpen = menu.style.display === 'block';

            // Close other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(m => {
                if (m.id !== id) m.style.display = 'none';
            });

            if (!isOpen) {
                menu.style.display = 'block';
                
                // Ensure layout is updated before measuring
                requestAnimationFrame(() => {
                    const rect = menu.getBoundingClientRect();
                    const container = menu.closest('.table-container');
                    const containerRect = container ? container.getBoundingClientRect() : null;
                    const windowHeight = window.innerHeight;
                    
                    // Boundary detection
                    // Check if it overflows the container bottom or window bottom
                    const overflowsContainer = containerRect && (rect.bottom > containerRect.bottom - 10);
                    const overflowsWindow = rect.bottom > windowHeight - 10;

                    if (overflowsContainer || overflowsWindow) {
                        // Try flipping to top
                        menu.style.top = 'auto';
                        menu.style.bottom = '100%';
                        menu.style.marginTop = '0';
                        menu.style.marginBottom = '10px';
                        
                        // Check if it now overflows the top of the container or window
                        const flippedRect = menu.getBoundingClientRect();
                        const overflowsTop = flippedRect.top < (containerRect ? containerRect.top : 0) || flippedRect.top < 0;
                        
                        if (overflowsTop) {
                            // If it still doesn't fit at the top, show it at the bottom but 
                            // we rely on the container's min-height and margin-bottom to provide space
                            menu.style.bottom = 'auto';
                            menu.style.top = '100%';
                            menu.style.marginTop = '10px';
                            menu.style.marginBottom = '0';
                        }
                    } else {
                        menu.style.top = '100%';
                        menu.style.bottom = 'auto';
                        menu.style.marginTop = '10px';
                        menu.style.marginBottom = '0';
                    }
                });
            } else {
                menu.style.display = 'none';
            }
        }

        // Close dropdowns when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.action-btn') && !event.target.closest('.action-btn')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        }
    </script>
    <style>
        .dropdown-item:hover {
            background-color: #f8fafc !important;
            color: var(--primary) !important;
        }
        .dropdown-menu {
            transition: opacity 0.2s, transform 0.2s;
        }
    </style>
    @endsection
@endsection
