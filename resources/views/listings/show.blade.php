@extends('layouts.app')

@section('content')
<div class="container listing-detail-container" style="padding-top: 20px;">
    <div style="max-width: 800px; margin-left: 0;">
        <!-- Breadcrumbs -->
        @php
            $category = $listing->categories->first();
            $parentCategory = null;
            $childCategory = null;
            if ($category) {
                if ($category->parent_id) {
                    $parentCategory = $category->parent;
                    $childCategory = $category;
                } else {
                    $parentCategory = $category;
                }
            }
        @endphp
        <nav style="margin-bottom: 20px; color: #64748b; font-size: 0.85rem; font-weight: 500;">
            <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none;">Beranda</a> 
            @if($parentCategory)
                <span style="margin: 0 8px; opacity: 0.5;">/</span>
                <a href="{{ route('home', ['category' => $parentCategory->slug]) }}" style="color: #64748b; text-decoration: none;">{{ $parentCategory->name }}</a>
            @endif
            @if($childCategory)
                <span style="margin: 0 8px; opacity: 0.5;">/</span>
                <a href="{{ route('home', ['category' => $childCategory->slug]) }}" style="color: #64748b; text-decoration: none;">{{ $childCategory->name }}</a>
            @endif
            <span style="margin: 0 8px; opacity: 0.5;">/</span>
            <span style="color: #1e293b; font-weight: 700;">{{ $listing->title }}</span>
        </nav>

    <div>
        <div>
            <!-- 1. Judul (Title - Aligned Version) -->
            <div style="padding: 10px 0; margin-bottom: 5px;">
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
                        <img src="{{ $photo->getUrl() }}" style="width: 100%; height: 100%; object-fit: cover;">
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
                        <div style="font-size: 0.85rem; font-weight: 600; opacity: 0.8;">KODE AKTIVASI USAHA</div>
                        <div style="font-size: 1.2rem; font-weight: 800; letter-spacing: 2px;">{{ $listing->activation_code }}</div>
                    </div>
                    <div style="font-size: 0.8rem; font-weight: 600; background: rgba(255,255,255,0.5); padding: 4px 10px; border-radius: 6px;">BELUM AKTIF</div>
                </div>
            @endif

            <!-- 3. Atribut Penting (Compact List Version) -->
            <div style="padding: 10px 0; margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px;">


                @if($listing->address)
                <div style="font-size: 1rem; color: #334155;">
                    <span style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; width: 80px; display: inline-block;">Alamat</span>
                    <span style="font-weight: 700;">
                        {{ $listing->address }}
                    </span>
                </div>
                @endif

                @if($listing->subdistrict)
                <div style="font-size: 1rem; color: #334155;">
                    <span style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; width: 80px; display: inline-block;">Kelurahan</span>
                    <span style="font-weight: 700;">
                        {{ $listing->subdistrict->name }}
                    </span>
                </div>
                @endif

                @if($listing->district)
                <div style="font-size: 1rem; color: #334155;">
                    <span style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; width: 80px; display: inline-block;">Kecamatan</span>
                    <span style="font-weight: 700;">
                        {{ $listing->district->name }}
                    </span>
                </div>
                @endif

                @if($listing->tags->isNotEmpty())
                <div style="font-size: 1rem; color: #334155;">
                    <span style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; width: 80px; display: inline-block;">Tagar</span>
                    <span style="font-weight: 700;">
                        @foreach($listing->tags as $index => $tag)
                            <a href="{{ route('categories.index', ['tag' => $tag->slug]) }}" style="color: #0ea5e9; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">#{{ $tag->name }}</a>{{ $index < $listing->tags->count() - 1 ? ', ' : '' }}
                        @endforeach
                    </span>
                </div>
                @endif

                <div style="font-size: 1rem; color: #334155;">
                    <span style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; width: 80px; display: inline-block; vertical-align: middle;">Website</span>
                    <span style="vertical-align: middle;">
                        @if($listing->website)
                            <a href="{{ str_starts_with($listing->website, 'http') ? $listing->website : 'https://' . $listing->website }}" target="_blank" style="display: inline-flex; align-items: center; padding: 4px 12px; background-color: #f0f9ff; color: #0284c7; font-size: 0.8rem; font-weight: 700; border-radius: 6px; text-decoration: none; border: 1px solid #bae6fd; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#e0f2fe'; this.style.borderColor='#7dd3fc'" onmouseout="this.style.backgroundColor='#f0f9ff'; this.style.borderColor='#bae6fd'">
                                <i class="fa-solid fa-link" style="margin-right: 6px;"></i> Lihat tautan
                            </a>
                        @else
                            <a href="/iklan-website" target="_blank" style="display: inline-flex; align-items: center; padding: 4px 12px; background-color: #fff1f2; color: #e11d48; font-size: 0.8rem; font-weight: 700; border-radius: 6px; text-decoration: none; border: 1px solid #fecdd3; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#ffe4e6'; this.style.borderColor='#fda4af'" onmouseout="this.style.backgroundColor='#fff1f2'; this.style.borderColor='#fecdd3'">
                                <i class="fa-solid fa-globe" style="margin-right: 6px;"></i> Belum ada website. Lihat iklan
                            </a>
                        @endif
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
                            </div>
                            @if($listing->user->created_at)
                            <div style="font-size: 0.8rem; color: #64748b;">Member sejak {{ $listing->user->created_at->format('M Y') }}</div>
                            @endif
                        </div>
                    </div>
                    </div>
                </div>

                <div class="listing-footer-buttons">
                    @php
                        $canSeeContact = false;
                        if ($listing->whatsapp_visibility == 2) { $canSeeContact = true; } 
                        elseif ($listing->whatsapp_visibility == 1) { $canSeeContact = auth()->check(); }
                    @endphp

                    @if($canSeeContact)
                        <a href="{{ route('listings.whatsapp', $listing->id) }}" target="_blank" class="btn-detail btn-detail-whatsapp">
                            <i class="fa-brands fa-whatsapp"></i> Hubungi WhatsApp
                        </a>
                    @elseif($listing->whatsapp_visibility == 1)
                        <a href="{{ route('login') }}" class="btn-detail btn-detail-sky">
                            <i class="fa-solid fa-right-to-bracket"></i> Login untuk Chat
                        </a>
                    @else
                        <a href="#contact-form" class="btn-detail btn-detail-slate">
                            <i class="fa-regular fa-envelope"></i> Hubungi via Form
                        </a>
                    @endif
                    
                    @auth
                        <form action="{{ route('listings.favorite', $listing->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-detail {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'btn-detail-favorited' : 'btn-detail-favorite' }}">
                                @if(auth()->user()->favorites()->where('listing_id', $listing->id)->exists())
                                    <i class="fa-solid fa-heart"></i> Hapus dari Favorit
                                @else
                                    <i class="fa-regular fa-heart"></i> Tambah ke Favorit
                                @endif
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn-detail btn-detail-favorite">
                            <i class="fa-regular fa-heart"></i> Tambah ke Favorit
                        </a>
                    @endauth

                    <button type="button" onclick="openReportModal()" class="btn-detail btn-detail-report">
                        <i class="fa-solid fa-triangle-exclamation"></i> Laporkan Usaha
                    </button>

                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.listings.edit', $listing->id) }}" class="btn-detail btn-detail-sky" style="width: 100%; box-sizing: border-box;">
                                <i class="fa-solid fa-pen-to-square"></i> Edit Profil Usaha (Admin)
                            </a>
                        @elseif($listing->user_id == auth()->id())
                            <a href="{{ route('listings.edit', $listing->id) }}" class="btn-detail btn-detail-sky" style="width: 100%; box-sizing: border-box;">
                                <i class="fa-solid fa-pen-to-square"></i> Edit Profil Usaha
                            </a>
                        @endif
                    @endauth
                </div>


            </div>
        </div> <!-- End listing-main-column -->
    </div> <!-- End listing-details-grid -->

        <!-- Section Postingan Lainnya (Pindahan dari Sidebar) -->
        <div style="margin-top: 50px;">
        @if($listing->whatsapp_visibility == 0)
            <div id="contact-form" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 30px; margin-bottom: 50px; max-width: 800px;">
                <h3 style="font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: 10px;">Tertarik dengan usaha ini?</h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Pemilik usaha memilih untuk menerima pesan melalui admin. Silakan isi form di bawah ini.</p>
                
                <form action="{{ route('listing.contact.admin', $listing->id) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Nomor WA Anda</label>
                        <input type="text" name="visitor_whatsapp" class="form-control" placeholder="Contoh: 0812xxxx" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Pesan kepada pemilik usaha</label>
                        <textarea name="visitor_message" rows="4" class="form-control" placeholder="Tuliskan pesan atau pertanyaan Anda..." required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; border-radius: 12px; font-weight: 800;">Kirim Pesan</button>
                </form>
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
                            @if($related->getThumbnailUrl())
                                <img src="{{ $related->getThumbnailUrl() }}" alt="{{ $related->title }}">
                            @else
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8fafc; color: #94a3b8; font-size: 0.75rem; font-weight: 700; gap: 6px; border-bottom: 1px solid #f1f5f9;">
                                    <i class="fa-regular fa-image" style="font-size: 1.5rem; color: #cbd5e1;"></i>
                                    <span>No Picture</span>
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
                <a href="{{ route('home') }}" style="display: inline-block; padding: 12px 30px; border-radius: 12px; border: 2px solid #e2e8f0; color: #64748b; font-weight: 700; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='transparent'">Lihat Semua Usaha</a>
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
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .btn-detail {
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        cursor: pointer;
    }

    .btn-detail:hover {
        transform: translateY(-2px);
    }

    .btn-detail-whatsapp {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2);
    }

    .btn-detail-whatsapp:hover {
        box-shadow: 0 6px 16px rgba(37, 211, 102, 0.3);
    }

    .btn-detail-sky {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);
    }

    .btn-detail-sky:hover {
        box-shadow: 0 6px 16px rgba(14, 165, 233, 0.3);
    }

    .btn-detail-slate {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(100, 116, 139, 0.2);
    }

    .btn-detail-slate:hover {
        box-shadow: 0 6px 16px rgba(100, 116, 139, 0.3);
    }

    .btn-detail-favorite {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #cbd5e1;
    }

    .btn-detail-favorite:hover {
        background: #f1f5f9;
    }

    .btn-detail-favorited {
        background: #fee2e2;
        color: #ef4444;
        border: 1px solid #fca5a5;
    }

    .btn-detail-favorited:hover {
        background: #fecaca;
    }

    .btn-detail-report {
        background: #fff5f5;
        color: #e53e3e;
        border: 1px solid #fed7d7;
    }

    .btn-detail-report:hover {
        background: #ffebeb;
    }

    @media (max-width: 640px) {
        .listing-footer-row {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .listing-footer-buttons {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .listing-footer-buttons form {
            display: block !important;
            width: 100% !important;
        }

        .listing-footer-buttons form button {
            width: 100% !important;
        }

        .listing-footer-buttons .btn-detail {
            width: 100% !important;
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

    /* Report Modal Styles */
    .report-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .report-modal-overlay.show {
        opacity: 1;
    }

    .report-modal-card {
        background: #ffffff;
        border-radius: 20px;
        width: 100%;
        max-width: 500px;
        margin: 20px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(226, 232, 240, 0.8);
        overflow: hidden;
        transform: scale(0.9);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .report-modal-overlay.show .report-modal-card {
        transform: scale(1);
    }

    .report-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
    }

    .report-modal-header h3 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        color: #e53e3e;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .report-modal-close-btn {
        background: none;
        border: none;
        font-size: 1.75rem;
        color: #94a3b8;
        cursor: pointer;
        transition: color 0.2s;
        line-height: 1;
        padding: 0;
    }

    .report-modal-close-btn:hover {
        color: #475569;
    }

    .report-modal-body {
        padding: 24px;
        max-height: 70vh;
        overflow-y: auto;
    }

    .report-form-group {
        margin-bottom: 20px;
    }

    .report-form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 8px;
    }

    .report-form-control {
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        font-size: 0.95rem;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.2s ease;
        box-sizing: border-box;
        font-family: inherit;
    }

    .report-form-control:focus {
        outline: none;
        border-color: #ef4444;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    select.report-form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 16px;
        padding-right: 40px;
    }

    .report-form-help {
        display: block;
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 6px;
        font-weight: 500;
    }

    .report-modal-footer {
        padding: 16px 24px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .report-btn-secondary {
        padding: 12px 20px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        background: #fff;
        color: #64748b;
        border: 1.5px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
    }

    .report-btn-secondary:hover {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }

    .report-btn-primary {
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        transition: all 0.2s;
    }

    .report-btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
    }
</style>

<!-- Report Listing Modal -->
<div id="reportModal" class="report-modal-overlay" style="display: none;">
    <div class="report-modal-card">
        <div class="report-modal-header">
            <h3><i class="fa-solid fa-triangle-exclamation"></i> Laporkan Usaha</h3>
            <button type="button" onclick="closeReportModal()" class="report-modal-close-btn">&times;</button>
        </div>
        <form action="{{ route('listings.report', $listing->id) }}" method="POST">
            @csrf
            <div class="report-modal-body">
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px; line-height: 1.5;">
                    Bantu kami menjaga kenyamanan komunitas Sebatam. Silakan pilih alasan laporan Anda untuk usaha: <strong style="color: #1e293b;">"{{ $listing->title }}"</strong>.
                </p>

                <div class="report-form-group">
                    <label>Pilih Alasan Laporan <span style="color: #ef4444;">*</span></label>
                    <select name="reason" required class="report-form-control">
                        <option value="" disabled selected>-- Pilih Alasan --</option>
                        <option value="Penipuan">Penipuan (Indikasi penipuan/kriminal)</option>
                        <option value="Spam / Duplikat">Spam / Duplikat (Usaha sampah/berulang-ulang)</option>
                        <option value="Konten Tidak Layak">Konten Tidak Layak (Melanggar norma/hukum)</option>
                        <option value="Usaha Sudah Tutup">Usaha Sudah Tutup (Informasi tidak lagi aktif)</option>
                        <option value="Lainnya">Lainnya (Tulis detail di kolom keterangan)</option>
                    </select>
                </div>

                @guest
                    <div class="report-form-group">
                        <label>Nomor WhatsApp Anda <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="reporter_whatsapp" required placeholder="Contoh: 0812xxxx" class="report-form-control">
                        <span class="report-form-help">Digunakan oleh admin untuk verifikasi atau tindak lanjut laporan.</span>
                    </div>
                @endguest

                <div class="report-form-group">
                    <label>Keterangan Tambahan / Bukti (Opsional)</label>
                    <textarea name="description" rows="4" placeholder="Berikan rincian penjelasan singkat mengenai laporan Anda..." class="report-form-control"></textarea>
                </div>
            </div>
            <div class="report-modal-footer">
                <button type="button" onclick="closeReportModal()" class="report-btn-secondary">Batal</button>
                <button type="submit" class="report-btn-primary">Kirim Laporan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReportModal() {
        const modal = document.getElementById('reportModal');
        modal.style.display = 'flex';
        // Trigger reflow for transition
        void modal.offsetWidth;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeReportModal() {
        const modal = document.getElementById('reportModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }

    // Close on overlay click
    document.getElementById('reportModal').onclick = function(e) {
        if (e.target.id === 'reportModal') {
            closeReportModal();
        }
    };
</script>
@endsection
