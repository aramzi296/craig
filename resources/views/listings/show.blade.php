@extends('layouts.app')

@section('content')
<div class="container listing-detail-container" style="padding-top: 20px;">
    <div style="max-width: 800px; margin-left: 0;">
        <!-- Breadcrumbs -->
        <nav style="margin-bottom: 20px; color: #64748b; font-size: 0.85rem; font-weight: 500;">
            <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none;">Beranda</a> 
            <span style="margin: 0 8px; opacity: 0.5;">/</span>
            <a href="{{ route('home', ['category' => $listing->approvedTags->first()->slug ?? 'lainnya']) }}" style="color: #64748b; text-decoration: none;">{{ $listing->approvedTags->first()->name ?? '#Lainnya' }}</a> 
            <span style="margin: 0 8px; opacity: 0.5;">/</span>
            <span style="color: #1e293b; font-weight: 700;">{{ $listing->title }}</span>
        </nav>

    <div>
        <div>
            <!-- 1. Judul (Title - Aligned Version) -->
            <div style="padding: 10px 0; margin-bottom: 5px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap;">
                    @if($listing->is_premium)
                        <span style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 2px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800;">PREMIUM</span>
                    @endif
                </div>
                <h1 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; line-height: 1.2; margin: 0;">{{ $listing->title }}</h1>
                <div style="text-align: left; color: #94a3b8; font-size: 0.85rem; font-weight: 600; margin-top: 8px;">
                    Update {{ $listing->updated_at->diffForHumans() }}
                </div>
            </div>

            @if($listing->photos->count() > 0)
            <!-- Layout Galeri & Lightbox -->
            <div style="padding: 15px 0; margin-bottom: 10px;">
                <!-- Galeri Thumbnail Saja -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px;">
                    @foreach($listing->photos as $index => $photo)
                    <div style="aspect-ratio: 1/1; border-radius: 8px; overflow: hidden; cursor: pointer; border: 1px solid #f1f5f9; transition: all 0.2s;" 
                         onclick="openLightbox({{ $index }})"
                         onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='scale(1.05)'" 
                         onmouseout="this.style.borderColor='#f1f5f9'; this.style.transform='scale(1)'">
                        <img src="{{ $photo->getThumbnailUrl() }}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Keterangan (Description) di bawah foto -->
            <div style="background: white; padding: 0 0 25px 0; margin-bottom: 15px;">
                <div style="line-height: 1.7; color: #475569; font-size: 1.05rem; white-space: pre-line;">{!! e($listing->description) !!}</div>
            </div>

            @if($listing->photos->count() > 0)
            <!-- Lightbox Modal Modern (Script remains same) -->
            <div id="lightbox" style="display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); justify-content: center; align-items: center; padding: 20px; user-select: none;">
                <span style="position: absolute; top: 20px; right: 30px; color: white; font-size: 3rem; font-weight: 300; cursor: pointer; line-height: 1; z-index: 10001;" onclick="closeLightbox()">&times;</span>
                <button onclick="prevImage()" style="position: absolute; left: 20px; background: rgba(255,255,255,0.1); color: white; border: none; padding: 20px 15px; border-radius: 8px; cursor: pointer; transition: 0.3s; z-index: 10001; font-size: 1.5rem;">&lt;</button>
                <button onclick="nextImage()" style="position: absolute; right: 20px; background: rgba(255,255,255,0.1); color: white; border: none; padding: 20px 15px; border-radius: 8px; cursor: pointer; transition: 0.3s; z-index: 10001; font-size: 1.5rem;">&gt;</button>
                <div style="max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center; gap: 15px;">
                    <img id="lightbox-img" style="max-width: 100%; max-height: 85vh; border-radius: 8px; box-shadow: 0 0 50px rgba(0,0,0,0.5);">
                    <div id="lightbox-counter" style="color: rgba(255,255,255,0.6); font-size: 0.8rem; background: rgba(255,255,255,0.1); padding: 4px 15px; border-radius: 20px;"></div>
                </div>
            </div>

            <script>
                const galleryImages = @json($listing->photos->map(fn($p) => $p->getUrl()));
                let currentIndex = 0;
                function openLightbox(index) { currentIndex = index; updateLightboxContent(); document.getElementById('lightbox').style.display = 'flex'; document.body.style.overflow = 'hidden'; }
                function closeLightbox() { document.getElementById('lightbox').style.display = 'none'; document.body.style.overflow = 'auto'; }
                function updateLightboxContent() { const img = document.getElementById('lightbox-img'); const counter = document.getElementById('lightbox-counter'); img.style.opacity = '0'; setTimeout(() => { img.src = galleryImages[currentIndex]; counter.innerText = `${currentIndex + 1} / ${galleryImages.length}`; img.style.opacity = '1'; }, 150); }
                function nextImage() { currentIndex = (currentIndex + 1) % galleryImages.length; updateLightboxContent(); }
                function prevImage() { currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length; updateLightboxContent(); }
                document.addEventListener('keydown', function(e) { const lightbox = document.getElementById('lightbox'); if (lightbox.style.display === 'flex') { if (e.key === 'ArrowRight') nextImage(); if (e.key === 'ArrowLeft') prevImage(); if (e.key === 'Escape') closeLightbox(); } });
                document.getElementById('lightbox').onclick = function(e) { if (e.target.id === 'lightbox') closeLightbox(); };
            </script>
            @endif
            
            @if($listing->activation_code && !$listing->is_active)
                <div style="background: #fff7ed; border: 1px solid #ffedd5; color: #9a3412; padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; font-weight: 600; opacity: 0.8;">KODE AKTIVASI IKLAN</div>
                        <div style="font-size: 1.2rem; font-weight: 800; letter-spacing: 2px;">{{ $listing->activation_code }}</div>
                    </div>
                    <div style="font-size: 0.8rem; font-weight: 600; background: rgba(255,255,255,0.5); padding: 4px 10px; border-radius: 6px;">BELUM AKTIF</div>
                </div>
            @endif

            <!-- 3. Atribut Penting (Compact List Version) -->
            <div style="padding: 10px 0; margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px;">
                <div style="font-size: 1rem; color: #334155;">
                    <span style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; width: 80px; display: inline-block;">Harga</span>
                    <span style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">
                        @if($listing->price && $listing->price > 0)
                            Rp {{ number_format($listing->price, 0, ',', '.') }}
                        @else
                            Hubungi Kami
                        @endif
                    </span>
                </div>
                
                <div style="font-size: 1rem; color: #334155;">
                    <span style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; width: 80px; display: inline-block;">Lokasi</span>
                    <span style="font-weight: 700;">
                        {{ $listing->district?->name ?? 'Batam' }}
                    </span>
                </div>

                <div style="font-size: 1rem; color: #334155;">
                    <span style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; width: 80px; display: inline-block;">Hashtag</span>
                    <span style="font-weight: 700;">
                        {{ $listing->approvedTags->pluck('name')->map(fn($n) => "#$n")->join(', ') }}
                    </span>
                </div>
            </div>

            <!-- 4. Kontak & User (Interaction - Aligned Version) -->
            <div style="padding: 10px 0; margin-bottom: 30px;">
                <div class="listing-footer-row" style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                        <img src="{{ $listing->user->getProfilePhoto() }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid #f1f5f9;" alt="">
                        <div>
                            <div style="font-weight: 700; font-size: 1rem; color: #1e293b;">
                                <a href="{{ route('user.listings', $listing->user_id) }}" style="color: inherit; text-decoration: none;">{{ $listing->user->name }}</a>
                                @if($listing->user->is_verified) <span style="color: #3b82f6; font-size: 0.75rem; vertical-align: middle;">(Terverifikasi)</span> @endif
                            </div>
                            <div style="font-size: 0.8rem; color: #64748b;">Member sejak {{ $listing->user->created_at->format('M Y') }}</div>
                        </div>
                    </div>
                    </div>
                </div>

                <div class="listing-footer-buttons" style="display: flex; gap: 24px; align-items: center;">
                    @php
                        $canSeeContact = false;
                        if ($listing->whatsapp_visibility == 2) { $canSeeContact = true; } 
                        elseif ($listing->whatsapp_visibility == 1) { $canSeeContact = auth()->check(); }
                    @endphp

                    @if($canSeeContact)
                        <a href="https://wa.me/{{ $listing->user->whatsapp }}?text=Halo {{ $listing->user->name }}, saya tertarik dengan iklan Anda di {{ config('app.name') }}: {{ $listing->title }}." target="_blank" style="color: #25D366; font-weight: 800; text-decoration: none; font-size: 1rem;">
                            Hubungi WhatsApp
                        </a>
                    @elseif($listing->whatsapp_visibility == 1)
                        <a href="{{ route('login') }}" style="color: #0ea5e9; font-weight: 800; text-decoration: none; font-size: 1rem;">
                            Login untuk Chat
                        </a>
                    @else
                        <a href="#contact-form" style="color: #64748b; font-weight: 800; text-decoration: none; font-size: 1rem;">
                            Hubungi via Form
                        </a>
                    @endif
                    
                    @auth
                        <form action="{{ route('listings.favorite', $listing->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; padding: 0; color: {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? '#ef4444' : '#64748b' }}; font-weight: 800; cursor: pointer; font-size: 1rem;">
                                {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'Hapus dari Favorit' : 'Tambah ke Favorit' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" style="color: #64748b; font-weight: 800; text-decoration: none; font-size: 1rem;">
                            Tambah ke Favorit
                        </a>
                    @endauth

                    <a href="https://wa.me/{{ config('services.whatsapp.admin_number_2') }}?text=Halo Admin, saya ingin melaporkan iklan ini karena (sebutkan alasan): {{ route('listings.show', $listing->slug) }}" target="_blank" style="color: #ef4444; font-weight: 800; text-decoration: none; font-size: 1rem;">
                        Laporkan Iklan
                    </a>
                </div>


            </div>

            <!-- Owner Actions -->
            @auth
                @if($listing->user_id == auth()->id())
                <div class="glass" style="padding: 25px; border-radius: var(--radius); margin-bottom: 40px; text-align: left; border: 1px dashed var(--accent); background: rgba(0, 163, 255, 0.03);">
                    <h3 style="font-size: 1.1rem; color: var(--text); margin-bottom: 15px; font-weight: 700;">Menu Pemilik Iklan</h3>
                    <div style="display: flex; gap: 15px; justify-content: flex-start; flex-wrap: wrap;">
                        <a href="{{ route('listings.edit', $listing->id) }}" style="color: #0ea5e9; font-weight: 800; text-decoration: none;">
                            Edit Postingan
                        </a>
                        {{-- Upgrade ke Premium dinonaktifkan sementara --}}
                    </div>
                </div>
                @endif
            @endauth
        </div> <!-- End listing-main-column -->
    </div> <!-- End listing-details-grid -->

        <!-- Section Postingan Lainnya (Pindahan dari Sidebar) -->
        <div style="margin-top: 50px;">
        @if($listing->whatsapp_visibility == 0)
            <div id="contact-form" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 30px; margin-bottom: 50px; max-width: 800px;">
                <h3 style="font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: 10px;">Tertarik dengan iklan ini?</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Pengiklan memilih untuk menerima pesan melalui admin. Silakan isi form di bawah ini.</p>
                
                <form action="{{ route('listing.contact.admin', $listing->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Nomor WA Anda</label>
                        <input type="text" name="visitor_whatsapp" class="form-control" placeholder="Contoh: 0812xxxx" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Pesan kepada pengiklan</label>
                        <textarea name="visitor_message" rows="4" class="form-control" placeholder="Tuliskan pesan atau pertanyaan Anda..." required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; border-radius: 12px; font-weight: 800;">Kirim Pesan</button>
                </form>
            </div>
        @endif

        <!-- Premium Listings Section -->
        @if($sidebarPremiumListings->count() > 0)
            <div style="margin-bottom: 50px;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #b45309; margin-bottom: 25px;">Postingan Premium</h2>
                <div class="listing-grid-related">
                    @foreach($sidebarPremiumListings as $premium)
                    <a href="{{ route('listings.show', $premium->slug) }}" class="listing-card-grid">
                        <div class="grid-image-wrapper">
                            <img src="{{ $premium->getThumbnailUrl() }}" alt="{{ $premium->title }}">
                            @if($premium->price > 0)
                                <div class="price-tag">
                                    Rp {{ number_format($premium->price, 0, ',', '.') }}
                                </div>
                            @endif
                        </div>

                        <div class="grid-content">
                            <h3 class="grid-title">{{ $premium->title }}</h3>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Related Listings Section -->
        @if($relatedListings->count() > 0)
            <div style="margin-bottom: 50px;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 25px;">Postingan Terkait</h2>
                <div class="listing-grid-related">
                    @foreach($relatedListings as $related)
                    <a href="{{ route('listings.show', $related->slug) }}" class="listing-card-grid">
                        <div class="grid-image-wrapper">
                            <img src="{{ $related->getThumbnailUrl() }}" alt="{{ $related->title }}">
                            @if($related->price > 0)
                                <div class="price-tag">
                                    Rp {{ number_format($related->price, 0, ',', '.') }}
                                </div>
                            @endif
                        </div>

                        <div class="grid-content">
                            <h3 class="grid-title">{{ $related->title }}</h3>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        @endif
        
            <div style="text-align: left; margin-bottom: 40px;">
                <a href="{{ route('home') }}" style="display: inline-block; padding: 12px 30px; border-radius: 12px; border: 2px solid #e2e8f0; color: #64748b; font-weight: 700; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='transparent'">Lihat Semua Iklan</a>
            </div>
        </div>
    </div>
</div>

<style>
    .listing-details-grid {
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .listing-main-column {
        width: 100%;
        margin: 0 !important;
        padding: 0 !important;
    }

    .listing-footer-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        align-items: center;
        margin-bottom: 30px;
    }

    .listing-footer-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    @media (max-width: 640px) {
        .listing-footer-row,
        .listing-footer-buttons {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .listing-footer-row {
            margin-bottom: 25px;
        }

        .listing-footer-row div:first-child {
            order: 1;
        }
        .listing-footer-row div:last-child {
            order: 2;
        }

        .listing-footer-row div[style*="font-size: 2.6rem"] {
            font-size: 2rem !important;
        }
    }

    /* Grid Card Styles from Homepage */
    .listing-grid-related {
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

    .price-tag {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(4px);
        color: #fff;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 700;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
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
        .listing-grid-related {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
    }
</style>
@endsection
