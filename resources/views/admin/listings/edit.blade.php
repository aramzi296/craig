@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Edit Listing</h1>
    <p style="color: var(--text-muted);">Ubah informasi iklan: {{ $listing->title }}</p>
</div>

<div class="form-card">
    <form action="{{ route('admin.listings.update', $listing->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="form-group-horizontal">
            <label for="listing_type_id">Tipe Iklan</label>
            <div class="form-input-side">
                <select name="listing_type_id" id="listing_type_id" class="form-control @error('listing_type_id') is-invalid @enderror" required>
                    @foreach($listingTypes as $type)
                        <option value="{{ $type->id }}" {{ old('listing_type_id', $listing->listing_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('listing_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

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
                <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $listing->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="price">Harga (Rp)</label>
            <div class="form-input-side">
                <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $listing->price) }}" placeholder="kosongkan jika tidak ada">
                <small class="text-muted">Kosongkan jika iklan berupa pengumuman atau informasi tanpa harga.</small>
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="website">Link Website (Opsional)</label>
            <div class="form-input-side">
                <input type="url" name="website" id="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $listing->website) }}" placeholder="https://example.com">
                <small class="text-muted">Link website produk, portfolio, atau info lebih lanjut.</small>
                @error('website')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label>Galeri Foto Saat Ini</label>
            <div class="form-input-side">
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    @forelse($listing->photos as $photo)
                        <div style="position: relative; width: 100px; height: 100px; border: 1px solid var(--border); border-radius: 8px; overflow: hidden;">
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
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="photos">Tambah Foto</label>
            <div class="form-input-side">
                <input type="file" name="photos[]" id="photos" class="form-control @error('photos') is-invalid @enderror" multiple accept="image/*">
                <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                    Unggah foto tambahan. Maksimal 12 foto total.
                </small>
                @error('photos')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror
                @error('photos.*')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror
            </div>
        </div>

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
                        border-radius: 12px;
                        border: 1px solid #e2e8f0;
                        padding: 8px;
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
                    Ketik dan pilih kategori yang sesuai. Maksimal <strong>10</strong> kategori. 
                    Jika kategori tidak ada di daftar, ketik saja nama kategori baru lalu tekan <strong>Enter</strong>.
                </small>
                @error('categories')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror

                <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const input = document.querySelector('#categories-tagify');
                        const whitelist = @json($categories->pluck('name'));
                        
                        new Tagify(input, {
                            whitelist: whitelist,
                            maxTags: 10,
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


        <div style="display: flex; gap: 15px; margin-top: 40px; justify-content: flex-end;">
            <a href="{{ route('admin.listings') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Perbarui Iklan</button>
        </div>
    </form>
</div>

{{-- Hidden Delete Forms for Photos --}}
@foreach($listing->photos as $photo)
    <form id="delete-photo-{{ $photo->id }}" action="{{ route('admin.listings.photos.destroy', $photo->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach
@endsection
