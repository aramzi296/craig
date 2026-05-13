@extends('layouts.app')

@section('title', '#Hashtag - ' . config('app.name'))

@section('content')
<div class="container page-section" style="padding-top: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
        <h2 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0;">#Hashtag</h2>
        <div class="search-box" style="box-shadow: none; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; width: 350px;">
            <input type="text" id="categorySearch" placeholder="Cari hashtag..." style="flex: 1; padding: 10px 15px; border: none; outline: none;">
        </div>
    </div>

    <div id="categoriesContainer" style="display: flex; flex-wrap: wrap; gap: 10px;">
        @foreach($categories as $category)
            <a href="{{ route('home', ['category' => $category->slug]) }}" class="category-item" data-name="{{ strtolower($category->name) }}" style="text-decoration: none; color: #4b5563; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; display: flex; align-items: center; gap: 5px; background: #f1f5f9; padding: 8px 16px; border-radius: 50px; border: 1px solid #e2e8f0;">
                <span style="color: #64748b; font-weight: 400;">#</span>
                <span class="name-span">{{ $category->name }}</span>
            </a>
        @endforeach
    </div>

    <!-- No Results Found -->
    <div id="noResults" style="display: none; text-align: center; padding: 60px 20px;">
        <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;">
            <i class="fa-solid fa-hashtag"></i>
        </div>
        <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 8px;">Hashtag tidak ditemukan</h3>
        <p style="color: #64748b;">Coba kata kunci lain atau periksa ejaan Anda.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('categorySearch');
        const categoryItems = document.querySelectorAll('.category-item');
        const noResults = document.getElementById('noResults');

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let totalVisible = 0;

            categoryItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const span = item.querySelector('.name-span');
                
                if (query === '' || name.includes(query)) {
                    item.style.display = 'flex';
                    totalVisible++;

                    // Reset/Apply highlights
                    if (query !== '') {
                        const originalText = span.textContent;
                        const index = originalText.toLowerCase().indexOf(query);
                        if (index >= 0) {
                            const before = originalText.substring(0, index);
                            const match = originalText.substring(index, index + query.length);
                            const after = originalText.substring(index + query.length);
                            span.innerHTML = `${before}<span style="background: rgba(14, 165, 233, 0.2); color: #0ea5e9; font-weight: 700; border-radius: 2px;">${match}</span>${after}`;
                        }
                    } else {
                        span.innerHTML = span.textContent;
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            noResults.style.display = totalVisible > 0 ? 'none' : 'block';
        });
    });
</script>

<style>
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
    @media (max-width: 768px) {
        .search-box { width: 100% !important; margin-top: 15px; }
        .container.page-section > div:first-child { flex-direction: column; align-items: flex-start !important; }
    }
</style>
@endsection
