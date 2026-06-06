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
    <div class="search-container">
        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="categorySearch" placeholder="Cari kategori atau subkategori..." autocomplete="off">
        </div>
    </div>

    @php
    $iconMap = [
        'Kuliner & Jajanan' => 'utensils',
        'Bengkel Las, Bangunan & Service' => 'screwdriver-wrench',
        'Jasa Bisnis & Legalitas' => 'briefcase',
        'Logistik, Ekspedisi & Kargo' => 'truck',
        'Rental & Transportasi Lokal' => 'car',
        'Lapak Belanja & Toko Lokal' => 'store',
        'Penginapan & Properti' => 'house-chimney',
        'Lowongan Kerja & Karir' => 'user-tie',
        'Salon, Seni & Kecantikan' => 'scissors',
        'Kesehatan & Kebugaran' => 'heart-pulse',
        'Pendidikan & Pelatihan' => 'graduation-cap',
        'Wisata & Hiburan Lokal' => 'umbrella-beach',
        'Usaha Lainnya & Serba-Serbi' => 'cubes'
    ];
    
    // Dynamic soft colors for each parent category to give a premium, colorful feel
    $colorMap = [
        'Kuliner & Jajanan' => ['bg' => '#fef2f2', 'text' => '#dc2626', 'border' => '#fee2e2'],
        'Bengkel Las, Bangunan & Service' => ['bg' => '#fef3c7', 'text' => '#d97706', 'border' => '#fde68a'],
        'Jasa Bisnis & Legalitas' => ['bg' => '#ecfdf5', 'text' => '#059669', 'border' => '#d1fae5'],
        'Logistik, Ekspedisi & Kargo' => ['bg' => '#f0fdf4', 'text' => '#16a34a', 'border' => '#dcfce7'],
        'Rental & Transportasi Lokal' => ['bg' => '#eff6ff', 'text' => '#2563eb', 'border' => '#dbeafe'],
        'Lapak Belanja & Toko Lokal' => ['bg' => '#f5f3ff', 'text' => '#7c3aed', 'border' => '#ddd6fe'],
        'Penginapan & Properti' => ['bg' => '#faf5ff', 'text' => '#9333ea', 'border' => '#f3e8ff'],
        'Lowongan Kerja & Karir' => ['bg' => '#fdf2f8', 'text' => '#db2777', 'border' => '#fbcfe8'],
        'Salon, Seni & Kecantikan' => ['bg' => '#fff1f2', 'text' => '#e11d48', 'border' => '#ffe4e6'],
        'Kesehatan & Kebugaran' => ['bg' => '#f0fdfa', 'text' => '#0d9488', 'border' => '#ccfbf1'],
        'Pendidikan & Pelatihan' => ['bg' => '#f0f9ff', 'text' => '#0284c7', 'border' => '#e0f2fe'],
        'Wisata & Hiburan Lokal' => ['bg' => '#fff7ed', 'text' => '#ea580c', 'border' => '#ffedd5'],
        'Usaha Lainnya & Serba-Serbi' => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0']
    ];
    @endphp

    <!-- Categories Grid -->
    <div id="directoryGrid" class="grid-layout">
        @foreach($categories as $parent)
            @php
            $iconName = $iconMap[$parent->name] ?? $parent->icon ?? 'folder';
            $colors = $colorMap[$parent->name] ?? ['bg' => '#eff6ff', 'text' => '#2563eb', 'border' => '#dbeafe'];
            @endphp
            <div class="parent-card" data-name="{{ strtolower($parent->name) }}">
                <!-- Card Header -->
                <div class="parent-header">
                    <div class="parent-icon-wrapper" style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; border: 1px solid {{ $colors['border'] }}">
                        <i class="fa-solid fa-{{ $iconName }}"></i>
                    </div>
                    <div class="parent-title-container">
                        <h2 class="parent-title">
                            <a href="{{ route('home', ['category' => $parent->slug]) }}" class="parent-link">
                                <span class="parent-name-text">{{ $parent->name }}</span>
                            </a>
                        </h2>
                        <span class="parent-listings-badge">({{ $parent->listings_count }}) Listing</span>
                    </div>
                </div>

                <!-- Card Body (Subcategories) -->
                <div class="subcategory-list">
                    @foreach($parent->children as $child)
                        <div class="subcategory-item" data-name="{{ strtolower($child->name) }}">
                            <a href="{{ route('home', ['category' => $child->slug]) }}" class="subcategory-link">
                                <div class="subcategory-info">
                                    <i class="fa-solid fa-hashtag subcat-hashtag"></i>
                                    <span class="subcat-name-text">{{ $child->name }}</span>
                                </div>
                                <span class="subcat-count-badge">({{ $child->listings_count }})</span>
                            </a>
                        </div>
                    @endforeach
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

<style>
    /* Search Bar Stylings */
    .search-container {
        max-width: 600px;
        margin: 0 auto 50px;
        position: relative;
    }
    
    .search-wrapper {
        display: flex;
        align-items: center;
        padding: 6px 18px;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
        border-radius: 20px;
        background: white;
        border: 1px solid #e2e8f0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .search-wrapper:focus-within {
        border-color: #0ea5e9;
        box-shadow: 0 10px 30px -5px rgba(14, 165, 233, 0.15);
        transform: translateY(-2px);
    }
    
    .search-icon {
        color: #0ea5e9;
        font-size: 1.2rem;
        margin-right: 14px;
        transition: transform 0.3s ease;
    }
    
    .search-wrapper:focus-within .search-icon {
        transform: scale(1.1);
    }
    
    #categorySearch {
        flex: 1;
        padding: 10px 0;
        border: none;
        outline: none;
        font-size: 1rem;
        font-weight: 600;
        color: #1e293b;
        background: transparent;
    }
    
    #categorySearch::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    /* Grid Layout */
    .grid-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 30px;
    }

    @media (min-width: 768px) {
        .grid-layout {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1140px) {
        .grid-layout {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* Parent Category Card */
    .parent-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        padding: 26px;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -2px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }

    .parent-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #0ea5e9 0%, #2563eb 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .parent-card:hover {
        transform: translateY(-6px);
        border-color: rgba(14, 165, 233, 0.4);
        box-shadow: 0 20px 40px -15px rgba(14, 165, 233, 0.15);
    }

    .parent-card:hover::before {
        opacity: 1;
    }

    /* Parent Header */
    .parent-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f1f5f9;
    }

    .parent-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .parent-card:hover .parent-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    .parent-title-container {
        flex-grow: 1;
        min-width: 0;
    }

    .parent-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px 0;
        line-height: 1.4;
        letter-spacing: -0.01em;
    }

    .parent-link {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .parent-link:hover {
        color: #0ea5e9;
    }

    .parent-listings-badge {
        background: #f1f5f9;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 9999px;
        display: inline-block;
    }

    /* Subcategory List */
    .subcategory-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        flex-grow: 1;
    }

    .subcategory-item {
        transition: all 0.2s ease;
    }

    .subcategory-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        color: #475569;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }

    .subcategory-link:hover {
        background: #f0f9ff;
        border-color: #bae6fd;
        color: #0284c7;
        padding-left: 18px;
    }

    .subcategory-info {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .subcat-hashtag {
        color: #94a3b8;
        font-size: 0.8rem;
        transition: color 0.2s ease;
    }

    .subcategory-link:hover .subcat-hashtag {
        color: #0ea5e9;
    }

    .subcat-name-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .subcat-count-badge {
        font-size: 0.75rem;
        color: #64748b;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 2px 8px;
        border-radius: 8px;
        font-weight: 700;
        transition: all 0.2s ease;
        flex-shrink: 0;
        margin-left: 10px;
    }

    .subcategory-link:hover .subcat-count-badge {
        color: #0284c7;
        border-color: #bae6fd;
        background: #e0f2fe;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('categorySearch');
        const parentCards = document.querySelectorAll('.parent-card');
        const noResults = document.getElementById('noResults');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let visibleParents = 0;

            parentCards.forEach(parentCard => {
                const parentName = parentCard.getAttribute('data-name');
                const parentNameSpan = parentCard.querySelector('.parent-name-text');
                const subcategoryItems = parentCard.querySelectorAll('.subcategory-item');
                
                const parentMatches = query === '' || parentName.includes(query);
                let matchingChildrenCount = 0;

                // Highlight parent matching text
                if (query !== '' && parentName.includes(query)) {
                    highlightText(parentNameSpan, query);
                } else {
                    parentNameSpan.innerHTML = parentNameSpan.textContent;
                }

                subcategoryItems.forEach(item => {
                    const childName = item.getAttribute('data-name');
                    const childNameSpan = item.querySelector('.subcat-name-text');
                    const childMatches = childName.includes(query);

                    if (query === '') {
                        item.style.display = 'block';
                        childNameSpan.innerHTML = childNameSpan.textContent;
                    } else if (childMatches) {
                        item.style.display = 'block';
                        highlightText(childNameSpan, query);
                        matchingChildrenCount++;
                    } else {
                        if (parentMatches) {
                            item.style.display = 'block';
                            childNameSpan.innerHTML = childNameSpan.textContent;
                            matchingChildrenCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });

                if (parentMatches || matchingChildrenCount > 0) {
                    parentCard.style.display = 'flex';
                    visibleParents++;
                } else {
                    parentCard.style.display = 'none';
                }
            });

            noResults.style.display = visibleParents > 0 ? 'none' : 'block';
        });

        function highlightText(element, query) {
            const originalText = element.textContent.trim();
            const idx = originalText.toLowerCase().indexOf(query);
            if (idx >= 0) {
                const before = originalText.substring(0, idx);
                const match = originalText.substring(idx, idx + query.length);
                const after = originalText.substring(idx + query.length);
                element.innerHTML = `${before}<mark style="background: rgba(14, 165, 233, 0.15); color: #0284c7; padding: 0 2px; border-radius: 4px; font-weight: 700;">${match}</mark>${after}`;
            }
        }
    });
</script>
@endsection
