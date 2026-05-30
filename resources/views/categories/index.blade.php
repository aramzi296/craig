@extends('layouts.app')

@section('title', 'Tagar - ' . config('app.name'))

@section('content')
<div class="container page-section" style="padding-top: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
        <h2 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0;">Tagar</h2>
        <div class="search-box" style="box-shadow: none; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; width: 350px;">
            <input type="text" id="categorySearch" placeholder="Cari tagar..." style="flex: 1; padding: 10px 15px; border: none; outline: none;">
        </div>
    </div>

    <div id="categoriesContainer" style="display: flex; flex-wrap: wrap; gap: 10px;">
        @foreach($categories as $category)
            <a href="{{ route('home', ['tag' => $category->slug]) }}" class="category-item" data-name="{{ strtolower($category->name) }}" style="text-decoration: none; color: #4b5563; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; display: flex; align-items: center; gap: 5px; background: #f1f5f9; padding: 8px 16px; border-radius: 50px; border: 1px solid #e2e8f0;">
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
        <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 8px;">Tagar tidak ditemukan</h3>
        <p style="color: #64748b;">Coba kata kunci lain atau periksa ejaan Anda.</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('categorySearch');
        const noResults = document.getElementById('noResults');
        const container = document.getElementById('categoriesContainer');

        let debounceTimer;

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            debounceTimer = setTimeout(() => {
                fetch(`/tagar?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    container.innerHTML = '';
                    const categories = data.categories;

                    if (categories && categories.length > 0) {
                        noResults.style.display = 'none';
                        categories.forEach(category => {
                            const a = document.createElement('a');
                            // Ensure the dynamic tag has identical styling, hover transitions, and features
                            a.href = `/?tag=${encodeURIComponent(category.slug)}`;
                            a.className = 'category-item';
                            a.setAttribute('data-name', category.name.toLowerCase());
                            a.style.textDecoration = 'none';
                            a.style.color = '#4b5563';
                            a.style.fontWeight = '600';
                            a.style.fontSize = '0.9rem';
                            a.style.transition = 'all 0.2s';
                            a.style.display = 'flex';
                            a.style.alignItems = 'center';
                            a.style.gap = '5px';
                            a.style.background = '#f1f5f9';
                            a.style.padding = '8px 16px';
                            a.style.borderRadius = '50px';
                            a.style.border = '1px solid #e2e8f0';

                            const hashSpan = document.createElement('span');
                            hashSpan.style.color = '#64748b';
                            hashSpan.style.fontWeight = '400';
                            hashSpan.textContent = '#';

                            const nameSpan = document.createElement('span');
                            nameSpan.className = 'name-span';

                            const nameText = category.name;
                            const queryLower = query.toLowerCase();
                            const index = nameText.toLowerCase().indexOf(queryLower);

                            if (query !== '' && index >= 0) {
                                const before = nameText.substring(0, index);
                                const match = nameText.substring(index, index + query.length);
                                const after = nameText.substring(index + query.length);
                                nameSpan.innerHTML = `${escapeHtml(before)}<span style="background: rgba(14, 165, 233, 0.2); color: #0ea5e9; font-weight: 700; border-radius: 2px;">${escapeHtml(match)}</span>${escapeHtml(after)}`;
                            } else {
                                nameSpan.textContent = nameText;
                            }

                            a.appendChild(hashSpan);
                            a.appendChild(nameSpan);
                            container.appendChild(a);
                        });
                    } else {
                        noResults.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error fetching tags:', error);
                });
            }, 250);
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
