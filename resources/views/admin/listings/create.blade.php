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
            <label for="parent_category_id">Kategori Utama</label>
            <div class="form-input-side">
                <select id="parent_category_id" class="form-control" style="height: 48px; border-radius: 8px;" required>
                    <option value="">Pilih Kategori Utama</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('parent_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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

                    // Populate on old/initial value if present
                    const oldParentId = "{{ old('parent_category_id') }}";
                    const oldChildId = "{{ old('category_id') }}";
                    if (oldParentId) {
                        updateChildCategories(oldParentId, oldChildId);
                    }
                }
            });
        </script>

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
            <label for="foto_fitur">Foto Fitur Utama</label>
            <div class="form-input-side">
                <input type="file" name="foto_fitur" id="foto_fitur" class="form-control @error('foto_fitur') is-invalid @enderror" accept="image/*">
                <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                    Pilih foto fitur utama (akan muncul di daftar pencarian).
                </small>
                @error('foto_fitur')
                    <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="galeri">Galeri Foto</label>
            <div class="form-input-side">
                <input type="file" name="galeri[]" id="galeri" class="form-control @error('galeri') is-invalid @enderror" multiple accept="image/*">
                <small class="text-muted" style="display: block; margin-top: 5px;">Format: <strong>{{ strtoupper(str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp'))) }}</strong>. Ukuran maks: <strong>{{ get_setting('max_image_size', 2048) / 1024 }}MB</strong> per foto.</small>
                <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                    Unggah beberapa foto sekaligus untuk galeri.
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
                        <option value="{{ $dist->id }}" {{ old('district_id') == $dist->id ? 'selected' : '' }}>{{ $dist->name }}</option>
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
                <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" placeholder="Contoh: Jl. Sudirman No. 12, Ruko Citra Mas" value="{{ old('address') }}" required>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group-horizontal">
            <label for="comment_visibility">Kolom Komentar</label>
            <div class="form-input-side">
                <select name="comment_visibility" id="comment_visibility" class="form-control">
                    <option value="1" {{ old('comment_visibility', 1) == 1 ? 'selected' : '' }}>Aktifkan</option>
                    <option value="0" {{ old('comment_visibility') == '0' ? 'selected' : '' }}>Nonaktifkan</option>
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

                <input name="tags" id="tags-tagify" class="form-control" placeholder="Pilih atau ketik #Tagar..." value="{{ old('tags', '') }}">
                
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

        // Subdistrict filter logic
        const districtSelect = document.getElementById('district_id');
        const subdistrictSelect = document.getElementById('subdistrict_id');
        const subdistricts = @json($subdistricts);
        const oldSubdistrictId = "{{ old('subdistrict_id') }}";

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

        districtSelect.addEventListener('change', populateSubdistricts);

        // Populate on load if district value exists
        if (districtSelect.value) {
            populateSubdistricts();
        }
    });
</script>
@endsection
