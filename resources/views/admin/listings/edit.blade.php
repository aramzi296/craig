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
        
        @php
            $currentCategory = $listing->categories->first();
            $currentParentId = $currentCategory ? ($currentCategory->parent_id ?: $currentCategory->id) : null;
            $currentChildId = $currentCategory ? ($currentCategory->parent_id ? $currentCategory->id : null) : null;
        @endphp

        <div class="form-group-horizontal">
            <label for="parent_category_id">Kategori Utama</label>
            <div class="form-input-side">
                <select id="parent_category_id" class="form-control" style="height: 48px; border-radius: 8px;" required>
                    <option value="">Pilih Kategori Utama</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('parent_category_id', $currentParentId) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="category_id">Sub Kategori</label>
            <div class="form-input-side">
                <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" style="height: 48px; border-radius: 8px;" required disabled>
                    <option value="">Pilih Sub Kategori</option>
                </select>
                <small style="color: var(--text-muted); display: block; margin-top: 5px;">Pilih Kategori Utama terlebih dahulu untuk memunculkan Sub Kategori.</small>
                @error('category_id')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const parentSelect = document.getElementById('parent_category_id');
                const childSelect = document.getElementById('category_id');
                const categoryTree = @json($categories);

                function updateChildCategories(selectedParentId, selectedChildId = null) {
                    childSelect.innerHTML = '<option value="">Pilih Sub Kategori</option>';
                    if (!selectedParentId) {
                        childSelect.disabled = true;
                        return;
                    }

                    const parentCat = categoryTree.find(c => c.id == selectedParentId);
                    if (parentCat && parentCat.children && parentCat.children.length > 0) {
                        parentCat.children.forEach(child => {
                            const opt = document.createElement('option');
                            opt.value = child.id;
                            opt.textContent = child.name;
                            if (selectedChildId && child.id == selectedChildId) {
                                opt.selected = true;
                            }
                            childSelect.appendChild(opt);
                        });
                        childSelect.disabled = false;
                    } else {
                        childSelect.disabled = true;
                    }
                }

                if (parentSelect && childSelect) {
                    parentSelect.addEventListener('change', function() {
                        updateChildCategories(this.value);
                    });

                    // Populate initial values
                    const initialParentId = "{{ old('parent_category_id', $currentParentId) }}";
                    const initialChildId = "{{ old('category_id', $currentChildId) }}";
                    if (initialParentId) {
                        updateChildCategories(initialParentId, initialChildId);
                    }
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
                <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $listing->description) }}</textarea>
                @error('description')
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

        <div class="form-group-horizontal" style="align-items: flex-start;">
            <label>Foto Fitur Utama</label>
            <div class="form-input-side">
                @php $featuredPhoto = $listing->photos->where('collection', 'foto_fitur')->first(); @endphp
                @if($featuredPhoto)
                    <div style="margin-bottom: 15px;">
                        <div style="position: relative; width: 150px; height: 150px; border: 2px solid var(--primary); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <img src="{{ $featuredPhoto->getThumbnailUrl() }}" alt="Featured" style="width: 100%; height: 100%; object-fit: cover;">
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--primary); color: white; font-size: 0.7rem; text-align: center; padding: 2px 0; font-weight: 700;">FOTO FITUR</div>
                        </div>
                    </div>
                @endif
                <input type="file" name="foto_fitur" id="foto_fitur" class="form-control @error('foto_fitur') is-invalid @enderror" accept="image/*">
                <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                    @if($featuredPhoto) Ganti foto fitur utama. @else Pilih foto fitur utama (akan muncul di daftar pencarian). @endif
                </small>
                @error('foto_fitur')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal" style="align-items: flex-start; margin-top: 30px;">
            <label>Galeri Foto</label>
            <div class="form-input-side">
                @php $galleryPhotos = $listing->photos->where('collection', 'galeri'); @endphp
                @if($galleryPhotos->count() > 0)
                    <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;">
                        @foreach($galleryPhotos as $photo)
                            <div style="position: relative; width: 100px; height: 100px; border: 1px solid var(--border); border-radius: 10px; overflow: hidden; background: #f8fafc;">
                                <img src="{{ $photo->getThumbnailUrl() }}" alt="Gallery" style="width: 100%; height: 100%; object-fit: cover;">
                                <button type="button" 
                                        onclick="if(confirm('Hapus foto dari galeri?')) { document.getElementById('delete-photo-{{ $photo->id }}').submit(); }"
                                        style="position: absolute; top: 5px; right: 5px; background: rgba(239, 68, 68, 0.9); color: white; border: none; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.7rem; z-index: 10;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <input type="file" name="galeri[]" id="galeri" class="form-control @error('galeri') is-invalid @enderror" multiple accept="image/*">
                <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                    Unggah foto tambahan untuk galeri. Format: <strong>{{ strtoupper(str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp'))) }}</strong>. Ukuran maks: <strong>{{ get_setting('max_image_size', 2048) / 1024 }}MB</strong> per foto.
                </small>
                @error('galeri')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror
                @error('galeri.*')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="district_id">Kecamatan</label>
            <div class="form-input-side">
                <select name="district_id" id="district_id" class="form-control @error('district_id') is-invalid @enderror" required>
                    <option value="">Pilih Kecamatan</option>
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
            <label for="subdistrict_id">Kelurahan</label>
            <div class="form-input-side">
                <select name="subdistrict_id" id="subdistrict_id" class="form-control @error('subdistrict_id') is-invalid @enderror" required>
                    <option value="">Pilih Kelurahan</option>
                </select>
                @error('subdistrict_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="address">Alamat Lengkap</label>
            <div class="form-input-side">
                <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" placeholder="Contoh: Jl. Sudirman No. 12, Ruko Citra Mas" value="{{ old('address', $listing->address) }}" required>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="comment_visibility">Kolom Komentar</label>
            <div class="form-input-side">
                <select name="comment_visibility" id="comment_visibility" class="form-control">
                    <option value="1" {{ old('comment_visibility', $listing->comment_visibility) == 1 ? 'selected' : '' }}>Aktifkan</option>
                    <option value="0" {{ old('comment_visibility', $listing->comment_visibility) == 0 ? 'selected' : '' }}>Nonaktifkan</option>
                </select>
                <small class="text-muted">Pilih apakah pengunjung bisa meninggalkan komentar.</small>
            </div>
        </div>

        <div class="form-group-horizontal">
            <label>#Tagar</label>
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
                    $currentTags = $listing->tags->pluck('name')->toArray();
                    $initialValue = old('tags', implode(',', $currentTags));
                @endphp
                <input name="tags" id="tags-tagify" class="form-control" placeholder="Pilih atau ketik #Tagar..." value="{{ $initialValue }}">
                
                <small id="tag-info" style="color: var(--text-muted); display: block; margin-top: 8px;">
                    Ketik dan pilih #Tagar yang sesuai. Maksimal <strong>10</strong> #Tagar. 
                    Jika #Tagar tidak ada di daftar, ketik saja nama #Tagar baru lalu tekan <strong>Enter</strong>.
                </small>
                @error('tags')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror

                <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const input = document.querySelector('#tags-tagify');
                        const whitelist = @json($tags->pluck('name'));
                        
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

                        // Subdistrict filter logic
                        const districtSelect = document.getElementById('district_id');
                        const subdistrictSelect = document.getElementById('subdistrict_id');
                        const subdistricts = @json($subdistricts);
                        const oldSubdistrictId = "{{ old('subdistrict_id', $listing->subdistrict_id) }}";

                        function populateSubdistricts() {
                            const districtId = districtSelect.value;
                            subdistrictSelect.innerHTML = '<option value="">Pilih Kelurahan</option>';

                            if (districtId) {
                                const filtered = subdistricts.filter(sub => sub.district_id == districtId);
                                filtered.forEach(sub => {
                                    const opt = document.createElement('option');
                                    opt.value = sub.id;
                                    opt.textContent = sub.name;
                                    if (oldSubdistrictId && oldSubdistrictId == sub.id) {
                                        opt.selected = true;
                                    }
                                    subdistrictSelect.appendChild(opt);
                                });
                            }
                        }

                        if (districtSelect && subdistrictSelect) {
                            districtSelect.addEventListener('change', populateSubdistricts);

                            // Populate on load if district value exists
                            if (districtSelect.value) {
                                populateSubdistricts();
                            }
                        }
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
