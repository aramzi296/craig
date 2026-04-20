@extends('layouts.app')

@section('content')
<div class="container listing-detail-container">
    <nav style="margin-bottom: 20px; color: var(--text-muted); font-size: 0.9rem;">
        <a href="{{ route('home') }}">Beranda</a> / 
        <a href="{{ route('home', ['category' => $listing->categories->first()->slug ?? 'lainnya']) }}">{{ $listing->categories->first()->name ?? 'Tanpa Kategori' }}</a> / 
        {{ $listing->title }}
    </nav>

    <div class="listing-details-grid">
        <div class="listing-main-column">
            <!-- Details Header -->
            <div class="glass" style="padding: 30px; border-radius: var(--radius); margin-bottom: 25px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
                    @if($listing->listingType)
                        <span style="background: {{ $listing->listingType->color ?? 'var(--primary)' }}; color: white; padding: 4px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ $listing->listingType->name }}
                        </span>
                    @endif
                    <div style="color: var(--primary); font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        {{ $listing->categories->pluck('name')->join(' • ') }}
                    </div>
                </div>
                
                <h1 style="font-size: 2.2rem; font-weight: 700; margin-bottom: 0; color: var(--text); line-height: 1.2;">
                    {{ $listing->title }}
                    @if($listing->is_premium)
                        <span class="badge badge-premium" style="font-size: 0.8rem; vertical-align: middle; margin-top: -5px; display: inline-block;">PREMIUM</span>
                    @endif
                </h1>
            </div>

            <!-- Image Component -->
            <div class="glass" style="border-radius: var(--radius); overflow: hidden; line-height: 0; margin-bottom: 25px;">
                <img src="{{ $listing->getImageUrl() }}" alt="{{ $listing->title }}" style="width: 100%; height: auto; object-fit: cover; display: block;">
            </div>

            <!-- Features Component -->
            @if(!empty($listing->features) && count(array_filter($listing->features)) > 0)
            <div class="glass" style="padding: 30px; border-radius: var(--radius); margin-bottom: 0px;">
                <ul style="list-style: none; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                    @foreach(array_filter($listing->features) as $feature)
                    <li style="display: flex; align-items: center; gap: 12px; font-size: 1rem; color: var(--text); font-weight: 500;">
                        <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.2rem;"></i> {{ $feature }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Description Component -->
            <div class="glass" style="padding: 30px; border-radius: var(--radius); margin-bottom: 25px;">
                <div style="line-height: 1.8; color: var(--text); font-size: 1.05rem;">
                    {!! nl2br(e($listing->description)) !!}
                </div>
            </div>

            <!-- Footer Details (Dan Seterusnya) -->
            <div class="glass" style="padding: 35px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 40px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: center; margin-bottom: 30px;">
                    <div>
                        <div style="font-size: 2.6rem; font-weight: 800; color: var(--primary); letter-spacing: -1px;">
                            Rp {{ number_format($listing->price, 0, ',', '.') }}
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; color: var(--text-muted); margin-top: 10px; font-size: 1.05rem; font-weight: 500;">
                            <i class="fa-solid fa-location-dot" style="color: var(--secondary); font-size: 1.2rem;"></i> {{ $listing->location }}, Batam
                        </div>
                        @if($listing->listingType)
                        <div style="display: flex; align-items: center; gap: 10px; color: var(--text-muted); margin-top: 10px; font-size: 1.05rem; font-weight: 500;">
                            <i class="fa-solid fa-tag" style="color: var(--secondary); font-size: 1.2rem;"></i> Tipe Iklan: {{ $listing->listingType->name }}
                        </div>
                        @endif
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 12px; padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border);">
                        <img src="{{ $listing->user->getProfilePhoto() }}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" alt="">
                        <div>
                            <div style="font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 4px;">
                                {{ $listing->user->name }}
                                @if($listing->user->is_verified)
                                    <i class="fa-solid fa-circle-check verified-badge" style="font-size: 0.8rem;" title="Akun Terverifikasi"></i>
                                @endif
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Member sejak {{ $listing->user->created_at->format('M Y') }}</div>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <a href="https://wa.me/{{ $listing->user->whatsapp }}?text=Halo {{ $listing->user->name }}, saya tertarik dengan iklan Anda di Sebatam: {{ $listing->title }}. Apakah masih tersedia%3F" target="_blank" class="btn btn-primary" style="padding: 18px; font-size: 1.1rem; border-radius: 12px;">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.5rem;"></i> Hubungi via WhatsApp
                    </a>
                    
                    @auth
                        <form action="{{ route('listings.favorite', $listing->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'btn-secondary' : 'btn-outline' }}" style="padding: 18px; font-size: 1.1rem; width: 100%; border-radius: 12px;">
                                <i class="fa-{{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'solid' : 'regular' }} fa-heart" style="{{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'color: #ef4444;' : '' }}"></i> 
                                {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'Favorit Terdaftar' : 'Tambah ke Favorit' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline" style="padding: 18px; font-size: 1.1rem; text-align: center; border-radius: 12px;">
                            <i class="fa-regular fa-heart"></i> Tambah ke Favorit
                        </a>
                    @endauth
                </div>

                <div style="margin-top: 30px; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
                    Iklan ID: #BT{{ 1000 + $listing->id }} • Dilihat {{ number_format($listing->views_count, 0, ',', '.') }} kali • Diperbarui {{ $listing->updated_at->diffForHumans() }}
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <aside class="listing-sidebar-info">
            <!-- Premium Listings Section -->
            @if($sidebarPremiumListings->count() > 0)
                <div style="margin-bottom: 40px;">
                    <h2 class="section-title" style="margin-top: 0; margin-bottom: 20px; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; color: #b45309;">
                        <i class="fa-solid fa-crown"></i> Postingan Premium
                    </h2>
                    <div class="listing-grid">
                        @foreach($sidebarPremiumListings as $premium)
                        <a href="{{ route('listings.show', $premium->slug) }}" class="listing-card" style="height: auto; flex-direction: row; padding: 12px; gap: 15px; align-items: center; border-left: 3px solid #f59e0b; background: #fffbeb;">
                            <img src="{{ $premium->getThumbnailUrl() }}" alt="{{ $premium->title }}" class="listing-image" style="width: 80px; height: 80px; margin: 0; border-radius: 8px; flex-shrink: 0;">
                            <div class="listing-details" style="padding: 0; flex: 1;">
                                <div class="listing-category" style="font-size: 0.65rem; margin-bottom: 2px; display: flex; align-items: center; gap: 5px;">
                                    <span class="badge badge-premium" style="font-size: 0.55rem; padding: 2px 4px;">PREMIUM</span>
                                    <span>{{ $premium->categories->first()->name ?? '' }}</span>
                                </div>
                                <h3 class="listing-title" style="font-size: 0.9rem; margin-bottom: 4px; line-height: 1.3; color: var(--text);">{{ $premium->title }}</h3>
                                <div class="listing-price" style="font-size: 0.95rem; margin-bottom: 0; color: var(--primary); font-weight: 700;">Rp {{ number_format($premium->price, 0, ',', '.') }}</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Related Listings Section -->
            @if($relatedListings->count() > 0)
                <div>
                    <h2 class="section-title" style="margin-top: 0; margin-bottom: 20px; font-size: 1.3rem;">Postingan Terkait</h2>
                    <div class="listing-grid">
                        @foreach($relatedListings as $related)
                        <a href="{{ route('listings.show', $related->slug) }}" class="listing-card" style="height: auto; flex-direction: row; padding: 12px; gap: 15px; align-items: center;">
                            <img src="{{ $related->getThumbnailUrl() }}" alt="{{ $related->title }}" class="listing-image" style="width: 80px; height: 80px; margin: 0; border-radius: 8px; flex-shrink: 0;">
                            <div class="listing-details" style="padding: 0; flex: 1;">
                                <div class="listing-category" style="font-size: 0.7rem; margin-bottom: 2px; display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                                    @if($related->listingType)
                                        <span style="background: {{ $related->listingType->color }}; color: white; padding: 1px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 700;">
                                            {{ $related->listingType->name }}
                                        </span>
                                    @endif
                                    <span>{{ $related->categories->take(1)->pluck('name')->join(', ') }}</span>
                                </div>
                                <h3 class="listing-title" style="font-size: 0.9rem; margin-bottom: 4px; line-height: 1.3;">{{ $related->title }}</h3>
                                <div class="listing-price" style="font-size: 0.95rem; margin-bottom: 0; color: var(--primary); font-weight: 700;">Rp {{ number_format($related->price, 0, ',', '.') }}</div>
                                <div class="listing-location" style="font-size: 0.75rem; margin: 0; color: var(--text-muted);"><i class="fa-solid fa-location-dot"></i> {{ $related->location }}</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div style="margin-top: 30px; display: flex; flex-direction: column; gap: 10px;">
                <a href="{{ route('home') }}" class="btn btn-outline" style="width: 100%;">Lihat Semua Iklan</a>
                @auth
                    @if($listing->user_id == auth()->id())
                        <a href="{{ route('listings.edit', $listing->id) }}" class="btn btn-outline" style="width: 100%; border-color: var(--accent); color: var(--accent);">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Postingan
                        </a>
                        @if(!$listing->is_premium && !$listing->hasPendingPremiumRequest())
                            <a href="{{ route('dashboard.premium.upgrade', $listing->id) }}" class="btn btn-primary" style="width: 100%; background: #f59e0b; border-color: #f59e0b;">
                                <i class="fa-solid fa-crown"></i> Upgrade ke Premium
                            </a>
                        @endif
                    @endif
                @endauth
            </div>
        </aside>
    </div>
</div>
@endsection
