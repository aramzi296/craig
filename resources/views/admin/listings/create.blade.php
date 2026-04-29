@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Tambah Listing Baru</h1>
    <p style="color: var(--text-muted);">Buat iklan baru di BatamCraig sebagai admin.</p>
</div>

<div class="form-card">
    <form action="{{ route('admin.listings.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group-horizontal">
            <label for="user_id">Nama Pengguna (Pemilik Iklan)</label>
            <div class="form-input-side">
                <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                    <option value="">Pilih Pengguna</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ (old('user_id') == $user->id || request('user_id') == $user->id) ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->whatsapp }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="form-group-horizontal">
            <label for="listing_type_id">Tipe Iklan</label>
            <div class="form-input-side">
                <select name="listing_type_id" id="listing_type_id" class="form-control @error('listing_type_id') is-invalid @enderror" required>
                    <option value="">Pilih Tipe</option>
                    @foreach($listingTypes as $type)
                        <option value="{{ $type->id }}" {{ old('listing_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
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
                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Rumah Minimalis di Batam Centre" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="description">Deskripsi Lengkap</label>
            <div class="form-input-side">
                <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="price">Harga (Rp)</label>
            <div class="form-input-side">
                <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" placeholder="Contoh: 500000 (kosongkan jika tidak ada)">
                <small class="text-muted">Kosongkan jika iklan berupa pengumuman atau informasi tanpa harga.</small>
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="website">Link Website (Opsional)</label>
            <div class="form-input-side">
                <input type="url" name="website" id="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website') }}" placeholder="https://example.com">
                <small class="text-muted">Link website produk, portfolio, atau info lebih lanjut.</small>
                @error('website')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="photos">Foto Iklan</label>
            <div class="form-input-side">
                <input type="file" name="photos[]" id="photos" class="form-control @error('photos') is-invalid @enderror" multiple accept="image/*">
                <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                    Anda dapat mengunggah beberapa foto sekaligus.
                </small>
                @error('photos')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @error('photos.*')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="district_id">Lokasi di Batam</label>
            <div class="form-input-side">
                <select name="district_id" id="district_id" class="form-control @error('district_id') is-invalid @enderror" required>
                    <option value="">Pilih Lokasi</option>
                    @foreach($districts as $dist)
                        <option value="{{ $dist->id }}" {{ old('district_id') == $dist->id ? 'selected' : '' }}>{{ $dist->name }}</option>
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

                <input name="categories" id="categories-tagify" class="form-control" placeholder="Pilih atau ketik kategori..." value="{{ old('categories', '') }}">
                
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


        {{-- Status Aktif ditiadakan saat pembuatan agar iklan selalu tidak langsung aktif --}}
        <input type="hidden" name="is_active" value="0">

        <div style="display: flex; gap: 15px; margin-top: 40px; justify-content: flex-end;">
            <a href="{{ route('admin.listings') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Terbitkan Iklan</button>
        </div>
    </form>
</div>

{{-- Tambahkan Select2 untuk penanganan user dalam jumlah besar --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .select2-container--default .select2-selection--single {
        height: 48px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        padding: 0 10px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
    }
</style>

<script>
    $(document).ready(function() {
        $('#user_id').select2({
            placeholder: 'Ketik nama atau nomor WA pengguna...',
            minimumInputLength: 2,
            ajax: {
                url: "{{ route('admin.users.search') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
    });
</script>
@endsection
