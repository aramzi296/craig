@extends('layouts.app')

@section('content')
<section class="hero" style="background-image: url('{{ asset('gelombang.png') }}');">
    <div class="container">
        <div style="display: flex; gap: 15px; justify-content: center; align-items: center; flex-wrap: wrap;">
            <form action="{{ route('search') }}" method="GET" class="search-box" style="margin: 0; width: 100%; max-width: 600px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" placeholder="Cari apa saja di Batam..." value="{{ request('q') }}">
                
                <!-- Hidden inputs to preserve filters when searching via keyword -->
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
                @if(request('location')) <input type="hidden" name="location" value="{{ request('location') }}"> @endif
                
                <button type="submit" class="btn btn-primary" style="margin-right: -2px; border-radius: 50px; padding: 12px 30px;">Cari</button>
            </form>
            <a href="{{ route('listings.create') }}" class="btn btn-primary" style="border-radius: 50px; padding: 15px 30px; height: 58px; background: var(--secondary); border: none; box-shadow: 0 4px 14px 0 rgba(249, 115, 22, 0.39);">
                <i class="fa-solid fa-plus"></i> Pasang Iklan Anda
            </a>
        </div>
    </div>
</section>

<div class="container page-section" style="padding-top: 0;">
    <div class="search-layout">
        
        <!-- Sidebar Filters -->
        <aside class="search-sidebar">
            <div style="position: sticky; top: 100px; background: white; padding: 25px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                    <h3 style="font-size: 1.2rem; font-weight: 800; margin: 0; color: var(--text);">Filter</h3>
                    <a href="{{ route('search') }}" style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">Reset</a>
                </div>

                <form action="{{ route('search') }}" method="GET" id="filter-form">
                    @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

                    <!-- Type Filter -->
                    <div class="filter-group" style="margin-bottom: 30px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 12px; font-size: 0.95rem;">Tipe Iklan</label>
                        <select name="type" class="form-control" onchange="this.form.submit()" style="background: white; border-radius: 8px;">
                            <option value="">Semua Tipe</option>
                            @foreach($listingTypes as $type)
                                <option value="{{ $type->slug }}" {{ request('type') == $type->slug ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-group" style="margin-bottom: 30px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 12px; font-size: 0.95rem;">Kategori</label>
                        <select name="category" class="form-control" onchange="this.form.submit()" style="background: white; border-radius: 8px;">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <!-- Location Filter -->
                    <div class="filter-group" style="margin-bottom: 30px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 12px; font-size: 0.95rem;">Lokasi</label>
                        <select name="location" class="form-control" onchange="this.form.submit()" style="background: white; border-radius: 8px;">
                            <option value="">Semua Lokasi</option>
                            @foreach($districts as $dist)
                                <option value="{{ $dist->id }}" {{ request('location') == $dist->id ? 'selected' : '' }}>{{ $dist->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Results Area -->
        <main class="search-results">
            <button id="mobile-filter-toggle" onclick="toggleMobileFilter()">
                <i class="fa-solid fa-filter"></i> Tampilkan Filter
            </button>
            <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <h2 class="section-title" style="margin: 0; font-size: 1.6rem;">
                    @if(request('q')) 
                        Hasil untuk "{{ request('q') }}"
                    @else
                        Semua Iklan
                    @endif
                    <span style="font-weight: 500; color: var(--text-muted); font-size: 1rem; margin-left: 10px;">({{ $listings->total() }} iklan)</span>
                </h2>
                

            </div>

            @if($listings->count() > 0)
                <div class="listing-grid">
                    @foreach($listings as $listing)
                    <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card">
                        @if($listing->getThumbnailUrl())
                            <img src="{{ $listing->getThumbnailUrl() }}" alt="{{ $listing->title }}" class="listing-image">
                        @endif
                        <div class="listing-details">
                            <h3 class="listing-title">
                                {{ $listing->title }}
                                @if($listing->is_premium)
                                    <span class="badge badge-premium" style="vertical-align: middle; font-size: 0.6rem;">PREMIUM</span>
                                @endif
                            </h3>
                            
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; margin: 5px 0 10px 0;">
                                {{ \Illuminate\Support\Str::limit($listing->description, 150) }}
                            </p>

                            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; margin-top: auto;">
                                <div class="listing-price" style="font-size: 1.2rem; margin: 0; color: var(--primary);">
                                    @if($listing->price && $listing->price > 0)
                                        Rp {{ number_format($listing->price, 0, ',', '.') }}
                                    @else
                                        Hubungi Kami
                                    @endif
                                </div>
                                <div class="listing-location" style="margin: 0; font-size: 0.85rem;"><i class="fa-solid fa-location-dot"></i> {{ $listing->district?->name ?? 'Batam' }}</div>
                                <div class="listing-category" style="margin: 0; font-size: 0.7rem; display: flex; align-items: center; gap: 5px;">
                                    {{ $listing->categories->take(1)->pluck('name')->join('') }}
                                    @if($listing->listingType)
                                        <span style="background: {{ $listing->listingType->color }}; color: white; padding: 1px 6px; border-radius: 4px; font-size: 0.6rem;">
                                            {{ $listing->listingType->name }}
                                        </span>
                                    @endif
                                    <span style="font-size: 0.65rem; color: var(--text-muted);"><i class="fa-solid fa-clock"></i> Update: {{ $listing->updated_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="listing-right-panel" style="min-width: 140px; justify-content: center;">
                            <div class="btn-whatsapp-sm">
                                <i class="fa-brands fa-whatsapp"></i> WhatsApp
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>

                <div style="margin-top: 40px; display: flex; justify-content: center;">
                    {{ $listings->appends(request()->query())->links('vendor.pagination.simple-custom') }}
                </div>
            @else
                <div style="text-align: center; padding: 100px 20px; background: white; border-radius: var(--radius); border: 1px dashed var(--border);">
                    <div style="font-size: 3rem; color: var(--border); margin-bottom: 20px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h2 style="font-weight: 700;">Tidak ada iklan ditemukan</h2>
                    <p style="color: var(--text-muted);">Coba cari dengan kata kunci lain atau ubah filter Anda.</p>
                    <a href="{{ route('search') }}" class="btn btn-primary" style="margin-top: 20px;">Lihat Semua Iklan</a>
                </div>
            @endif
        </main>
    </div>
</div>

<style>
    .search-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 40px;
    }

    #mobile-filter-toggle {
        display: none;
        width: 100%;
        margin-bottom: 20px;
        background: white;
        color: var(--text);
        border: 1px solid var(--border);
        padding: 12px;
        border-radius: 12px;
        font-weight: 700;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
    }

    @media (max-width: 992px) {
        .search-layout {
            grid-template-columns: 1fr !important;
            gap: 0 !important;
        }
        
        #mobile-filter-toggle {
            display: flex;
        }

        .search-sidebar {
            display: none !important;
            margin-bottom: 30px;
        }

        .search-sidebar.active {
            display: block !important;
        }

        .search-sidebar div[style*="position: sticky"] {
            position: static !important;
            box-shadow: none !important;
            padding: 20px !important;
        }
        
        .listing-card {
            flex-direction: column !important;
            height: auto !important;
            gap: 15px !important;
        }

        .listing-image {
            width: 100% !important;
            height: 200px !important;
        }

        .listing-right-panel {
            flex-direction: row !important;
            align-items: center !important;
            width: 100% !important;
            padding-left: 0 !important;
            border-left: none !important;
            border-top: 1px solid var(--border) !important;
            padding-top: 15px !important;
            justify-content: space-between !important;
        }
    }
</style>

<script>
    function toggleMobileFilter() {
        const sidebar = document.querySelector('.search-sidebar');
        const btn = document.getElementById('mobile-filter-toggle');
        const isShowing = sidebar.classList.toggle('active');
        
        btn.innerHTML = isShowing 
            ? '<i class="fa-solid fa-xmark"></i> Tutup Filter' 
            : '<i class="fa-solid fa-filter"></i> Tampilkan Filter';
    }
</script>
@endsection
