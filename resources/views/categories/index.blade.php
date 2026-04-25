@extends('layouts.app')

@section('title', 'Daftar Kategori - BatamCraig')

@section('content')
<section class="hero" style="background-image: url('{{ asset('gelombang.png') }}');">
    <div class="container">
        <h1 class="hidden md:block">Semua Kategori</h1>
        <p class="hidden md:block">Cari berdasarkan kategori yang Anda butuhkan. Semua listing dikelompokkan dengan rapi untuk memudahkan pencarian Anda.</p>

        <!-- Search Bar inside Hero -->
        <div style="max-width: 600px; margin: 0 auto;">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="categorySearch" placeholder="Cari kategori (misal: Properti, Jasa, Elektronik...)">
                <div id="searchBadge" style="display: none; align-items: center; background: var(--primary); color: white; padding: 6px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; white-space: nowrap; margin-right: 4px;">
                    <span id="matchCount" style="margin-right: 4px;">0</span> ditemukan
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container page-section">

    <div id="categoriesContainer" style="display: flex; flex-direction: column; gap: 50px;">
        @foreach($groupedCategories as $letter => $categories)
            <div id="letter-{{ $letter }}" class="category-group" style="padding-bottom: 10px;">
                <div class="group-header" style="font-size: 2.2rem; font-weight: 800; color: #38bdf8; margin-bottom: 5px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">
                    {{ $letter }}
                </div>
                <div class="category-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px 20px; padding-top: 15px;">
                    @foreach($categories as $category)
                        <a href="{{ route('home', ['category' => $category->slug]) }}" class="category-item" data-name="{{ strtolower($category->name) }}" style="text-decoration: underline; color: #166534; font-weight: 600; font-size: 1rem; transition: color 0.2s;" onmouseover="this.style.color='#15803d'" onmouseout="this.style.color='#166534'">
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
