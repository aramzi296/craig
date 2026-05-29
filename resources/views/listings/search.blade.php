@extends('layouts.app')

@section('content')
<section class="hero" style="background: linear-gradient(rgba(219, 234, 254, 0.6), rgba(219, 234, 254, 0.6)), url('{{ asset('batam-hero.jpg') }}') no-repeat center center; background-size: cover; border-bottom: 1px solid #e5e7eb; padding: 60px 0;">
    <div class="container" style="max-width: 800px; text-align: center;">
        <h2 style="font-size: 3rem; font-weight: 800; margin-bottom: 12px; color: #111827; text-shadow: 0 2px 4px rgba(255,255,255,0.5); letter-spacing: -0.02em;">
            @if(request('q')) 
                Hasil: "{{ request('q') }}"
            @else
                Cari di Batam
            @endif
        </h2>
        <p style="color: #374151; font-size: 1.3rem; margin-bottom: 40px; font-weight: 500;">
            Temukan berbagai iklan dan penawaran terbaik dengan cepat.
        </p>
        
        <form action="{{ route('search') }}" method="GET" class="search-box" style="box-shadow: 0 4px 20px -2px rgba(0,0,0,0.1);">
            <input type="text" name="q" placeholder="Contoh: Tukang AC, Kos-kosan..." value="{{ request('q') }}">
            @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
            @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif
            @if(request('location')) <input type="hidden" name="location" value="{{ request('location') }}"> @endif
            <button type="submit">CARI</button>
        </form>
    </div>
</section>

<div class="container" style="margin-top: 30px;">
    <!-- Filter Toggle Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h2 style="margin: 0; font-size: 1.4rem; font-weight: 800; color: #1e293b;">
                @if(request('q')) 
                    Hasil: "{{ request('q') }}"
                @else
                    Semua Iklan
                @endif
                <span style="font-weight: 600; color: #94a3b8; font-size: 0.9rem; margin-left: 8px;">{{ $listings->total() }} ditemukan</span>
            </h2>
        </div>
        <button id="filter-toggle-btn" onclick="toggleFilterPanel()" style="background: white; border: 1px solid #e2e8f0; padding: 10px 20px; border-radius: 50px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <i class="fa-solid fa-filter"></i> 
            <span>Tampilkan Filter</span>
        </button>
    </div>

    <!-- Collapsible Filter Panel -->
    <div id="filter-panel" style="display: none; background: #f8fafc; padding: 25px; border-radius: 16px; border: 1px solid #f1f5f9; margin-bottom: 30px;">
        <form action="{{ route('search') }}" method="GET" id="filter-form">
            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: flex-end;">
                <!-- Type Filter -->
                <div class="filter-group">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Tipe Iklan</label>
                    <select name="type" class="form-control" onchange="this.form.submit()" style="width: 100%; background: white; border-radius: 10px; font-size: 0.9rem; border: 1px solid #e2e8f0; padding: 12px;">
                        <option value="">Semua Tipe</option>
                        @foreach($listingTypes as $type)
                            <option value="{{ $type->slug }}" {{ request('type') == $type->slug ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="filter-group">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Kategori</label>
                    <select name="category" class="form-control" onchange="this.form.submit()" style="width: 100%; background: white; border-radius: 10px; font-size: 0.9rem; border: 1px solid #e2e8f0; padding: 12px;">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Location Filter -->
                <div class="filter-group">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Lokasi</label>
                    <select name="location" class="form-control" onchange="this.form.submit()" style="width: 100%; background: white; border-radius: 10px; font-size: 0.9rem; border: 1px solid #e2e8f0; padding: 12px;">
                        <option value="">Semua Lokasi</option>
                        @foreach($districts as $dist)
                            <option value="{{ $dist->id }}" {{ request('location') == $dist->id ? 'selected' : '' }}>{{ $dist->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="text-align: right;">
                    <a href="{{ route('search') }}" style="color: #ef4444; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; padding: 12px;">
                        <i class="fa-solid fa-rotate-left"></i> Reset Filter
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Area -->
    <main class="search-results">
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
                                {{ $listing->approvedCategories->take(1)->pluck('name')->join('') }}

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

            <div style="margin-top: 40px; display: flex; justify-content: center; padding-bottom: 60px;">
                {{ $listings->appends(request()->query())->links('vendor.pagination.simple-custom') }}
            </div>
        @else
            <div style="text-align: center; padding: 80px 20px; background: #f8fafc; border-radius: 12px; border: 1px dashed #e2e8f0; margin-bottom: 100px;">
                <i class="fa-solid fa-magnifying-glass" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                <h3 style="font-weight: 800; color: #1e293b; margin-bottom: 8px;">Tidak ada hasil ditemukan</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Coba gunakan kata kunci lain atau hapus filter Anda.</p>
                <a href="{{ route('search') }}" class="btn btn-primary" style="padding: 10px 25px; font-weight: 700;">Lihat Semua Iklan</a>
            </div>
        @endif
    </main>
</div>

<script>
    function toggleFilterPanel() {
        const panel = document.getElementById('filter-panel');
        const btn = document.getElementById('filter-toggle-btn');
        const span = btn.querySelector('span');
        const icon = btn.querySelector('i');
        
        if (panel.style.display === 'none') {
            panel.style.display = 'block';
            span.textContent = 'Tutup Filter';
            icon.classList.remove('fa-filter');
            icon.classList.add('fa-xmark');
            btn.style.background = '#f1f5f9';
        } else {
            panel.style.display = 'none';
            span.textContent = 'Tampilkan Filter';
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-filter');
            btn.style.background = 'white';
        }
    }

    // Auto-open if filters are active
    @if(request('type') || request('category') || request('location'))
        document.addEventListener('DOMContentLoaded', function() {
            toggleFilterPanel();
        });
    @endif
</script>
@endsection
