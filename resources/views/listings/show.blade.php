@extends('layouts.app')

@section('content')
<div class="container listing-detail-container" style="padding-top: 20px;">
    <!-- Breadcrumbs -->
    <nav style="margin-bottom: 20px; color: #64748b; font-size: 0.85rem; font-weight: 500;">
        <a href="{{ route('home') }}" style="color: #64748b; text-decoration: none;">Beranda</a> 
        <span style="margin: 0 8px; opacity: 0.5;">/</span>
        <a href="{{ route('home', ['category' => $listing->approvedCategories->first()->slug ?? 'lainnya']) }}" style="color: #64748b; text-decoration: none;">{{ $listing->approvedCategories->first()->name ?? 'Tanpa Kategori' }}</a> 
        <span style="margin: 0 8px; opacity: 0.5;">/</span>
        <span style="color: #1e293b; font-weight: 700;">{{ $listing->title }}</span>
    </nav>

    <div class="listing-details-grid {{ $listing->is_premium ? 'premium-layout' : '' }}">
        <div class="listing-main-column">
            <!-- Layout Galeri & Lightbox -->
            @if($listing->photos->count() > 0)
            <div style="background: white; border-radius: 12px; overflow: hidden; margin-bottom: 20px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <!-- Foto Utama -->
                <div style="width: 100%; aspect-ratio: 16/10; overflow: hidden; cursor: pointer; background: #f8fafc;" onclick="openLightbox(0)">
                    <img src="{{ $listing->getImageUrl() }}" alt="{{ $listing->title }}" style="width: 100%; height: 100%; object-fit: contain;">
                </div>

                <!-- Galeri Thumbnail -->
                @if($listing->photos->count() > 1)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); gap: 8px; padding: 12px; background: white; border-top: 1px solid #f1f5f9;">
                    @foreach($listing->photos as $index => $photo)
                    <div style="aspect-ratio: 1/1; border-radius: 8px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.2s;" 
                         onclick="openLightbox({{ $index }})"
                         onmouseover="this.style.borderColor='var(--primary)'" 
                         onmouseout="this.style.borderColor='transparent'">
                        <img src="{{ $photo->getThumbnailUrl() }}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Lightbox Modal Modern (Script remains same) -->
            <div id="lightbox" style="display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); justify-content: center; align-items: center; padding: 20px; user-select: none;">
                <span style="position: absolute; top: 20px; right: 30px; color: white; font-size: 3rem; font-weight: 300; cursor: pointer; line-height: 1; z-index: 10001;" onclick="closeLightbox()">&times;</span>
                <button onclick="prevImage()" style="position: absolute; left: 20px; background: rgba(255,255,255,0.1); color: white; border: none; padding: 20px 15px; border-radius: 8px; cursor: pointer; transition: 0.3s; z-index: 10001;"><i class="fa-solid fa-chevron-left"></i></button>
                <button onclick="nextImage()" style="position: absolute; right: 20px; background: rgba(255,255,255,0.1); color: white; border: none; padding: 20px 15px; border-radius: 8px; cursor: pointer; transition: 0.3s; z-index: 10001;"><i class="fa-solid fa-chevron-right"></i></button>
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

            <!-- 1. Judul (Title) -->
            <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #f1f5f9;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap;">
                    @if($listing->listingType)
                        <span style="background: {{ $listing->listingType->color ?? 'var(--primary)' }}; color: white; padding: 2px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase;">
                            {{ $listing->listingType->name }}
                        </span>
                    @endif
                    @if($listing->is_premium)
                        <span style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 2px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800;">PREMIUM</span>
                    @endif
                </div>
                <h1 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; line-height: 1.2; margin: 0;">{{ $listing->title }}</h1>
            </div>

            <!-- 3. Atribut Penting (Price & Info Grid) -->
            <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #f1f5f9;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
                    <div>
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 5px;">Harga</span>
                        <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">
                            @if($listing->price && $listing->price > 0)
                                Rp {{ number_format($listing->price, 0, ',', '.') }}
                            @else
                                Hubungi Kami
                            @endif
                        </span>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 5px;">Lokasi</span>
                        <span style="font-size: 1.1rem; font-weight: 700; color: #334155;">
                            <i class="fa-solid fa-location-dot" style="color: #64748b; margin-right: 4px;"></i> {{ $listing->district?->name ?? 'Batam' }}
                        </span>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 5px;">Kategori</span>
                        <span style="font-size: 1.1rem; font-weight: 700; color: #334155;">
                            {{ $listing->approvedCategories->pluck('name')->first() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Deskripsi (Description) -->
            <div style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #f1f5f9;">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Deskripsi</h3>
                <div style="line-height: 1.7; color: #475569; font-size: 1rem;">
                    {!! nl2br(e($listing->description)) !!}
                </div>
            </div>

            <!-- 4. Kontak & User (Interaction) -->
            <div style="background: white; padding: 25px; border-radius: 12px; border: 1px solid #f1f5f9; margin-bottom: 30px;">
                <div class="listing-footer-row" style="margin-bottom: 25px;">
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 10px; border: 1px solid #f1f5f9;">
                        <img src="{{ $listing->user->getProfilePhoto() }}" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid white;" alt="">
                        <div>
                            <div style="font-weight: 700; font-size: 1rem; color: #1e293b;">
                                <a href="{{ route('user.listings', $listing->user_id) }}" style="color: inherit; text-decoration: none;">{{ $listing->user->name }}</a>
                                @if($listing->user->is_verified) <i class="fa-solid fa-circle-check" style="color: #3b82f6; font-size: 0.85rem;" title="Terverifikasi"></i> @endif
                            </div>
                            <div style="font-size: 0.8rem; color: #64748b;">Member sejak {{ $listing->user->created_at->format('M Y') }}</div>
                        </div>
                    </div>
                    <div style="text-align: right; color: #94a3b8; font-size: 0.8rem; font-weight: 500;">
                        ID: #{{ 1000 + $listing->id }}<br>Terbit: {{ $listing->created_at->format('d M Y') }}
                    </div>
                </div>

                <div class="listing-footer-buttons" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    @php
                        $canSeeContact = false;
                        if ($listing->whatsapp_visibility == 2) { $canSeeContact = true; } 
                        elseif ($listing->whatsapp_visibility == 1) { $canSeeContact = auth()->check(); }
                    @endphp

                    @if($canSeeContact)
                        <a href="https://wa.me/{{ $listing->user->whatsapp }}?text=Halo {{ $listing->user->name }}, saya tertarik dengan iklan Anda di SEBATAM: {{ $listing->title }}." target="_blank" class="btn btn-primary" style="padding: 14px; font-weight: 800; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> Hubungi WhatsApp
                        </a>
                    @elseif($listing->whatsapp_visibility == 1)
                        <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 14px; font-weight: 800; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-lock"></i> Login untuk Chat
                        </a>
                    @else
                        <div class="btn btn-secondary disabled" style="padding: 14px; font-weight: 800; border-radius: 10px; opacity: 0.7; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-eye-slash"></i> WA Private
                        </div>
                    @endif
                    
                    @auth
                        <form action="{{ route('listings.favorite', $listing->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'btn-secondary' : 'btn-outline' }}" style="width: 100%; padding: 14px; font-weight: 800; border-radius: 10px;">
                                <i class="fa-{{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'solid' : 'regular' }} fa-heart" style="{{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'color: #ef4444;' : '' }}"></i> 
                                Favorit
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline" style="padding: 14px; font-weight: 800; border-radius: 10px; text-align: center;">
                            <i class="fa-regular fa-heart"></i> Favorit
                        </a>
                    @endauth
                </div>

                <div style="margin-top: 25px; text-align: center; color: #94a3b8; font-size: 0.8rem; border-top: 1px solid #f8fafc; padding-top: 15px;">
                    Dilihat {{ number_format($listing->views_count, 0, ',', '.') }} kali • Diperbarui {{ $listing->updated_at->diffForHumans() }}
                </div>
            </div>

            <!-- Owner Actions -->
            @auth
                @if($listing->user_id == auth()->id())
                <div class="glass" style="padding: 25px; border-radius: var(--radius); margin-bottom: 40px; text-align: center; border: 1px dashed var(--accent); background: rgba(0, 163, 255, 0.03);">
                    <h3 style="font-size: 1.1rem; color: var(--text); margin-bottom: 15px; font-weight: 700;">Menu Pemilik Iklan</h3>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="{{ route('listings.edit', $listing->id) }}" class="btn btn-outline" style="border-color: var(--accent); color: var(--accent); padding: 12px 25px; border-radius: 10px;">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Postingan
                        </a>
                        {{-- Upgrade ke Premium dinonaktifkan sementara --}}
                    </div>
                </div>
                @endif
            @endauth

            <!-- Comments Section -->
            <div id="comments-section" class="glass" style="padding: 30px; border-radius: var(--radius); margin-top: 25px; margin-bottom: 40px;">
                <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 25px; color: var(--text);">Komentar ({{ $listing->comments->count() }})</h3>
                
                @if($listing->comment_visibility == 0)
                    <div style="text-align: center; padding: 30px; color: var(--text-muted); background: #f8fafc; border-radius: 12px; border: 1px dashed var(--border);">
                        <i class="fa-solid fa-comment-slash" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                        Fitur komentar dinonaktifkan oleh pemilik postingan.
                    </div>
                @else
                    <!-- Comment Form -->
                    @auth
                        @php $maxChars = get_setting('max_karakter_komentar', 250); @endphp
                        <form action="{{ route('comments.store', $listing->id) }}" method="POST" style="margin-bottom: 30px;">
                            @csrf
                            <div style="position: relative;">
                                <textarea name="content" id="comment-textarea" rows="3" class="form-control" placeholder="Tulis komentar atau pertanyaan Anda di sini..." style="border-radius: 12px; padding: 15px; margin-bottom: 5px; resize: none;" required maxlength="{{ $maxChars }}" oninput="updateCharCount()"></textarea>
                                <div id="char-count" style="text-align: right; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 12px;">0 / {{ $maxChars }}</div>
                            </div>
                            <div style="display: flex; justify-content: flex-end;">
                                <button type="submit" class="btn btn-primary" style="padding: 10px 25px;">Kirim Komentar</button>
                            </div>
                        </form>
                        
                        <script>
                            function updateCharCount() {
                                const textarea = document.getElementById('comment-textarea');
                                const counter = document.getElementById('char-count');
                                const maxChars = {{ $maxChars }};
                                const length = textarea.value.length;
                                counter.innerText = `${length} / ${maxChars}`;
                                
                                if (length >= maxChars) {
                                    counter.style.color = '#ef4444';
                                } else {
                                    counter.style.color = 'var(--text-muted)';
                                }
                            }
                        </script>
                    @else
                        @if($listing->comment_visibility == 1)
                            <div style="text-align: center; padding: 25px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 30px;">
                                <i class="fa-solid fa-lock" style="color: var(--primary); margin-bottom: 10px; display: block; font-size: 1.5rem;"></i>
                                <span style="font-weight: 600; color: var(--text);">Login untuk melihat dan menulis komentar</span>
                                <div style="margin-top: 15px;">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Login Sekarang</a>
                                </div>
                            </div>
                        @else
                            <div style="margin-bottom: 30px; padding: 15px; background: #f0f9ff; border-radius: 10px; border: 1px solid #bae6fd; color: #0369a1; font-size: 0.9rem;">
                                <i class="fa-solid fa-circle-info"></i> Silakan <a href="{{ route('login') }}" style="font-weight: 700; text-decoration: underline;">Login</a> untuk menulis komentar.
                            </div>
                        @endif
                    @endauth

                    <!-- Comments List -->
                    @php
                        $canSeeComments = ($listing->comment_visibility == 2 || ($listing->comment_visibility == 1 && auth()->check()));
                    @endphp

                    @if($canSeeComments)
                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            @forelse($listing->comments as $comment)
                                <div style="display: flex; gap: 15px;">
                                    <img src="{{ $comment->user->getProfilePhoto() }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; flex-shrink: 0;" alt="">
                                    <div style="flex: 1;">
                                        <div style="background: #f8fafc; padding: 15px; border-radius: 0 15px 15px 15px; border: 1px solid var(--border);">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                                <span style="font-weight: 700; font-size: 0.95rem;">{{ $comment->user->name }}</span>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div style="font-size: 0.95rem; line-height: 1.5; color: var(--text);">
                                                {{ nl2br(e($comment->content)) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 20px; color: var(--text-muted);">
                                    Belum ada komentar. Jadilah yang pertama bertanya!
                                </div>
                            @endforelse
                        </div>
                    @endif
                @endif
            </div>
        </div>

        @if(!$listing->is_premium)
        <!-- Sidebar Column -->
        <aside class="listing-sidebar-info">
            <!-- Premium Listings Section -->
            @if($sidebarPremiumListings->count() > 0)
                <div style="margin-bottom: 40px;">
                    <h2 class="section-title" style="margin-top: 0; margin-bottom: 20px; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; color: #b45309;">
                        <i class="fa-solid fa-crown"></i> Postingan Premium
                    </h2>
                    <div class="listing-grid">
                        @foreach($sidebarPremiumListings as $premium)
                        <a href="{{ route('listings.show', $premium->slug) }}" class="listing-card" style="height: auto; flex-direction: row; padding: 12px; gap: 15px; align-items: center; border-left: 3px solid #f59e0b; background: #fffbeb;">
                            @if($premium->getThumbnailUrl())
                                <img src="{{ $premium->getThumbnailUrl() }}" alt="{{ $premium->title }}" class="listing-image" style="width: 80px; height: 80px; margin: 0; border-radius: 8px; flex-shrink: 0;">
                            @endif
                            <div class="listing-details" style="padding: 0; flex: 1;">
                                <h3 class="listing-title" style="font-size: 0.9rem; margin-bottom: 4px; line-height: 1.3; color: var(--text);">{{ $premium->title }}</h3>
                                <div class="listing-category" style="font-size: 0.65rem; margin-bottom: 5px; display: flex; align-items: center; gap: 5px;">
                                    <span class="badge badge-premium" style="font-size: 0.55rem; padding: 2px 4px;">PREMIUM</span>
                                    <span>{{ $premium->approvedCategories->first()->name ?? '' }}</span>

                                </div>
                                <div class="listing-price" style="font-size: 0.95rem; margin-bottom: 0; color: var(--primary); font-weight: 700;">
                                    @if($premium->price && $premium->price > 0)
                                        Rp {{ number_format($premium->price, 0, ',', '.') }}
                                    @else
                                        Hubungi Kami
                                    @endif
                                </div>
                            </div>
                        </a>

                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Related Listings Section -->
            @if($relatedListings->count() > 0)
                <div>
                    <h2 class="section-title" style="margin-top: 0; margin-bottom: 20px; font-size: 1.3rem;">Postingan Terkait</h2>
                    <div class="listing-grid">
                        @foreach($relatedListings as $related)
                        <a href="{{ route('listings.show', $related->slug) }}" class="listing-card" style="height: auto; flex-direction: row; padding: 12px; gap: 15px; align-items: center;">
                            @if($related->getThumbnailUrl())
                                <img src="{{ $related->getThumbnailUrl() }}" alt="{{ $related->title }}" class="listing-image" style="width: 80px; height: 80px; margin: 0; border-radius: 8px; flex-shrink: 0;">
                            @endif
                            <div class="listing-details" style="padding: 0; flex: 1;">
                                <h3 class="listing-title" style="font-size: 0.9rem; margin-bottom: 4px; line-height: 1.3;">{{ $related->title }}</h3>
                                <div class="listing-category" style="font-size: 0.7rem; margin-bottom: 4px; display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                                    @if($related->listingType)
                                        <span style="background: {{ $related->listingType->color }}; color: white; padding: 1px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 700;">
                                            {{ $related->listingType->name }}
                                        </span>
                                    @endif
                                    <span>{{ $related->approvedCategories->take(1)->pluck('name')->join(', ') }}</span>

                                </div>
                                <div class="listing-price" style="font-size: 0.95rem; margin-bottom: 2px; color: var(--primary); font-weight: 700;">
                                    @if($related->price && $related->price > 0)
                                        Rp {{ number_format($related->price, 0, ',', '.') }}
                                    @else
                                        Hubungi Kami
                                    @endif
                                </div>
                                <div class="listing-location" style="font-size: 0.75rem; margin: 0; color: var(--text-muted);"><i class="fa-solid fa-location-dot"></i> {{ $related->district?->name ?? 'Batam' }}</div>
                            </div>
                        </a>

                        @endforeach
                    </div>
                </div>
            @endif
            
            <div style="margin-top: 30px; display: flex; flex-direction: column; gap: 10px;">
                <a href="{{ route('home') }}" class="btn btn-outline" style="width: 100%;">Lihat Semua Iklan</a>
            </div>
        </aside>
        @endif
    </div>
</div>

<style>
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
            text-align: center;
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
</style>
@endsection
