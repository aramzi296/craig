@extends('layouts.app')

@section('title', 'Cari Berdasarkan Tagar - ' . config('app.name'))

@section('content')
<style>
    .hashtag-item {
        display: inline-block;
        background: #f1f5f9;
        color: #0ea5e9;
        padding: 6px 16px;
        border-radius: 20px;
        text-decoration: none;
        font-weight: 600;
        margin: 5px 8px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .hashtag-item:hover {
        background: #e0f2fe;
        border-color: #7dd3fc;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        text-decoration: none;
    }
    .search-input {
        width: 100%;
        max-width: 600px;
        padding: 12px 20px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.2s;
        margin-bottom: 20px;
    }
    .search-input:focus {
        border-color: #0ea5e9;
    }

    /* Grid Layout Styles (Same as home) */
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
        padding-bottom: 100%;
        background: #f8fafc;
    }
    .grid-image-wrapper img {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;
    }
    .grid-content {
        padding: 12px 8px;
    }
    .grid-title {
        font-size: 0.9rem; font-weight: 700; color: #1e293b;
        line-height: 1.4; margin: 0;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
        overflow: hidden; height: 2.8em;
    }
    @media (max-width: 576px) {
        .listing-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .grid-title {
            font-size: 0.75rem;
        }
    }
</style>

<div class="container page-section" style="padding-top: 40px; min-height: 60vh;">
    <div style="margin-bottom: 30px; text-align: center;">
        <h2 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Cari dengan Tagar</h2>
        <p style="color: #64748b; margin-bottom: 20px;">Ketik untuk mencari tagar, atau pilih dari tagar acak di bawah ini.</p>
        
        <input type="text" id="tagsInput" class="search-input" placeholder="Ketik nama tagar (misal: kost, sewa mobil)...">
    </div>

    <!-- Container for Tags -->
    <div id="tags-container" style="text-align: center; transition: opacity 0.2s; max-height: 250px; overflow-y: auto; padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fafafa;">
        @foreach($categories as $tag)
            <a href="{{ route('categories.index', ['tag' => $tag->slug]) }}" class="hashtag-item">#{{ $tag->name }}</a>
        @endforeach
    </div>

    <!-- Container for Listings -->
    <div id="listing-container" style="margin-top: 40px; transition: opacity 0.2s;">
        <!-- Akan diisi otomatis via AJAX saat tagar diklik -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('tagsInput');
        const tagsContainer = document.getElementById('tags-container');
        const listingContainer = document.getElementById('listing-container');

        let timeout = null;

        // Load tag from URL if present
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('tag')) {
            loadListings(window.location.href);
        }

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const query = input.value.trim();
                
                tagsContainer.style.opacity = '0.5';

                fetch(`/tagar?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    tagsContainer.innerHTML = '';
                    if (data.categories && data.categories.length > 0) {
                        data.categories.forEach(tag => {
                            const a = document.createElement('a');
                            a.href = `/tagar?tag=${encodeURIComponent(tag.slug)}`;
                            a.className = 'hashtag-item';
                            a.textContent = `#${tag.name}`;
                            tagsContainer.appendChild(a);
                        });
                    } else {
                        tagsContainer.innerHTML = '<div style="color: #94a3b8; padding: 20px;">Tidak ada tagar yang cocok.</div>';
                    }
                    tagsContainer.style.opacity = '1';
                })
                .catch(err => {
                    console.error('Error fetching tags:', err);
                    tagsContainer.style.opacity = '1';
                });
            }, 300);
        });

        function loadListings(targetUrl) {
            const urlObj = new URL(targetUrl, window.location.origin);
            let fetchUrl = urlObj.href;
            let pushUrl = urlObj.href;

            if (urlObj.pathname === '/tagar') {
                const fetchUrlObj = new URL(urlObj.href);
                fetchUrlObj.pathname = '/';
                fetchUrl = fetchUrlObj.href;
            } else if (urlObj.pathname === '/') {
                const pushUrlObj = new URL(urlObj.href);
                pushUrlObj.pathname = '/tagar';
                pushUrl = pushUrlObj.href;
            }

            listingContainer.style.opacity = '0.5';
            
            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.getElementById('listing-container');
                
                if (newContent) {
                    listingContainer.innerHTML = newContent.innerHTML;
                } else {
                    listingContainer.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 40px 0;">Tidak ada iklan ditemukan.</div>';
                }
                listingContainer.style.opacity = '1';
                
                // Update URL parameter
                window.history.pushState(null, '', pushUrl);
            })
            .catch(err => {
                console.error('Error fetching listings:', err);
                listingContainer.style.opacity = '1';
            });
        }

        // Event delegation
        document.addEventListener('click', function(e) {
            // Click on a hashtag
            const hashtagLink = e.target.closest('a.hashtag-item');
            if (hashtagLink) {
                e.preventDefault();
                loadListings(hashtagLink.href);
                return;
            }

            // Click on pagination inside listing container
            const listingLink = e.target.closest('#listing-container a');
            if (listingLink && !listingLink.classList.contains('listing-card-grid')) {
                e.preventDefault();
                loadListings(listingLink.href);
            }
        });

        // Handle browser back button
        window.addEventListener('popstate', function() {
            window.location.reload();
        });
    });
</script>
@endsection
