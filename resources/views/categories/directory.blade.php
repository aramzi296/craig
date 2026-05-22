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
            <input type="text" id="categorySearch" placeholder="Cari kategori atau subkategori..." style="flex: 1; padding: 8px 0; border: none; outline: none; font-size: 1rem; font-weight: 500; color: #1e293b; background: transparent;">
        </div>
    </div>

    <!-- Categories Grid -->
    <div id="directoryGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 30px;">
        @foreach($categories as $category)
            <div class="category-card" data-name="{{ strtolower($category->name) }}" data-subs="{{ strtolower($category->children->pluck('name')->implode(' ')) }}" style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.04); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between;">
                <!-- Decorative Top Gradient Ring -->
                <div style="position: absolute; top: -50px; right: -50px; width: 120px; height: 120px; border-radius: 50%; background: radial-gradient(circle, rgba(14,165,233,0.08) 0%, rgba(255,255,255,0) 70%); pointer-events: none;"></div>

                <div>
                    <!-- Category Header -->
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                        <div style="width: 50px; height: 50px; border-radius: 16px; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); display: flex; align-items: center; justify-content: center; color: #0284c7; font-size: 1.35rem; font-weight: 700; box-shadow: 0 8px 16px -4px rgba(14,165,233,0.15);">
                            <i class="fa-solid fa-{{ $category->icon ?: 'folder' }}"></i>
                        </div>
                        <div>
                            <h3 class="category-title" style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;">
                                <a href="{{ route('home', ['category' => $category->slug]) }}" style="color: inherit; text-decoration: none; transition: color 0.2s;">
                                    {{ $category->name }}
                                </a>
                            </h3>
                        </div>
                    </div>

                    <!-- Subcategories List -->
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                        @foreach($category->children as $sub)
                            <a href="{{ route('home', ['category' => $sub->slug]) }}" class="subcategory-link" data-name="{{ strtolower($sub->name) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 14px; background: #f8fafc; color: #475569; text-decoration: none; font-size: 0.92rem; font-weight: 600; transition: all 0.2s; border: 1px solid #f1f5f9;">
                                <span style="display: flex; align-items: center; gap: 8px;">
                                    <span style="color: #0ea5e9; font-weight: 400; font-size: 0.8rem;">#</span>
                                    <span class="sub-name">{{ $sub->name }}</span>
                                </span>
                                @if($sub->listings_count > 0)
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; min-width: 20px; text-align: center;">
                                        {{ $sub->listings_count }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
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
                const subs = card.getAttribute('data-subs');
                const subLinks = card.querySelectorAll('.subcategory-link');
                let cardHasVisibleSub = false;

                // Check subcategories inside this card
                subLinks.forEach(link => {
                    const subName = link.getAttribute('data-name');
                    const span = link.querySelector('.sub-name');

                    if (query === '' || subName.includes(query)) {
                        link.style.display = 'flex';
                        cardHasVisibleSub = true;

                        // Highlight match in subcategory name
                        if (query !== '') {
                            const originalText = span.textContent;
                            const idx = originalText.toLowerCase().indexOf(query);
                            if (idx >= 0) {
                                const before = originalText.substring(0, idx);
                                const match = originalText.substring(idx, idx + query.length);
                                const after = originalText.substring(idx + query.length);
                                span.innerHTML = `${before}<mark style="background: rgba(14, 165, 233, 0.15); color: #0284c7; padding: 0 2px; border-radius: 4px; font-weight: 700;">${match}</mark>${after}`;
                            }
                        } else {
                            span.innerHTML = span.textContent;
                        }
                    } else {
                        link.style.display = 'none';
                    }
                });

                // Show card if parent matches or at least one child matches
                if (query === '' || catName.includes(query) || cardHasVisibleSub) {
                    card.style.display = 'flex';
                    visibleCards++;

                    // If parent matches but no subcategories matched, show all subcategories
                    if (catName.includes(query) && !cardHasVisibleSub) {
                        subLinks.forEach(link => {
                            link.style.display = 'flex';
                            const span = link.querySelector('.sub-name');
                            span.innerHTML = span.textContent; // reset
                        });
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
    .category-card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 25px 50px -12px rgba(14, 165, 233, 0.08) !important;
        border-color: rgba(14, 165, 233, 0.25) !important;
    }
    
    .category-card:hover h3 a {
        color: #0284c7 !important;
    }

    .subcategory-link:hover {
        background: linear-gradient(135deg, #e0f2fe 0%, #f0f9ff 100%) !important;
        color: #0369a1 !important;
        border-color: #bae6fd !important;
        transform: translateX(4px);
    }
</style>
@endsection
