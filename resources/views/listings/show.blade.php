@extends('layouts.app')

@section('content')
<div class="container listing-detail-container">
    <nav style="margin-bottom: 20px; color: var(--text-muted); font-size: 0.9rem;">
        <a href="{{ route('home') }}">Beranda</a> / 
        <a href="{{ route('home', ['category' => $listing->categories->first()->slug ?? 'lainnya']) }}">{{ $listing->categories->first()->name ?? 'Tanpa Kategori' }}</a> / 
        {{ $listing->title }}
    </nav>

    <div class="listing-details-grid {{ $listing->is_premium ? 'premium-layout' : '' }}">
        <div class="listing-main-column">
            <!-- Layout Galeri & Lightbox -->
            @if($listing->photos->count() > 0)
            <div class="glass" style="border-radius: var(--radius); overflow: hidden; margin-bottom: 25px;">
                <!-- Foto Utama -->
                <div style="width: 100%; aspect-ratio: 16/10; overflow: hidden; cursor: pointer;" onclick="openLightbox(0)">
                    <img src="{{ $listing->getImageUrl() }}" alt="{{ $listing->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                </div>

                <!-- Galeri Thumbnail -->
                @if($listing->photos->count() > 1)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; padding: 10px; background: #f8fafc; border-top: 1px solid var(--border);">
                    @foreach($listing->photos as $index => $photo)
                    <div style="aspect-ratio: 1/1; border-radius: 6px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all 0.2s;" 
                         onclick="openLightbox({{ $index }})"
                         onmouseover="this.style.borderColor='var(--primary)'" 
                         onmouseout="this.style.borderColor='transparent'">
                        <img src="{{ $photo->getThumbnailUrl() }}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Lightbox Modal Modern -->
            <div id="lightbox" style="display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); justify-content: center; align-items: center; padding: 20px; user-select: none;">
                <!-- Close Button -->
                <span style="position: absolute; top: 20px; right: 30px; color: white; font-size: 3rem; font-weight: 300; cursor: pointer; line-height: 1; z-index: 10001;" onclick="closeLightbox()">&times;</span>
                
                <!-- Nav Buttons -->
                <button onclick="prevImage()" style="position: absolute; left: 20px; background: rgba(255,255,255,0.1); color: white; border: none; padding: 20px 15px; border-radius: 8px; cursor: pointer; transition: 0.3s; z-index: 10001;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                    <i class="fa-solid fa-chevron-left" style="font-size: 2rem;"></i>
                </button>
                
                <button onclick="nextImage()" style="position: absolute; right: 20px; background: rgba(255,255,255,0.1); color: white; border: none; padding: 20px 15px; border-radius: 8px; cursor: pointer; transition: 0.3s; z-index: 10001;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                    <i class="fa-solid fa-chevron-right" style="font-size: 2rem;"></i>
                </button>

                <!-- Image Area -->
                <div style="max-width: 90%; max-height: 90%; display: flex; flex-direction: column; align-items: center; gap: 15px;">
                    <img id="lightbox-img" style="max-width: 100%; max-height: 85vh; border-radius: 8px; box-shadow: 0 0 50px rgba(0,0,0,0.5); transition: opacity 0.3s ease-in-out;">
                    <div id="lightbox-counter" style="color: rgba(255,255,255,0.6); font-size: 0.9rem; font-weight: 500; background: rgba(255,255,255,0.1); padding: 4px 15px; border-radius: 20px;"></div>
                </div>
            </div>

            <script>
                const galleryImages = @json($listing->photos->map(fn($p) => $p->getUrl()));
                let currentIndex = 0;

                function openLightbox(index) {
                    currentIndex = index;
                    updateLightboxContent();
                    document.getElementById('lightbox').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }

                function closeLightbox() {
                    document.getElementById('lightbox').style.display = 'none';
                    document.body.style.overflow = 'auto';
                }

                function updateLightboxContent() {
                    const img = document.getElementById('lightbox-img');
                    const counter = document.getElementById('lightbox-counter');
                    
                    img.style.opacity = '0';
                    setTimeout(() => {
                        img.src = galleryImages[currentIndex];
                        counter.innerText = `${currentIndex + 1} / ${galleryImages.length}`;
                        img.style.opacity = '1';
                    }, 150);
                }

                function nextImage() {
                    currentIndex = (currentIndex + 1) % galleryImages.length;
                    updateLightboxContent();
                }

                function prevImage() {
                    currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
                    updateLightboxContent();
                }

                // Keyboard Support
                document.addEventListener('keydown', function(e) {
                    const lightbox = document.getElementById('lightbox');
                    if (lightbox.style.display === 'flex') {
                        if (e.key === 'ArrowRight') nextImage();
                        if (e.key === 'ArrowLeft') prevImage();
                        if (e.key === 'Escape') closeLightbox();
                    }
                });

                // Close on click outside
                document.getElementById('lightbox').onclick = function(e) {
                    if (e.target.id === 'lightbox') closeLightbox();
                };
            </script>
            @endif

            <!-- 1. Judul (Title) -->
            <div class="glass" style="padding: 25px; border-radius: var(--radius); margin-bottom: 20px;">
                <h1 style="font-size: 2.2rem; font-weight: 700; margin: 0; color: var(--text); line-height: 1.2;">
                    {{ $listing->title }}
                    @if($listing->is_premium)
                        <span class="badge badge-premium" style="font-size: 0.8rem; vertical-align: middle; margin-top: -5px; display: inline-block;">PREMIUM</span>
                    @endif
                </h1>
            </div>

            <!-- 2. Keterangan (Description) -->
            <div class="glass" style="padding: 25px; border-radius: var(--radius); margin-bottom: 20px;">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 15px; color: var(--text); border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                    Deskripsi
                </h3>
                <div style="line-height: 1.8; color: var(--text); font-size: 1.05rem;">
                    {!! nl2br(e($listing->description)) !!}
                </div>
            </div>

            <!-- 3. Atribut (Attributes) -->
            <div class="glass" style="padding: 25px; border-radius: var(--radius); margin-bottom: 20px;">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 20px; color: var(--text); border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                    Atribut & Informasi
                </h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <!-- Harga -->
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Harga</span>
                        <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">
                            @if($listing->price && $listing->price > 0)
                                Rp {{ number_format($listing->price, 0, ',', '.') }}
                            @else
                                Hubungi Kami
                            @endif
                        </span>
                    </div>

                    <!-- Lokasi -->
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Lokasi</span>
                        <span style="font-size: 1.1rem; font-weight: 600; color: var(--text);">
                            <i class="fa-solid fa-location-dot" style="color: var(--secondary);"></i> {{ $listing->district?->name ?? 'Batam' }}, Batam
                        </span>
                    </div>

                    <!-- Kategori -->
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Kategori</span>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            @if($listing->listingType)
                                <span style="background: {{ $listing->listingType->color ?? 'var(--primary)' }}; color: white; padding: 2px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">
                                    {{ $listing->listingType->name }}
                                </span>
                            @endif
                            <span style="font-size: 1rem; font-weight: 600; color: var(--primary);">
                                {{ $listing->categories->pluck('name')->join(' • ') }}
                            </span>
                        </div>
                    </div>

                    <!-- Website -->
                    @if($listing->website)
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Website</span>
                        <a href="{{ $listing->website }}" target="_blank" rel="nofollow" style="font-size: 1rem; font-weight: 600; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 5px;">
                            <i class="fa-solid fa-globe"></i> Kunjungi Situs
                        </a>
                    </div>
                    @endif

                    <!-- Kadaluarsa -->
                    @if($listing->expires_at)
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Berakhir Pada</span>
                        <span style="font-size: 1.1rem; font-weight: 600; color: {{ $listing->expires_at->isPast() ? '#ef4444' : 'var(--text)' }};">
                            <i class="fa-solid fa-calendar-xmark" style="color: var(--secondary);"></i> {{ $listing->expires_at->format('d M Y') }}
                            <small style="display: block; font-size: 0.75rem; font-weight: 400; color: var(--text-muted);">({{ $listing->expires_at->diffForHumans() }})</small>
                        </span>
                    </div>
                    @endif
                </div>
            </div>


            <!-- 4. Kontak & User (Interaction) -->
            <div class="glass" style="padding: 25px; border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 40px;">
                <div class="listing-footer-row">
                    <div style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border);">
                        <img src="{{ $listing->user->getProfilePhoto() }}" style="width: 55px; height: 55px; border-radius: 50%; object-fit: cover;" alt="">
                        <div>
                            <div style="font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 6px;">
                                {{ $listing->user->name }}
                                @if($listing->user->is_verified)
                                    <i class="fa-solid fa-circle-check verified-badge" style="font-size: 0.9rem;" title="Akun Terverifikasi"></i>
                                @endif
                            </div>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">Member sejak {{ $listing->user->created_at->format('M Y') }}</div>
                        </div>
                    </div>
                    
                    <div style="text-align: right;">
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Terbit: {{ $listing->created_at->format('d M Y') }}</div>
                    </div>
                </div>

                <div class="listing-footer-buttons">
                    @php
                        $canSeeContact = false;
                        if ($listing->whatsapp_visibility == 2) {
                            $canSeeContact = true;
                        } elseif ($listing->whatsapp_visibility == 1) {
                            $canSeeContact = auth()->check();
                        }
                    @endphp

                    @if($canSeeContact)
                        <a href="https://wa.me/{{ $listing->user->whatsapp }}?text=Halo {{ $listing->user->name }}, saya tertarik dengan iklan Anda di Sebatam: {{ $listing->title }}. Apakah masih tersedia%3F" target="_blank" class="btn btn-primary" style="padding: 18px; font-size: 1.1rem; border-radius: 12px;">
                            <i class="fa-brands fa-whatsapp" style="font-size: 1.5rem;"></i> Hubungi via whatsapp
                        </a>
                    @elseif($listing->whatsapp_visibility == 1)
                        <a href="{{ route('login') }}" class="btn btn-primary" style="padding: 18px; font-size: 1.1rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fa-solid fa-lock"></i> Login untuk kirim WA
                        </a>
                    @else
                        <div class="btn btn-secondary disabled" style="padding: 18px; font-size: 1rem; border-radius: 12px; cursor: not-allowed; opacity: 0.7; display: flex; align-items: center; justify-content: center; gap: 8px; background: #e2e8f0; color: #64748b; border: none;">
                            <i class="fa-solid fa-eye-slash"></i> WA tidak ditampilkan
                        </div>
                    @endif
                    
                    @auth
                        <form action="{{ route('listings.favorite', $listing->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'btn-secondary' : 'btn-outline' }}" style="padding: 18px; font-size: 1.1rem; width: 100%; border-radius: 12px;">
                                <i class="fa-{{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'solid' : 'regular' }} fa-heart" style="{{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'color: #ef4444;' : '' }}"></i> 
                                {{ auth()->user()->favorites()->where('listing_id', $listing->id)->exists() ? 'Favorit Terdaftar' : 'Tambah ke Favorit' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline" style="padding: 18px; font-size: 1.1rem; text-align: center; border-radius: 12px;">
                            <i class="fa-regular fa-heart"></i> Tambah ke Favorit
                        </a>
                    @endauth
                </div>

                <div style="margin-top: 30px; text-align: center; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
                    Iklan ID: #BT{{ 1000 + $listing->id }} • Dilihat {{ number_format($listing->views_count, 0, ',', '.') }} kali • Diperbarui {{ $listing->updated_at->diffForHumans() }}
                </div>
            </div>

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
                                    <span>{{ $premium->categories->first()->name ?? '' }}</span>
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
                                    <span>{{ $related->categories->take(1)->pluck('name')->join(', ') }}</span>
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
                @auth
                    @if($listing->user_id == auth()->id())
                        <a href="{{ route('listings.edit', $listing->id) }}" class="btn btn-outline" style="width: 100%; border-color: var(--accent); color: var(--accent);">
                            <i class="fa-solid fa-pen-to-square"></i> Edit Postingan
                        </a>
                        @if(!$listing->is_premium && !$listing->hasPendingPremiumRequest())
                            <a href="{{ route('dashboard.premium.upgrade', $listing->id) }}" class="btn btn-primary" style="width: 100%; background: #f59e0b; border-color: #f59e0b;">
                                <i class="fa-solid fa-crown"></i> Upgrade ke Premium
                            </a>
                        @endif
                    @endif
                @endauth
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
