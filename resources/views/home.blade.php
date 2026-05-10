@extends('layouts.app')

@section('content')
<style>
    /* Grid Layout Styles */
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
        padding-bottom: 100%; /* 1:1 Aspect Ratio */
        background: #f8fafc;
    }

    .grid-image-wrapper img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .grid-content {
        padding: 12px 8px;
    }

    .grid-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.4;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 2.8em;
    }

    @media (max-width: 576px) {
        .listing-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .grid-title {
            font-size: 0.75rem;
        }
        .price-current {
            font-size: 1rem;
        }
    }
</style>
<section class="search-header" style="background: #ffffff; padding: 40px 0; border-bottom: 1px solid #f1f5f9; margin-bottom: 20px;">
    <div class="container" style="max-width: 800px;">
        <form action="{{ route('home') }}" method="GET" class="search-box" style="box-shadow: 0 10px 30px -5px rgba(0,0,0,0.08); border-radius: 20px; border: 1px solid #e2e8f0; overflow: hidden; display: flex;">
            <input type="text" name="q" placeholder="Cari apa saja di Batam... (Contoh: Tukang AC, Kos-kosan)" value="{{ request('q') }}" style="flex: 1; border: none; padding: 15px 25px; font-size: 1rem; font-weight: 500; outline: none;">
            <button type="submit" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white; border: none; padding: 0 40px; font-weight: 800; text-transform: uppercase; cursor: pointer; transition: all 0.3s ease;">CARI</button>
        </form>
    </div>
</section>


<div class="container page-section" style="padding-top: 0;">


    @if(request('q'))
        <h2 class="section-title">
            Hasil Pencarian: "{{ request('q') }}"
            <span style="font-size: 0.9rem; color: #94a3b8; font-weight: 600; margin-left: 10px;">{{ $recentListings->total() }} ditemukan</span>
        </h2>
    @endif
    <div class="listing-grid">
        @foreach($recentListings as $listing)
        <a href="{{ route('listings.show', $listing->slug) }}" class="listing-card-grid">
            <div class="grid-image-wrapper">
                @php $profilePhoto = $listing->user->getProfilePhoto(); @endphp
                <img src="{{ $profilePhoto }}" alt="{{ $listing->title }}">
            </div>

            <div class="grid-content">
                <h3 class="grid-title">{{ $listing->title }}</h3>
            </div>
        </a>
        @endforeach
    </div>
    
    <div style="margin-top: 60px; display: flex; justify-content: center;">
        {{ $recentListings->appends(request()->query())->links('vendor.pagination.simple-custom') }}
    </div>
</div>
@endsection
