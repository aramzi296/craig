@extends('layouts.app')

@section('content')
<div class="search-header" style="background: white; border-bottom: 1px solid var(--border); padding: 30px 0;">
    <div class="container">
        <form action="{{ route('search') }}" method="GET" class="search-box" style="box-shadow: none; border: 1px solid var(--border); max-width: 800px; margin: 0 auto;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" placeholder="Cari apa saja di Batam..." value="{{ request('q') }}">
            
            <!-- Hidden inputs to preserve filters when searching via keyword -->
            @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
            @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
            @if(request('location')) <input type="hidden" name="location" value="{{ request('location') }}"> @endif
            
            <button type="submit" class="btn btn-primary" style="margin-right: -2px; border-radius: 50px; padding: 12px 30px;">Cari</button>
        </form>
    </div>
</div>

<div class="container page-section" style="padding-top: 40px;">
    <div class="search-layout" style="display: grid; grid-template-columns: 280px 1fr; gap: 40px;">
        
        <!-- Sidebar Filters -->
        <aside class="search-sidebar">
            <div style="position: sticky; top: 100px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="font-size: 1.2rem; font-weight: 700;">Filter</h3>
                    <a href="{{ route('search', ['q' => request('q')]) }}" style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">Reset</a>
                </div>

                <form action="{{ route('search') }}" method="GET" id="filter-form">
                    @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

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

                    <!-- Type Filter -->
                    <div class="filter-group" style="margin-bottom: 30px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 12px; font-size: 0.95rem;">Tipe Iklan</label>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($listingTypes as $type)
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                                    <input type="radio" name="type" value="{{ $type->slug }}" onchange="this.form.submit()" {{ (request('type') == $type->id || request('type') == $type->slug) ? 'checked' : '' }}>
                                    {{ $type->name }}
                                </label>
                            @endforeach
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="type" value="" onchange="this.form.submit()" {{ !request('type') ? 'checked' : '' }}>
                                Semua Tipe
                            </label>
                        </div>
                    </div>

                    <!-- Location Filter -->
                    <div class="filter-group" style="margin-bottom: 30px;">
                        <label style="display: block; font-weight: 700; margin-bottom: 12px; font-size: 0.95rem;">Lokasi</label>
                        <select name="location" class="form-control" onchange="this.form.submit()" style="background: white; border-radius: 8px;">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Results Area -->
        <main class="search-results">
            <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                <h1 style="font-size: 1.5rem; font-weight: 700;">
                    @if(request('q')) 
                        Hasil untuk "{{ request('q') }}"
                    @else
                        Semua Iklan
                    @endif
                    <span style="font-weight: 500; color: var(--text-muted); font-size: 1rem; margin-left: 10px;">({{ $listings->total() }} iklan ditemukan)</span>
                </h1>
                
                <div style="font-size: 0.9rem; color: var(--text-muted);">
                    Urutkan: <strong>Terbaru</strong>
                </div>
            </div>

            @if($listings->count() > 0)
                <div class="listing-grid">
                    @foreach($listings as $listing)
                    <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card">
                        @if($listing->getThumbnailUrl())
                            <img src="{{ $listing->getThumbnailUrl() }}" alt="{{ $listing->title }}" class="listing-image">
                        @endif
                        <div class="listing-details">
                            <div class="listing-category">
                                {{ $listing->categories->take(3)->pluck('name')->join(', ') }}
                                @if($listing->is_premium)
                                    <span class="badge badge-premium" style="margin-left: 5px; vertical-align: middle;">PREMIUM</span>
                                @endif
                                @if($listing->listingType)
                                    <span style="background: {{ $listing->listingType->color }}; color: white; padding: 2px 8px; border-radius: 4px; margin-left: 5px; font-size: 0.65rem; vertical-align: middle; display: inline-block;">
                                        {{ $listing->listingType->name }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="listing-title">{{ $listing->title }}</h3>
                            <div class="listing-location"><i class="fa-solid fa-location-dot"></i> {{ $listing->location }}</div>
                            @php
                                $cleanFeatures = array_slice(array_filter(array_map('trim', $listing->features ?? [])), 0, 4);
                            @endphp
                            <ul class="listing-features-summary {{ count($cleanFeatures) > 2 ? 'two-columns' : '' }}">
                                @foreach($cleanFeatures as $feature)
                                    <li><i class="fa-solid fa-check"></i> {{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="listing-right-panel">
                            <div class="listing-price">
                                @if($listing->price && $listing->price > 0)
                                    Rp {{ number_format($listing->price, 0, ',', '.') }}
                                @else
                                    Hubungi Kami
                                @endif
                            </div>
                            <div class="btn-whatsapp-sm">
                                <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>

                <div style="margin-top: 40px; display: flex; justify-content: center;">
                    {{ $listings->appends(request()->query())->links() }}
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
    @media (max-width: 992px) {
        .search-layout {
            grid-template-columns: 1fr;
        }
        .search-sidebar {
            display: none; /* We could implement a drawer for mobile later */
        }
    }
</style>
@endsection
