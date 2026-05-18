@extends('layouts.app')

@section('title', 'Daftar Lapak - ' . config('app.name'))

@section('content')
<div class="container" style="max-width: 800px; margin: 40px auto; padding: 0 20px;">
    <!-- Search Box -->
    <div style="margin-bottom: 40px;">
        <form action="{{ route('listings.index') }}" method="GET" id="search-form" style="display: flex; gap: 10px;">
            @if(request()->filled('type'))
                <input type="hidden" name="type" value="{{ request('type') }}">
            @endif
            <input type="text" name="q" id="search-input" value="{{ request('q') }}" placeholder="Cari listing..." 
                style="flex: 1; padding: 12px 20px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; outline: none; transition: border-color 0.2s;"
                onfocus="this.style.borderColor='#0ea5e9'" onblur="this.style.borderColor='#e2e8f0'">
            <button type="submit" style="background: #0ea5e9; color: white; border: none; padding: 12px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: background 0.2s;"
                onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='#0ea5e9'">
                Cari
            </button>
            @if(request()->filled('q') || request()->filled('type'))
                <a href="{{ route('listings.index') }}" style="background: #f1f5f9; color: #64748b; text-decoration: none; padding: 12px 20px; border-radius: 12px; font-weight: 700; display: flex; align-items: center; transition: background 0.2s;"
                    onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div id="listing-container">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 25px;">
            {{ $selectedType ? $selectedType->name : 'Semua Tipe Lapak' }}
        </h1>

        <!-- Listing Rows -->
        <div style="display: flex; flex-direction: column; gap: 0;">
            @forelse($listings as $listing)
                <div style="padding: 25px 0; border-bottom: 1px solid #f1f5f9; display: flex; gap: 25px; transition: background 0.2s;">
                    <!-- Photo Column -->
                    @if($listing->getThumbnailUrl())
                    <div style="flex-shrink: 0; width: 160px; height: 160px; border-radius: 16px; overflow: hidden; background: #f8fafc; border: 1px solid #f1f5f9;">
                        <a href="{{ route('listings.show', $listing->slug) }}">
                            <img src="{{ $listing->getThumbnailUrl() }}" alt="{{ $listing->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                        </a>
                    </div>
                    @endif

                    <!-- Content Column -->
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 8px;">
                        <a href="{{ route('listings.show', $listing->slug) }}" style="text-decoration: none; color: inherit;">
                            <h2 style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;">
                                {{ $listing->title }}
                            </h2>
                        </a>
                        
                        <p style="font-size: 0.95rem; color: #64748b; margin: 0; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $listing->description }}
                        </p>
                        
                        <div style="display: flex; align-items: center; gap: 20px; font-size: 0.85rem; color: #94a3b8; font-weight: 600;">
                            <span style="color: #0ea5e9;">{{ $listing->district ? $listing->district->name : 'Batam' }}</span>
                            <span style="color: #0f172a; font-weight: 800;">Rp {{ number_format($listing->price, 0, ',', '.') }}</span>
                            <span>photo({{ $listing->photos_count }})</span>
                            <span>{{ $listing->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 20px;">
                    <p style="color: #64748b; font-size: 1.1rem; margin: 0;">Tidak ada listing ditemukan.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $listings->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<style>
    /* Advanced Pagination Styling */
    .pagination-container {
        margin-top: 60px;
        padding-top: 40px;
        border-top: 1px solid #f1f5f9;
    }

    /* Target the nav element from Laravel */
    .pagination-container nav {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Hide the default "Showing..." info for a cleaner look */
    .pagination-container nav > div:last-child > div:first-child {
        display: none !important;
    }

    /* Override Bootstrap/Laravel flex classes to center everything */
    .pagination-container .justify-content-between {
        justify-content: center !important;
    }
    
    .pagination-container .d-sm-flex {
        display: flex !important;
        justify-content: center !important;
        width: 100% !important;
    }

    .pagination {
        display: flex !important;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .page-item .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        height: 44px;
        padding: 0 14px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        color: #475569;
        text-decoration: none !important;
        font-weight: 700;
        font-size: 0.95rem;
        background: white;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .page-item.active .page-link {
        background: #0ea5e9;
        color: white;
        border-color: #0ea5e9;
        box-shadow: 0 8px 20px -6px rgba(14, 165, 233, 0.5);
        transform: scale(1.05);
    }

    .page-item.disabled .page-link {
        color: #cbd5e1;
        background: #f8fafc;
        border-color: #f1f5f9;
        cursor: not-allowed;
    }

    .page-item:hover:not(.active):not(.disabled) .page-link {
        background: #f8fafc;
        border-color: #0ea5e9;
        color: #0ea5e9;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    /* Mobile view buttons adjustment */
    .d-sm-none .pagination {
        width: 100%;
        justify-content: center;
        gap: 12px;
    }

    .d-sm-none .page-link {
        flex: 1;
        max-width: 120px;
    }

    /* Media Query for Listing Rows */
    @media (max-width: 600px) {
        #listing-container > div > div {
            gap: 15px !important;
            padding: 20px 0 !important;
        }
        #listing-container div[style*="width: 160px"] {
            width: 100px !important;
            height: 100px !important;
            border-radius: 12px !important;
        }
        #listing-container h2 {
            font-size: 1.1rem !important;
        }
        #listing-container p {
            font-size: 0.85rem !important;
            -webkit-line-clamp: 3 !important;
        }
        #listing-container div[style*="gap: 20px"] {
            gap: 10px !important;
            font-size: 0.75rem !important;
            flex-wrap: wrap !important;
        }
        
        .pagination {
            gap: 4px;
        }
        .page-item .page-link {
            min-width: 38px;
            height: 38px;
            padding: 0 8px;
            border-radius: 10px;
            font-size: 0.85rem;
        }
    }
</style>
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        const listingContainer = document.getElementById('listing-container');
        let timeout = null;

        function performSearch() {
            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData);
            const q = searchInput.value;
            
            // Only search if empty (reset) or length >= 3
            if (q.length > 0 && q.length < 3) return;

            const url = new URL(searchForm.action);
            // Reconstruct URL with all form parameters
            formData.forEach((value, key) => {
                if (value) url.searchParams.set(key, value);
            });

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
@endsection
