@extends('layouts.app')

@section('content')
<section class="hero" style="background-image: url('{{ asset('gelombang.png') }}');">
    <div class="container">
        <h1>Temukan Segalanya di Batam</h1>
        <p>Langsung Chat WhatsApp Tanpa Ribet.</p>
        
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Apa yang sedang Anda cari hari ini?">
            <button class="btn btn-primary" style="margin-right: -2px; border-radius: 50px; padding: 12px 30px;">Cari</button>
        </div>
    </div>
</section>

<div class="container page-section" style="padding-top: 20px;">
    <h2 class="section-title">Kategori Populer</h2>
    <div class="category-grid">
        @foreach($categories as $category)
        <a href="{{ route('home', ['category' => $category->slug]) }}" class="category-card {{ $selectedCategory && $selectedCategory->id == $category->id ? 'active-category' : '' }}">
            {{ $category->name }}
        </a>
        @endforeach
    </div>

    <div style="margin-top: 16px; text-align: center;">
        <a href="{{ route('categories.index') }}" class="btn btn-outline-primary">
            Lihat Semua Kategori
        </a>
    </div>

    @if($selectedCategory)
        <div style="margin-top: 60px; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 2rem; font-weight: 700;">Menampilkan: {{ $selectedCategory->name }}</h2>
            <a href="{{ route('home') }}" style="color: var(--primary); font-weight: 600;">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    @endif

    @if($featuredListings->count() > 0 && !$selectedCategory)
    <h2 class="section-title">Iklan Premium</h2>
    <div class="listing-grid">
        @foreach($featuredListings as $listing)
        <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card">
            <img src="{{ $listing->getThumbnailUrl() }}" alt="{{ $listing->title }}" class="listing-image">
            <div class="listing-details">
                <div class="listing-category">
                    {{ $listing->categories->pluck('name')->join(', ') }}
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
                <div class="listing-price">Rp {{ number_format($listing->price, 0, ',', '.') }}</div>
                <div class="btn-whatsapp-sm">
                    <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    <h2 class="section-title">{{ $selectedCategory ? 'Listing di ' . $selectedCategory->name : 'Listing Terbaru' }}</h2>
    <div class="listing-grid">
        @foreach($recentListings as $listing)
        <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card">
            <img src="{{ $listing->getThumbnailUrl() }}" alt="{{ $listing->title }}" class="listing-image">
            <div class="listing-details">
                <div class="listing-category">
                    {{ $listing->categories->pluck('name')->join(', ') }}
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
                <div class="listing-price">Rp {{ number_format($listing->price, 0, ',', '.') }}</div>
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
