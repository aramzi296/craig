@extends('layouts.app')

@section('content')
<style>
    /* Grid Layout Styles */
    .listing-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 15px;
        margin-bottom: 40px;
    }

    .listing-card-grid {
        background: #fff;
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .listing-card-grid:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: #0ea5e9;
    }

    .grid-image-wrapper {
        position: relative;
        width: 100%;
        padding-bottom: 100%; /* 1:1 Aspect Ratio */
        background: #f8fafc;
    }

    .price-tag {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(4px);
        color: #fff;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 700;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .grid-image-wrapper img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .grid-content {
        padding: 12px 8px;
    }

    .grid-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.4;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 2.8em;
    }

    @media (max-width: 576px) {
        .listing-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .grid-title {
            font-size: 0.75rem;
        }
        .price-current {
            font-size: 1rem;
        }
    }
    .advertiser-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .advertiser-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px -5px rgba(14, 165, 233, 0.15) !important;
    }
    @media (max-width: 768px) {
        .advertiser-card {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 20px !important;
            padding: 20px !important;
        }
    }
    .category-item {
        transition: all 0.2s ease;
    }
    .category-item:hover {
        background: #0ea5e9 !important;
        border-color: #0ea5e9 !important;
        color: #ffffff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
    }
    .category-item:hover span {
        color: #ffffff !important;
    }
</style>
<section class="search-header" style="background: #ffffff; padding: 40px 0; border-bottom: 1px solid #f1f5f9; margin-bottom: 20px;">
    <div class="container" style="max-width: 800px;">
        @php
            $districts = \Illuminate\Support\Facades\Cache::rememberForever('districts_list', function() {
                return \App\Models\District::orderBy('name')->get();
            });
        @endphp
        <form action="{{ route('home') }}" method="GET" id="search-form" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <div style="display: flex; gap: 10px; flex: 1; min-width: 250px;">
                <input type="text" name="q" id="search-input" value="{{ request('q') }}" placeholder="Cari apa saja di Batam... (Contoh: service AC, kost, rental mobil)" 
                    style="flex: 1; min-width: 150px; padding: 12px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#0ea5e9'" onblur="this.style.borderColor='#e2e8f0'">
                
                <select name="location" id="location-select" style="padding: 12px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; outline: none; transition: border-color 0.2s; background-color: white; max-width: 180px;"
                    onfocus="this.style.borderColor='#0ea5e9'" onblur="this.style.borderColor='#e2e8f0'">
                    <option value="">Semua Area</option>
                    @foreach($districts as $district)
                        <option value="{{ $district->id }}" {{ request('location') == $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 12px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: background 0.2s; white-space: nowrap;"
                onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='#0ea5e9'">
                Cari
            </button>
            @if(request()->filled('q') || request()->filled('category') || request()->filled('tag') || request()->filled('location'))
                <a href="{{ route('home') }}" style="background: #f1f5f9; color: #64748b; text-decoration: none; padding: 12px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; transition: background 0.2s; white-space: nowrap;"
                    onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    Reset
                </a>
            @endif
        </form>
    </div>
</section>


<div class="container page-section" style="padding-top: @if(isset($user)) 20px @else 0 @endif;">

    @if(isset($user))
        <div class="advertiser-card" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(240, 249, 255, 0.6) 100%); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(14, 165, 233, 0.15); border-radius: 20px; padding: 28px; box-shadow: 0 10px 30px -5px rgba(14, 165, 233, 0.08); margin-bottom: 35px; display: flex; align-items: center; gap: 28px; position: relative; overflow: hidden;">
            <!-- Background accent gradients -->
            <div style="position: absolute; top: -50px; right: -50px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(14, 165, 233, 0.18) 0%, rgba(255,255,255,0) 70%); pointer-events: none; border-radius: 50%;"></div>
            <div style="position: absolute; bottom: -60px; left: -20px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(37, 99, 235, 0.08) 0%, rgba(255,255,255,0) 70%); pointer-events: none; border-radius: 50%;"></div>

            <!-- Avatar -->
            <div class="advertiser-avatar-wrapper" style="position: relative; flex-shrink: 0;">
                <img src="{{ $user->getProfilePhoto() }}" alt="{{ $user->name }}" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 8px 20px rgba(0,0,0,0.06);">
                <div style="position: absolute; bottom: 0; right: 0; background: #10b981; border: 2px solid #ffffff; width: 18px; height: 18px; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" title="Aktif"></div>
            </div>

            <!-- Profile Info -->
            <div style="flex-grow: 1; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <h1 style="margin: 0; font-size: 1.8rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; line-height: 1.2;">{{ $user->name }}</h1>
                    <span style="background: linear-gradient(90deg, #0ea5e9, #2563eb); color: #ffffff; font-size: 0.7rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 2px 8px rgba(14, 165, 233, 0.2);">Verified Partner</span>
                </div>

                <div style="display: flex; gap: 20px; margin-top: 12px; flex-wrap: wrap;">
                    @if($user->whatsapp)
                        <a href="https://wa.me/{{ $user->whatsapp }}" target="_blank" rel="noopener noreferrer" style="color: #475569; text-decoration: none; font-size: 0.88rem; display: flex; align-items: center; gap: 8px; transition: color 0.2s;" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#475569'">
                            <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem; color: #10b981;"></i>
                            <span>+{{ $user->whatsapp }}</span>
                        </a>
                    @endif
                    <div style="color: #475569; font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-regular fa-calendar-check" style="font-size: 1.05rem; color: #0ea5e9;"></i>
                        <span>Bergabung {{ $user->created_at->format('M Y') }}</span>
                    </div>
                    <div style="color: #475569; font-size: 0.88rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-layer-group" style="font-size: 1.05rem; color: #2563eb;"></i>
                        <span>{{ $recentListings->total() }} Iklan Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

<div id="listing-container">
    @if(isset($user))
        <h2 class="section-title" style="font-size: 1.3rem; font-weight: 700; color: #1e293b; margin-bottom: 20px;">
            Semua Iklan dari {{ $user->name }}
            <span style="font-size: 0.9rem; color: #94a3b8; font-weight: 600; margin-left: 10px;">{{ $recentListings->total() }} iklan</span>
        </h2>
    @elseif(request('q'))
        <h2 class="section-title">
            Hasil Pencarian: "{{ request('q') }}"
            <span style="font-size: 0.9rem; color: #94a3b8; font-weight: 600; margin-left: 10px;">{{ $recentListings->total() }} ditemukan</span>
        </h2>
    @elseif(request('category'))
        @php
            $currentCategory = \App\Models\Category::where('slug', request('category'))->first();
        @endphp
        @if($currentCategory)
            <h2 class="section-title" style="font-size: 1.3rem; font-weight: 700; color: #1e293b; margin-bottom: 20px;">
                Kategori: {{ $currentCategory->name }}
                <span style="font-size: 0.9rem; color: #94a3b8; font-weight: 600; margin-left: 10px;">{{ $recentListings->total() }} iklan</span>
            </h2>
        @endif
    @elseif(request('tag'))
        @php
            $currentTag = \App\Models\Tag::where('slug', request('tag'))->first();
        @endphp
        @if($currentTag)
            <h2 class="section-title" style="font-size: 1.3rem; font-weight: 700; color: #1e293b; margin-bottom: 20px;">
                Tagar: #{{ $currentTag->name }}
                <span style="font-size: 0.9rem; color: #94a3b8; font-weight: 600; margin-left: 10px;">{{ $recentListings->total() }} iklan</span>
            </h2>
        @endif
    @endif
    


    <div class="listing-grid">
        @foreach($recentListings as $listing)
        <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card-grid">
            <div class="grid-image-wrapper">
                @if($listing->getThumbnailUrl())
                    <img src="{{ $listing->getThumbnailUrl() }}" alt="{{ $listing->title }}">
                @else
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8fafc; color: #94a3b8; font-size: 0.75rem; font-weight: 700; gap: 6px; border-bottom: 1px solid #f1f5f9;">
                        <i class="fa-regular fa-image" style="font-size: 1.5rem; color: #cbd5e1;"></i>
                        <span>No Picture</span>
                    </div>
                @endif
            </div>

            <div class="grid-content">
                <h3 class="grid-title">{{ $listing->title }}</h3>
            </div>
        </a>
        @endforeach
    </div>
    
    <div style="margin-top: 60px; display: flex; justify-content: center;">
        {{ $recentListings->appends(request()->query())->links('vendor.pagination.simple-custom') }}
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const locationSelect = document.getElementById('location-select');
        const searchForm = document.getElementById('search-form');
        const listingContainer = document.getElementById('listing-container');

        function performSearch() {
            const q = searchInput.value;
            const location = locationSelect ? locationSelect.value : '';
            
            // Only search if empty (reset) or length >= 3
            if (q.length > 0 && q.length < 3) return;

            const url = new URL(searchForm.action);
            if (q) url.searchParams.set('q', q);
            if (location) url.searchParams.set('location', location);
            
            const currentParams = new URLSearchParams(window.location.search);
            if (currentParams.has('category')) url.searchParams.set('category', currentParams.get('category'));
            if (currentParams.has('tag')) url.searchParams.set('tag', currentParams.get('tag'));

            // Show loading state
            listingContainer.style.opacity = '0.5';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('listing-container');
                
                if (newContent) {
                    listingContainer.innerHTML = newContent.innerHTML;
                    listingContainer.style.opacity = '1';
                    
                    // Update URL without refresh
                    window.history.pushState({ path: url.href }, '', url.href);
                }
            })
            .catch(error => {
                console.error('Error fetching search results:', error);
                listingContainer.style.opacity = '1';
            });
        }

        // Pemicu pencarian ketika menekan tombol Spasi (kata sudah utuh)
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === ' ' || e.code === 'Space') {
                performSearch();
            }
        });

        // Pemicu pencarian ketika input dihapus bersih (reset instan)
        searchInput.addEventListener('input', function() {
            if (searchInput.value === '') {
                performSearch();
            }
        });

        if (locationSelect) {
            locationSelect.addEventListener('change', function() {
                performSearch();
            });
        }

        // Intersepsi submit form (klik tombol Cari atau tekan Enter)
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function() {
            window.location.reload();
        });
    });
</script>
@endsection
