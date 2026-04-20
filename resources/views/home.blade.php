@extends('layouts.app')

@section('content')
<section class="hero" style="background-image: url('{{ asset('gelombang.png') }}');">
    <div class="container">
        <h1>Temukan Segalanya di Batam</h1>
        <p>Langsung Chat WhatsApp Tanpa Ribet.</p>
        
        <form action="{{ route('search') }}" method="GET" class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" placeholder="Apa yang sedang Anda cari hari ini?" value="{{ request('q') }}">
            <button type="submit" class="btn btn-primary" style="margin-right: -2px; border-radius: 50px; padding: 12px 30px;">Cari</button>
        </form>
    </div>
</section>

<div class="container page-section" style="padding-top: 20px;">
    <div class="category-grid" style="justify-content: center; margin-bottom: 20px;">
        @foreach($listingTypes as $type)
        <a href="{{ route('search', ['type' => $type->slug]) }}" class="category-card" style="border-color: {{ $type->color ?? 'var(--border)' }}; color: var(--text); padding: 8px 20px; font-size: 0.85rem;">
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $type->color ?? 'var(--primary)' }}; margin-right: 8px;"></span>
            {{ $type->name }}
        </a>
        @endforeach
    </div>

    @if($premiumListings->count() > 0)
    <div class="listing-grid">
        @foreach($premiumListings as $listing)
        <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card">
            <img src="{{ $listing->getThumbnailUrl() }}" alt="{{ $listing->title }}" class="listing-image">
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
    @endif

    <h2 class="section-title">Listing Terbaru</h2>
    <div class="listing-grid">
        @foreach($recentListings as $listing)
        <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card">
            <img src="{{ $listing->getThumbnailUrl() }}" alt="{{ $listing->title }}" class="listing-image">
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
    
    <div style="margin-top: 60px; display: flex; justify-content: center;">
        {{ $recentListings->appends(request()->query())->links() }}
    </div>
</div>
@endsection
