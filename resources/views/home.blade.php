@extends('layouts.app')

@section('content')
<section class="hero" style="background-image: url('{{ asset('gelombang.png') }}');">
    <div class="container">
        <h1 class="hidden md:block">Cari Apa Saja Sebatam</h1>
        <p class="hidden md:block">Dengan satu atau beberapa kata kunci, temukan berbagai iklan dan penawaran terbaik di Batam dengan mudah dan cepat. Testing perubahan</p>
        <div style="display: flex; gap: 15px; justify-content: center; align-items: center; flex-wrap: wrap;">
            <form action="{{ route('search') }}" method="GET" class="search-box" style="margin: 0; width: 100%; max-width: 600px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" placeholder="Apa yang sedang Anda cari hari ini?" value="{{ request('q') }}">
                <button type="submit" class="btn btn-primary" style="margin-right: -2px; border-radius: 50px; padding: 12px 30px;">Cari</button>
            </form>
            <a href="{{ route('listings.create') }}" class="btn btn-primary" style="border-radius: 50px; padding: 15px 30px; height: 58px; background: var(--secondary); border: none; box-shadow: 0 4px 14px 0 rgba(249, 115, 22, 0.39);">
                <i class="fa-solid fa-plus"></i> Pasang Iklan Anda
            </a>
        </div>
    </div>
</section>

<div class="container page-section" style="padding-top: 0;">

    @if($premiumListings->count() > 0)
    <h2 class="section-title" style="margin-top: 20px;">Postingan Premium</h2>
    <div class="listing-grid">
        @foreach($premiumListings as $listing)
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
    @endif

    <h2 class="section-title">Listing Terbaru</h2>
    <div class="listing-grid">
        @foreach($recentListings as $listing)
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
    
    <div style="margin-top: 60px; display: flex; justify-content: center;">
        {{ $recentListings->appends(request()->query())->links('vendor.pagination.simple-custom') }}
    </div>
</div>
@endsection
