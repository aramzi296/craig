@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Tambah Listing Baru</h1>
    <p style="color: var(--text-muted);">Buat iklan baru di BatamCraig sebagai admin.</p>
</div>

<div class="form-card">
    <form action="{{ route('admin.listings.store') }}" method="POST">
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
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; background: #f8fafc; padding: 15px; border-radius: 8px;">
                    @foreach($categories as $category)
                        <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" 
                                {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}
                                style="width: 18px; height: 18px;">
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
                @error('category_ids')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror

                <div style="margin-top: 15px;">
                    <label for="category_other" style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 5px;">Kategori Baru:</label>
                    <input type="text" name="category_other" id="category_other" class="form-control" placeholder="Tulis nama kategori baru..." value="{{ old('category_other') }}">
                </div>
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
                <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
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
                <small style="color: var(--text-muted); display: block; margin-top: 8px;">Maksimal 8 fitur utama.</small>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 40px; justify-content: flex-end;">
            <a href="{{ route('admin.listings') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Terbitkan Iklan</button>
        </div>
    </form>
</div>
@endsection
