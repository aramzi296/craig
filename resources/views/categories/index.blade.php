@extends('layouts.app')

@section('title', 'Daftar Kategori - BatamCraig')

@section('content')
<section class="hero" style="background: linear-gradient(rgba(219, 234, 254, 0.6), rgba(219, 234, 254, 0.6)), url('{{ asset('batam-hero.jpg') }}') no-repeat center center; background-size: cover; border-bottom: 1px solid #e5e7eb;">
    <div class="container" style="max-width: 800px;">
        <h2 style="font-size: 3rem; font-weight: 800; margin-bottom: 12px; color: #111827; text-shadow: 0 2px 4px rgba(255,255,255,0.5); letter-spacing: -0.02em;">Semua Kategori</h2>
        <p style="color: #374151; font-size: 1.3rem; margin-bottom: 40px; font-weight: 500;">Temukan layanan dan produk berdasarkan kategori yang Anda butuhkan.</p>

        <!-- Search Bar inside Hero -->
        <div class="search-box" style="box-shadow: 0 4px 20px -2px rgba(0,0,0,0.1);">
            <input type="text" id="categorySearch" placeholder="Cari kategori (misal: Properti, Jasa, Elektronik...)" style="flex: 1;">
            <div id="searchBadge" style="display: none; align-items: center; background: var(--primary); color: white; padding: 0 15px; font-size: 0.8rem; font-weight: 600; white-space: nowrap;">
                <span id="matchCount" style="margin-right: 4px;">0</span> ditemukan
            </div>
            <button type="button" style="cursor: default;">CARI</button>
        </div>
    </div>
</section>

<div class="container page-section" style="padding-top: 30px;">

    <div id="categoriesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @foreach($groupedCategories as $letter => $categories)
            <div id="letter-{{ $letter }}" class="category-group" style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #f1f5f9; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <div class="group-header" style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                    <span style="background: var(--primary-light); width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">{{ $letter }}</span>
                    <span>Kategori</span>
                </div>
                <div class="category-list" style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($categories as $category)
                        <a href="{{ route('home', ['category' => $category->slug]) }}" class="category-item" data-name="{{ strtolower($category->name) }}" style="text-decoration: none; color: #4b5563; font-weight: 500; font-size: 0.95rem; transition: all 0.2s; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem; color: #94a3b8;"></i>
                            <span class="name-span">{{ $category->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <!-- No Results Found -->
    <div id="noResults" style="display: none; text-align: center; padding: 60px 20px;">
        <div style="font-size: 4rem; color: var(--border); margin-bottom: 20px;">
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 10px;">Tidak ditemukan kategori</h3>
        <p style="color: var(--text-muted);">Coba kata kunci lain atau periksa ejaan Anda.</p>
        <button onclick="document.getElementById('categorySearch').value = ''; document.getElementById('categorySearch').dispatchEvent(new Event('input'));" class="btn btn-outline" style="margin-top: 20px;">Reset Pencarian</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('categorySearch');
        const categoryItems = document.querySelectorAll('.category-item');
        const categoryGroups = document.querySelectorAll('.category-group');
        const noResults = document.getElementById('noResults');
        const searchBadge = document.getElementById('searchBadge');
        const matchCount = document.getElementById('matchCount');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let totalVisible = 0;

            if (query === '') {
                // Show everything
                categoryItems.forEach(item => {
                    item.style.display = 'flex';
                    // Reset highlights
                    const span = item.querySelector('.name-span');
                    span.innerHTML = span.textContent;
                });
                categoryGroups.forEach(group => group.style.display = 'block');
                noResults.style.display = 'none';
                searchBadge.style.display = 'none';
                return;
            }

            categoryGroups.forEach(group => {
                const itemsInGroup = group.querySelectorAll('.category-item');
                let visibleInGroup = 0;

                itemsInGroup.forEach(item => {
                    const name = item.getAttribute('data-name');
                    const span = item.querySelector('.name-span');
                    
                    if (name.includes(query)) {
                        item.style.display = 'flex';
                        visibleInGroup++;
                        totalVisible++;

                        // Highlight match
                        const originalText = span.textContent;
                        const index = originalText.toLowerCase().indexOf(query);
                        if (index >= 0) {
                            const before = originalText.substring(0, index);
                            const match = originalText.substring(index, index + query.length);
                            const after = originalText.substring(index + query.length);
                            span.innerHTML = `${before}<span style="background: rgba(var(--primary-rgb), 0.2); color: var(--primary); font-weight: 700; border-radius: 2px;">${match}</span>${after}`;
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });

                if (visibleInGroup > 0) {
                    group.style.display = 'block';
                } else {
                    group.style.display = 'none';
                }
            });

            // Update badge and no results
            if (totalVisible > 0) {
                noResults.style.display = 'none';
                searchBadge.style.display = 'flex';
                matchCount.textContent = totalVisible;
            } else {
                noResults.style.display = 'block';
                searchBadge.style.display = 'none';
            }
        });

        // Focus search on '/' key
        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                searchInput.focus();
            }
        });
    });
</script>

<style>
    .category-group {
        transition: transform 0.3s ease, opacity 0.3s ease;
    }
    :root {
        --primary-rgb: 14, 165, 233;
    }
</style>
@endsection
