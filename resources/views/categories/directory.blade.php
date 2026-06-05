@extends('layouts.app')

@section('title', 'Kategori Usaha Sebatam - ' . config('app.name'))

@section('content')
<div class="container page-section" style="padding-top: 50px; padding-bottom: 80px;">
    <!-- Section Header -->
    <div style="text-align: center; margin-bottom: 50px;">
        <h1 style="font-size: 2.8rem; font-weight: 800; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.02em;">
            Kategori Usaha <span style="background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Sebatam</span>
        </h1>
        <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.6;">
            Temukan barang, jasa, dan peluang terbaik di Batam berdasarkan kategori terstruktur yang Anda butuhkan.
        </p>
    </div>

    <!-- Live Search Bar -->
    <div style="max-width: 500px; margin: 0 auto 50px; position: relative; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border-radius: 20px; background: white; padding: 4px; border: 1px solid #e2e8f0;">
        <div style="display: flex; align-items: center; padding: 8px 16px;">
            <i class="fa-solid fa-magnifying-glass" style="color: #0ea5e9; font-size: 1.2rem; margin-right: 12px;"></i>
            <input type="text" id="categorySearch" placeholder="Cari kategori..." style="flex: 1; padding: 8px 0; border: none; outline: none; font-size: 1rem; font-weight: 500; color: #1e293b; background: transparent;">
        </div>
    </div>

    <!-- Categories Grid -->
    <div id="directoryGrid">
        @foreach($categories as $category)
            <div class="category-card" data-name="{{ strtolower($category->name) }}">
                <div class="category-title" style="font-size: 1rem; font-weight: 500; color: #0f172a; margin: 0; line-height: 1.4; width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <a href="{{ route('home', ['category' => $category->slug]) }}" style="color: inherit; text-decoration: none; transition: color 0.2s; display: block; width: 100%;">
                        <span class="category-name-text">{{ $category->name }}</span>
                        <span style="color: #64748b; font-weight: 500; font-size: 0.9rem; margin-left: 4px;">({{ $category->listings_count }})</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- No Results State -->
    <div id="noResults" style="display: none; text-align: center; padding: 80px 20px;">
        <div style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;">
            <i class="fa-solid fa-circle-exclamation" style="background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
        </div>
        <h3 style="font-size: 1.4rem; font-weight: 700; color: #1e293b; margin-bottom: 10px;">Kategori tidak ditemukan</h3>
        <p style="color: #64748b; max-width: 400px; margin: 0 auto;">Coba ketik kata kunci pencarian lain untuk mencari kategori yang sesuai.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('categorySearch');
        const cards = document.querySelectorAll('.category-card');
        const noResults = document.getElementById('noResults');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let visibleCards = 0;

            cards.forEach(card => {
                const catName = card.getAttribute('data-name');
                const nameSpan = card.querySelector('.category-name-text');

                if (query === '' || catName.includes(query)) {
                    card.style.display = 'flex';
                    visibleCards++;

                    // Highlight match in category name
                    if (query !== '') {
                        const originalText = nameSpan.textContent.trim();
                        const idx = originalText.toLowerCase().indexOf(query);
                        if (idx >= 0) {
                            const before = originalText.substring(0, idx);
                            const match = originalText.substring(idx, idx + query.length);
                            const after = originalText.substring(idx + query.length);
                            nameSpan.innerHTML = `${before}<mark style="background: rgba(14, 165, 233, 0.15); color: #0284c7; padding: 0 2px; border-radius: 4px; font-weight: 700;">${match}</mark>${after}`;
                        }
                    } else {
                        nameSpan.innerHTML = nameSpan.textContent;
                    }
                } else {
                    card.style.display = 'none';
                }
            });

            noResults.style.display = visibleCards > 0 ? 'none' : 'block';
        });
    });
</script>

<style>
    #directoryGrid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }

    @media (min-width: 768px) {
        #directoryGrid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        #directoryGrid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .category-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        transition: all 0.2s ease-in-out;
        display: flex;
        align-items: center;
    }

    .category-card:hover {
        border-color: #0ea5e9 !important;
        background-color: #f0f9ff !important;
    }
    
    .category-card:hover .category-title a {
        color: #0284c7 !important;
    }
</style>
@endsection
