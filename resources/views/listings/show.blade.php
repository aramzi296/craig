@extends('layouts.app')

@section('content')
<div class="container listing-detail-container">
    <nav style="margin-bottom: 20px; color: var(--text-muted); font-size: 0.9rem;">
        <a href="{{ route('home') }}">Beranda</a> / 
        <a href="{{ route('home', ['category' => $listing->categories->first()->slug ?? 'lainnya']) }}">{{ $listing->categories->first()->name ?? 'Tanpa Kategori' }}</a> / 
        {{ $listing->title }}
    </nav>

    <div class="listing-details-grid">
        <!-- Image Component -->
        <div class="listing-main-image">
            <div class="glass" style="border-radius: var(--radius); overflow: hidden; background: white; line-height: 0;">
                <img src="{{ $listing->getImageUrl() }}" alt="{{ $listing->title }}" style="width: 100%; height: auto; object-fit: cover; display: block;">
            </div>
        </div>

        <!-- Description Component -->
        <div class="listing-description-box">
            <div class="glass" style="padding: 40px; border-radius: var(--radius); background: white;">
                <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 25px; color: var(--text);">Deskripsi</h2>
                <div style="line-height: 1.8; color: var(--text); font-size: 1.05rem;">
                    {!! nl2br(e($listing->description)) !!}
                </div>
            </div>
        </div>

        <!-- Sidebar Component -->
        <aside class="listing-sidebar-info" style="position: sticky; top: 100px;">
            <div class="glass" style="padding: 35px; border-radius: var(--radius); background: white; border: 1px solid var(--border);">
                <div style="color: var(--primary); font-weight: 700; font-size: 0.85rem; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ $listing->categories->pluck('name')->join(' • ') }}
                </div>
                <h1 style="font-size: 2.2rem; font-weight: 700; margin-bottom: 15px; color: var(--text); line-height: 1.2;">{{ $listing->title }}</h1>
                
                <div style="font-size: 2.6rem; font-weight: 800; color: var(--primary); margin-bottom: 30px; letter-spacing: -1px;">
                    Rp {{ number_format($listing->price, 0, ',', '.') }}
                </div>

                @if(!empty($listing->features) && count(array_filter($listing->features)) > 0)
                <div style="margin-bottom: 35px;">
                    <ul style="list-style: none; padding: 0; display: grid; gap: 12px;">
                        @foreach(array_filter($listing->features) as $feature)
                        <li style="display: flex; align-items: center; gap: 12px; font-size: 1rem; color: var(--text); font-weight: 500;">
                            <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.2rem;"></i> {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div style="display: flex; align-items: center; gap: 10px; color: var(--text-muted); margin-bottom: 35px; font-size: 1.05rem; font-weight: 500;">
                    <i class="fa-solid fa-location-dot" style="color: var(--secondary); font-size: 1.2rem;"></i> {{ $listing->location }}, Batam
                </div>

                <div style="display: grid; gap: 15px;">
                    <a href="https://wa.me/628123456789?text=Halo, saya tertarik dengan {{ $listing->title }}" target="_blank" class="btn btn-primary" style="display: flex; align-items: center; justify-content: center; gap: 12px; padding: 18px; font-size: 1.1rem; border-radius: 12px;">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.5rem;"></i> Hubungi via WhatsApp
                    </a>
                    
                    @auth
                    <form action="{{ route('listings.favorite', $listing->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'btn-secondary' : 'btn-outline' }}" style="padding: 18px; font-size: 1.1rem; width: 100%; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <i class="fa-{{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'solid' : 'regular' }} fa-heart" style="{{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'color: #ef4444;' : '' }}"></i> 
                            {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'Favorit Terdaftar' : 'Tambah ke Favorit' }}
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-outline" style="padding: 18px; font-size: 1.1rem; text-align: center; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <i class="fa-regular fa-heart"></i> Tambah ke Favorit
                    </a>
                    @endauth
                </div>

                <div style="margin-top: 30px; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
                    Iklan ID: #BT{{ 1000 + $listing->id }} • Diperbarui {{ $listing->updated_at->diffForHumans() }}
                </div>
            </div>
        </aside>
    </div>

    <!-- Related Listings -->
    @if($relatedListings->count() > 0)
    <div style="margin-top: 80px;">
        <h2 class="section-title">Postingan Lainnya</h2>
        <div class="listing-grid">
            @foreach($relatedListings as $related)
            <a href="{{ route('listings.show', $related->slug) }}" class="listing-card">
                <img src="{{ $related->getThumbnailUrl() }}" alt="{{ $related->title }}" class="listing-image">
                <div class="listing-details">
                    <div class="listing-category">{{ $related->categories->pluck('name')->join(', ') }}</div>
                    <h3 class="listing-title">{{ $related->title }}</h3>
                    <div class="listing-footer">
                        <div class="listing-price">Rp {{ number_format($related->price, 0, ',', '.') }}</div>
                        <div class="listing-location"><i class="fa-solid fa-location-dot"></i> {{ $related->location }}</div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
