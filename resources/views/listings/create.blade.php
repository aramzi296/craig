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
                    <div id="category-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; background: #f8fafc; padding: 15px; border-radius: 8px;">
                        @foreach($categories as $category)
                            <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="category-checkbox"
                                    {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}
                                    style="width: 18px; height: 18px;">
                                {{ $category->name }}
                            </label>
                        @endforeach
                    </div>
                    <small id="category-info" style="color: var(--text-muted); display: block; margin-top: 8px;">
                        Pilih maksimal <strong>{{ config('sebatam.max_category', 3) }}</strong> kategori.
                    </small>
                    @error('category_ids')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const maxCategories = {{ config('sebatam.max_category', 3) }};
                            const checkboxes = document.querySelectorAll('.category-checkbox');
                            const otherCategoryContainer = document.getElementById('other-category-container');
                            
                            function updateVisibility() {
                                const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
                                if (checkedCount >= maxCategories) {
                                    otherCategoryContainer.style.display = 'none';
                                } else {
                                    otherCategoryContainer.style.display = 'block';
                                }
                            }

                            checkboxes.forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
                                    
                                    if (checkedCount > maxCategories) {
                                        this.checked = false;
                                        alert('Anda hanya dapat memilih maksimal ' + maxCategories + ' kategori.');
                                    }
                                    updateVisibility();
                                });
                            });

                            // Initial check
                            updateVisibility();
                        });
                    </script>

                    <div id="other-category-container" style="margin-top: 15px;">
                        <label for="category_other" style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 5px;">Kategori Baru (Jika tidak ada yang cocok):</label>
                        <input type="text" name="category_other" id="category_other" class="form-control" placeholder="Tulis nama kategori baru..." value="{{ old('category_other') }}">
                    </div>
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
                    <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Jelaskan kondisi barang, kelengkapan, dsb." required>{{ old('description') }}</textarea>
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
                            <input type="text" name="features[]" class="form-control" placeholder="Fitur {{ $i + 1 }}" value="{{ old('features.'.$i) }}">
                        @endfor
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
