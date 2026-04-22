@extends('layouts.dashboard')

@section('dashboard_content')
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; font-weight: 700;">Edit Iklan Saya</h1>
        <p style="color: var(--text-muted);">Ubah informasi iklan Anda: {{ $listing->title }}</p>
    </div>

    <div class="form-card">
        <form action="{{ route('listings.update', $listing->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-group-horizontal">
                <label for="listing_type_id">Tipe Iklan</label>
                <div class="form-input-side">
                    <select name="listing_type_id" id="listing_type_id" class="form-control @error('listing_type_id') is-invalid @enderror" required>
                        @foreach($listingTypes as $type)
                            <option value="{{ $type->id }}" data-slug="{{ $type->slug }}" {{ old('listing_type_id', $listing->listing_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('listing_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const typeSelect = document.getElementById('listing_type_id');
                    const websiteWrapper = document.getElementById('website-field-wrapper');
                    
                    if (typeSelect && websiteWrapper) {
                        const linkWebsite = {{ config('sebatam.link_website') ? 'true' : 'false' }};
                        const linkWebsitePremium = {{ config('sebatam.link_website_premium') ? 'true' : 'false' }};

                        function toggleWebsiteField() {
                            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
                            const isPremium = selectedOption ? (selectedOption.getAttribute('data-slug') === 'premium') : false;

                            if (linkWebsite) {
                                websiteWrapper.style.display = '';
                            } else if (linkWebsitePremium && isPremium) {
                                websiteWrapper.style.display = '';
                            } else {
                                websiteWrapper.style.display = 'none';
                            }
                        }

                        typeSelect.addEventListener('change', toggleWebsiteField);
                        toggleWebsiteField(); // Initial check
                    }
                });
            </script>

            <div class="form-group-horizontal">
                <label for="title">Judul Iklan</label>
                <div class="form-input-side">
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $listing->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="description">Deskripsi Lengkap</label>
                <div class="form-input-side">
                    <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" required maxlength="{{ $listing->is_premium ? config('sebatam.huruf_deskripsi_iklan_premium', 2000) : config('sebatam.huruf_deskripsi_iklan', 100) }}">{{ old('description', $listing->description) }}</textarea>
                    <small class="text-muted" style="display: block; margin-top: 5px;">Maksimal {{ $listing->is_premium ? config('sebatam.huruf_deskripsi_iklan_premium', 2000) : config('sebatam.huruf_deskripsi_iklan', 100) }} huruf.@if(!$listing->is_premium) Upgrade ke premium untuk tambahan hingga {{ config('sebatam.huruf_deskripsi_iklan_premium', 2000) }} huruf.@endif</small>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="price">Harga (Opsional)</label>
                <div class="form-input-side">
                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $listing->price) }}" placeholder="kosongkan jika tidak ada">
                    <small class="text-muted">Kosongkan jika iklan berupa pengumuman atau informasi tanpa harga.</small>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label>Galeri Foto Saat Ini</label>
                <div class="form-input-side">
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        @forelse($listing->photos as $photo)
                            <div style="position: relative; width: 100px; height: 100px; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; group;">
                                <img src="{{ $photo->getThumbnailUrl() }}" alt="Thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                <button type="button" 
                                        onclick="if(confirm('Hapus foto ini?')) { document.getElementById('delete-photo-{{ $photo->id }}').submit(); }"
                                        style="position: absolute; top: 5px; right: 5px; background: rgba(239, 68, 68, 0.9); color: white; border: none; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.7rem; z-index: 10;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        @empty
                            <p style="color: var(--text-muted); font-size: 0.9rem;">Belum ada foto galeri.</p>
                        @endforelse
                    </div>
                    <small style="color: var(--text-muted); display: block; margin-top: 10px;">
                        Saat ini ada {{ $listing->photos->count() }} foto. 
                        Jatah sisa: {{ ($listing->is_premium ? config('sebatam.max_foto_iklan_premium', 8) : config('sebatam.max_foto_iklan', 0)) - $listing->photos->count() }} foto.
                    </small>
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="photos">Tambah Foto Galeri</label>
                <div class="form-input-side">
                    <input type="file" name="photos[]" id="photos" class="form-control @error('photos') is-invalid @enderror" multiple accept="image/*">
                    <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                        Unggah foto tambahan. Maksimal total foto adalah <strong>{{ $listing->is_premium ? config('sebatam.max_foto_iklan_premium', 8) : config('sebatam.max_foto_iklan', 0) }}</strong>.
                    </small>
                    @error('photos')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                    @error('photos.*')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if(config('sebatam.link_website') || config('sebatam.link_website_premium'))
            <div class="form-group-horizontal" id="website-field-wrapper" style="{{ !config('sebatam.link_website') && !$listing->is_premium ? 'display: none;' : '' }}">
                <label for="website">Link Website (Opsional)</label>
                <div class="form-input-side">
                    <input type="url" name="website" id="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $listing->website) }}" placeholder="https://example.com">
                    <small class="text-muted">Link website produk, portfolio, atau info lebih lanjut.</small>
                    @error('website')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            @endif

            <div class="form-group-horizontal">
                <label for="district_id">Lokasi di Batam</label>
                <div class="form-input-side">
                    <select name="district_id" id="district_id" class="form-control @error('district_id') is-invalid @enderror" required>
                        @foreach($districts as $dist)
                            <option value="{{ $dist->id }}" {{ old('district_id', $listing->district_id) == $dist->id ? 'selected' : '' }}>{{ $dist->name }}</option>
                        @endforeach
                    </select>
                    @error('district_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label>Kategori</label>
                <div class="form-input-side">
                    <!-- Added Tagify CSS -->
                    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
                    <style>
                        .tagify {
                            --tag-bg: var(--primary);
                            --tag-hover: var(--primary-dark);
                            --tag-text-color: #fff;
                            --tag-border-radius: 8px;
                            --tag-remove-btn-color: #fff;
                            --tag-remove-btn-bg--hover: rgba(255, 255, 255, 0.2);
                            border-radius: var(--radius);
                            border: 1px solid var(--border);
                            padding: 5px;
                            width: 100%;
                            background: #f8fafc;
                            transition: all 0.2s;
                        }
                        .tagify--focus {
                            border-color: var(--primary);
                            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
                            background: white;
                        }
                        .tagify__tag > div {
                            padding: 0.3em 0.7em;
                            font-weight: 500;
                        }
                        .tagify__tag__removeBtn {
                            margin-right: 0.4em;
                        }
                        .tagify__dropdown__item--active {
                            background: var(--primary);
                            color: white;
                        }
                    </style>

                    @php
                        $currentCategories = $listing->categories->pluck('name')->toArray();
                        $initialValue = old('categories', implode(',', $currentCategories));
                    @endphp
                    <input name="categories" id="categories-tagify" class="form-control" placeholder="Pilih atau ketik kategori..." value="{{ $initialValue }}">
                    
                    <small id="category-info" style="color: var(--text-muted); display: block; margin-top: 8px;">
                        Ketik untuk mencari kategori. Maksimal <strong>{{ config('sebatam.max_category', 3) }}</strong> kategori. 
                        Jika kategori tidak ada, tekan <strong>Enter</strong> untuk menambahkan sebagai kategori baru.
                    </small>
                    @error('categories')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror

                    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const input = document.querySelector('#categories-tagify');
                            const whitelist = @json($categories->pluck('name'));
                            
                            const tagify = new Tagify(input, {
                                whitelist: whitelist,
                                maxTags: {{ config('sebatam.max_category', 3) }},
                                dropdown: {
                                    maxItems: 20,
                                    classname: "tags-look",
                                    enabled: 0,
                                    closeOnSelect: true
                                }
                            });
                        });
                    </script>
                </div>
            </div>



            <div class="form-group-horizontal" style="align-items: flex-start;">
                <label>Visibilitas Kontak & Interaksi</label>
                <div class="form-input-side" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- WhatsApp Setting -->
                    <div style="padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border);">
                        <p style="font-weight: 700; font-size: 0.9rem; margin-bottom: 15px; color: var(--text);"><i class="fa-brands fa-whatsapp"></i> Tombol WhatsApp</p>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="whatsapp_visibility" value="0" {{ old('whatsapp_visibility', $listing->whatsapp_visibility) == '0' ? 'checked' : '' }}> Tidak ditampilkan
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="whatsapp_visibility" value="1" {{ old('whatsapp_visibility', $listing->whatsapp_visibility) == '1' ? 'checked' : '' }}> Hanya user login
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="whatsapp_visibility" value="2" {{ old('whatsapp_visibility', $listing->whatsapp_visibility) == '2' ? 'checked' : '' }}> Semua pengunjung
                            </label>
                        </div>
                    </div>

                    <!-- Comments Setting -->
                    <div style="padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border);">
                        <p style="font-weight: 700; font-size: 0.9rem; margin-bottom: 15px; color: var(--text);"><i class="fa-solid fa-comments"></i> Kolom Komentar</p>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="comment_visibility" value="0" {{ old('comment_visibility', $listing->comment_visibility) == '0' ? 'checked' : '' }}> Tidak ditampilkan
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="comment_visibility" value="1" {{ old('comment_visibility', $listing->comment_visibility) == '1' ? 'checked' : '' }}> Hanya user login
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="comment_visibility" value="2" {{ old('comment_visibility', $listing->comment_visibility) == '2' ? 'checked' : '' }}> Semua pengunjung
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 40px; justify-content: flex-end;">
                <a href="{{ route('dashboard') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <!-- Hidden Delete Forms -->
    @foreach($listing->photos as $photo)
        <form id="delete-photo-{{ $photo->id }}" action="{{ route('listings.photos.destroy', $photo->id) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endsection
