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
</style>
<section class="search-header" style="background: #ffffff; padding: 40px 0; border-bottom: 1px solid #f1f5f9; margin-bottom: 20px;">
    <div class="container" style="max-width: 800px;">
        <form action="{{ route('home') }}" method="GET" id="search-form" style="display: flex; gap: 10px;">
            <input type="text" name="q" id="search-input" value="{{ request('q') }}" placeholder="Cari apa saja di Batam... (Contoh: Tukang AC, Kos-kosan)" 
                style="flex: 1; padding: 12px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; outline: none; transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#0ea5e9'" onblur="this.style.borderColor='#e2e8f0'">
            <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 12px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: background 0.2s;"
                onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='#0ea5e9'">
                Cari
            </button>
            @if(request()->filled('q'))
                <a href="{{ route('home') }}" style="background: #f1f5f9; color: #64748b; text-decoration: none; padding: 12px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; transition: background 0.2s;"
                    onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    Reset
                </a>
            @endif
        </form>
    </div>
</section>


<div class="container page-section" style="padding-top: 0;">


<div id="listing-container">
    @if(request('q'))
        <h2 class="section-title">
            Hasil Pencarian: "{{ request('q') }}"
            <span style="font-size: 0.9rem; color: #94a3b8; font-weight: 600; margin-left: 10px;">{{ $recentListings->total() }} ditemukan</span>
        </h2>
    @endif
    <div class="listing-grid">
        @foreach($recentListings as $listing)
        <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card-grid">
            @if($listing->getThumbnailUrl())
            <div class="grid-image-wrapper">
                <img src="{{ $listing->getThumbnailUrl() }}" alt="{{ $listing->title }}">

            </div>
            @endif

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
        const searchForm = document.getElementById('search-form');
        const listingContainer = document.getElementById('listing-container');
        let timeout = null;

        function performSearch() {
            const q = searchInput.value;
            
            // Only search if empty (reset) or length >= 3
            if (q.length > 0 && q.length < 3) return;

            const url = new URL(searchForm.action);
            url.searchParams.set('q', q);

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

        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(performSearch, 500); // 500ms debounce
        });

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function() {
            window.location.reload();
        });
    });
</script>
@endsection
