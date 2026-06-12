@extends('layouts.app')

@section('title', 'Cari Berdasarkan Tagar - ' . config('app.name'))

@section('content')
<!-- Include Tagify -->
<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>

<style>
    .tagify {
        width: 100%;
        padding: 8px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        transition: border-color 0.2s;
        font-family: inherit;
    }
    .tagify:hover {
        border-color: #cbd5e1;
    }
    .tagify--focus {
        border-color: #0ea5e9;
    }
    .tagify__tag {
        background-color: #f1f5f9;
        border-radius: 6px;
    }
    .tagify__tag > div {
        color: #1e293b;
        font-weight: 600;
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
    <div style="margin-bottom: 30px;">
        <h2 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Cari dengan Tagar</h2>
        <p style="color: #64748b; margin-bottom: 20px;">Ketik dan pilih satu atau lebih tagar untuk menampilkan iklan yang sesuai.</p>
        
        <input name="tags" id="tagsInput" placeholder="Ketik nama tagar (misal: kost, sewa mobil)...">
    </div>

    <!-- Container for Listings -->
    <div id="listing-container" style="transition: opacity 0.2s;">
        <!-- Akan diisi otomatis via AJAX -->
        <div style="text-align: center; color: #94a3b8; padding: 40px 0;">
            Silakan pilih tagar di atas untuk melihat iklan.
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('tagsInput');
        const listingContainer = document.getElementById('listing-container');
        let tagify;

        // Fetch all tags for whitelist
        fetch('/tagar', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            const whitelist = data.categories.map(tag => ({
                value: tag.name,
                slug: tag.slug
            }));

            tagify = new Tagify(input, {
                whitelist: whitelist,
                enforceWhitelist: true,
                dropdown: {
                    maxItems: 20,
                    classname: "tags-look",
                    enabled: 0,
                    closeOnSelect: false
                }
            });

            tagify.on('add', fetchListings);
            tagify.on('remove', fetchListings);

            // Check if there are initial tags in URL
            const urlParams = new URLSearchParams(window.location.search);
            const urlTags = urlParams.getAll('tags[]');
            if (urlTags.length > 0) {
                const initialTags = urlTags.map(slug => whitelist.find(w => w.slug === slug)).filter(Boolean);
                if (initialTags.length > 0) {
                    // tagify.addTags will trigger the 'add' event and fetchListings automatically
                    tagify.addTags(initialTags);
                }
            }
        });

        function fetchListings() {
            const selectedTags = tagify.value.map(t => t.slug);
            
            if (selectedTags.length === 0) {
                listingContainer.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 40px 0;">Silakan pilih tagar di atas untuk melihat iklan.</div>';
                window.history.replaceState(null, '', '/tagar');
                return;
            }

            listingContainer.style.opacity = '0.5';

            const params = new URLSearchParams();
            selectedTags.forEach(tag => params.append('tags[]', tag));

            const url = '/?' + params.toString();

            fetch(url, {
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
                    listingContainer.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 40px 0;">Tidak ada iklan yang cocok dengan tagar tersebut.</div>';
                }
                listingContainer.style.opacity = '1';
                
                // Update URL parameter so user can copy the link
                window.history.replaceState(null, '', '/tagar?' + params.toString());
            })
            .catch(err => {
                console.error('Error fetching listings:', err);
                listingContainer.style.opacity = '1';
            });
        }
    });
</script>
@endsection
