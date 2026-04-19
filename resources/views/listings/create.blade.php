@extends('layouts.dashboard')

@section('dashboard_content')
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; font-weight: 700;">Pasang Iklan Baru</h1>
        <p style="color: var(--text-muted);">Bagikan apa yang Anda tawarkan ke seluruh komunitas di Batam.</p>
    </div>

    <div class="form-card">
        <form action="{{ route('listings.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
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
                            background: white;
                        }
                        .tagify--focus {
                            border-color: var(--primary);
                            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.1);
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

                            // Handle max tags reached
                            tagify.on('add', function(e) {
                                if (tagify.value.length >= {{ config('sebatam.max_category', 3) }}) {
                                    // Optionally show some feedback
                                }
                            });
                        });
                    </script>
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="title">Judul Iklan</label>
                <div class="form-input-side">
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Honda Vario 2022 Mulus" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="price">Harga (Rp)</label>
                <div class="form-input-side">
                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" placeholder="0" required>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="location">Lokasi di Batam</label>
                <div class="form-input-side">
                    <select name="location" id="location" class="form-control @error('location') is-invalid @enderror" required>
                        <option value="">Pilih Lokasi</option>
                        @foreach(['Batam Centre', 'Nagoya', 'Sekupang', 'Batu Ampar', 'Bengkong', 'Sei Beduk', 'Nongsa', 'Sagulung', 'Batu Aji'] as $loc)
                            <option value="{{ $loc }}" {{ old('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    </select>
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="description">Deskripsi Lengkap</label>
                <div class="form-input-side">
                    <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Jelaskan kondisi barang, kelengkapan, dsb." required maxlength="{{ config('sebatam.huruf_deskripsi_iklan', 100) }}">{{ old('description') }}</textarea>
                    <small class="text-muted">Maksimal {{ config('sebatam.huruf_deskripsi_iklan', 100) }} huruf. Upgrade ke premium untuk tambahan hingga {{ config('sebatam.huruf_deskripsi_iklan_premium', 2000) }} huruf.</small>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label>Fitur Penawaran</label>
                <div class="form-input-side">
                    <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                        @for($i = 0; $i < 8; $i++)
                            <input type="text" name="features[]" class="form-control" placeholder="Fitur {{ $i + 1 }}" value="{{ old('features.'.$i) }}" maxlength="{{ config('sebatam.huruf_fitur', 40) }}">
                        @endfor
                        <small class="text-muted" style="display: block; width: 100%; margin-top: 5px;">Maksimal {{ config('sebatam.huruf_fitur', 40) }} huruf per fitur.</small>
                    </div>
                    <small style="color: var(--text-muted); display: block; margin-top: 8px;">Maksimal 8 fitur utama yang akan ditampilkan pada ringkasan.</small>
                    @error('features')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="photos">Foto Fitur</label>
                <div class="form-input-side">
                    <input type="file" name="photos[]" id="photos" class="form-control @error('photos.*') is-invalid @enderror" multiple accept="image/*">
                    <small style="color: var(--text-muted); display: block; margin-top: 8px;">Anda bisa memilih lebih dari satu foto. Setiap foto akan otomatis dioptimalkan.</small>
                    @error('photos.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 40px; justify-content: flex-end;">
                <a href="{{ route('dashboard') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Terbitkan Iklan</button>
            </div>
        </form>
    </div>
@endsection
