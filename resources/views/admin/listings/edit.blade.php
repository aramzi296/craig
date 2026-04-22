@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Edit Listing</h1>
    <p style="color: var(--text-muted);">Ubah informasi iklan: {{ $listing->title }}</p>
</div>

<div class="form-card">
    <form action="{{ route('admin.listings.update', $listing->id) }}" method="POST">
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
            <label>Kategori</label>
            <div class="form-input-side">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; background: #f8fafc; padding: 15px; border-radius: 8px;">
                    @php
                        $selectedIds = old('category_ids', $listing->categories->pluck('id')->toArray());
                    @endphp
                    @foreach($categories as $category)
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" 
                                {{ in_array($category->id, $selectedIds) ? 'checked' : '' }}
                                style="width: 18px; height: 18px;">
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
                @error('category_ids')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror

                <div style="margin-top: 15px;">
                    <label for="category_other" style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 5px;">Tambah Kategori Baru:</label>
                    <input type="text" name="category_other" id="category_other" class="form-control" placeholder="Tulis nama kategori baru..." value="{{ old('category_other') }}">
                </div>
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
            <label for="description">Deskripsi Lengkap</label>
            <div class="form-input-side">
                <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $listing->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>


        <div style="display: flex; gap: 15px; margin-top: 40px; justify-content: flex-end;">
            <a href="{{ route('admin.listings') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Perbarui Iklan</button>
        </div>
    </form>
</div>
@endsection
