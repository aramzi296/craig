@extends('layouts.app')

@section('content')
<section class="hero" style="background-image: url('{{ asset('gelombang.png') }}');">
    <div class="container">
        <!-- <h1>Sebut Ape Nak Carek di Batam</h1> -->
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
        <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card" target="_blank">
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
                <div class="listing-location"><i class="fa-solid fa-location-dot"></i> {{ $listing->district?->name ?? 'Batam' }}</div>
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
        <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card" target="_blank">
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
                <div class="listing-location"><i class="fa-solid fa-location-dot"></i> {{ $listing->district?->name ?? 'Batam' }}</div>
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
        {{ $recentListings->appends(request()->query())->links('vendor.pagination.simple-custom') }}
    </div>
</div>
@endsection
