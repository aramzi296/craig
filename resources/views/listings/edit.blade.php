@extends('layouts.dashboard')

@section('dashboard_content')
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; font-weight: 700;">Edit Iklan Saya</h1>
        <p style="color: var(--text-muted);">Ubah informasi iklan Anda: {{ $listing->title }}</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #fecaca;">
            <div style="font-weight: 700; margin-bottom: 10px; font-size: 1.1rem;">
                <i class="fa-solid fa-circle-exclamation"></i> Terjadi kesalahan:
            </div>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-card">
        <form action="{{ route('listings.update', $listing->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-group-horizontal">
                <label for="listing_type_id">Tipe Iklan</label>
                <div class="form-input-side">
                    <select name="listing_type_id" id="listing_type_id" class="form-control @error('listing_type_id') is-invalid @enderror" required>
                        <option value="">Pilih Tipe Iklan</option>
                        @foreach($listingTypes as $type)
                            <option value="{{ $type->id }}" {{ old('listing_type_id', $listing->listing_type_id) == $type->id ? 'selected' : '' }} data-description="{{ $type->keterangan }}">
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    <div id="listing_type_description" style="margin-top: 8px; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; display: none; background: #f8fafc; padding: 10px; border-radius: 8px; border-left: 3px solid var(--primary);">
                        {{-- Akan diisi via JS --}}
                    </div>
                    @error('listing_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const typeSelect = document.getElementById('listing_type_id');
                    const typeDesc = document.getElementById('listing_type_description');

                    function updateTypeDescription() {
                        if (!typeSelect || !typeDesc) return;
                        const selectedOption = typeSelect.options[typeSelect.selectedIndex];
                        const description = selectedOption ? selectedOption.getAttribute('data-description') : '';
                        
                        if (description && description.trim() !== '') {
                            typeDesc.innerHTML = description;
                            typeDesc.style.display = 'block';
                        } else {
                            typeDesc.style.display = 'none';
                        }
                    }

                    if (typeSelect) {
                        typeSelect.addEventListener('change', updateTypeDescription);
                        updateTypeDescription(); // Initial call
                    }
                });
            </script>

            <div class="form-group-horizontal">
                <label for="title">Judul</label>
                <div class="form-input-side">
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $listing->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div style="margin-top: 8px;">
                        <a href="#" onclick="document.getElementById('modalContohJudul').style.display='flex'; return false;" style="font-size: 0.82rem; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fa-solid fa-lightbulb"></i> Lihat contoh judul iklan
                        </a>
                    </div>
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="description">Konten</label>
                <div class="form-input-side">
                    <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Isikan detail dari apa yang Anda iklankan/umumkan." required maxlength="{{ $listing->is_premium ? get_setting('huruf_deskripsi_iklan_premium', 2000) : get_setting('huruf_deskripsi_iklan', 100) }}">{{ old('description', $listing->description) }}</textarea>
                    <small class="text-muted" style="display: block; margin-top: 5px;">Maksimal {{ $listing->is_premium ? get_setting('huruf_deskripsi_iklan_premium', 2000) : get_setting('huruf_deskripsi_iklan', 100) }} huruf.@if(!$listing->is_premium) Upgrade ke premium untuk tambahan hingga {{ get_setting('huruf_deskripsi_iklan_premium', 2000) }} huruf.@endif</small>
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
                        Unggah foto tambahan untuk galeri. Jatah sisa: <strong>{{ ($listing->is_premium ? get_setting('max_foto_iklan_premium', 12) : get_setting('max_foto_iklan', 4)) - $galleryPhotos->count() }}</strong> foto.
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
                <label>#Hashtag</label>
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
                        $currentTags = $listing->tags->pluck('name')->toArray();
                        $initialValue = old('tags', implode(',', $currentTags));
                    @endphp
                    <input name="tags" id="tags-tagify" class="form-control" placeholder="isikan tag tanpa tanda #...." value="{{ $initialValue }}">
                    
                    <small id="tag-info" style="color: var(--text-muted); display: block; margin-top: 8px;">
                        Masukkan tag sesuai yang Anda butuhkan, pisahkan setiap tag dengan tanda koma.
                    </small>
                    @error('tags')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror

                    <script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const input = document.querySelector('#tags-tagify');
                            const whitelist = @json($categories->pluck('name'));
                            
                            const tagify = new Tagify(input, {
                                whitelist: whitelist,
                                maxTags: {{ $listing->is_premium ? get_setting('max_category_premium', 10) : get_setting('max_category', 3) }},
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

            <div class="form-group-horizontal">
                <label for="whatsapp_visibility">Bagaimana pengunjung menghubungi Anda dengan WA?</label>
                <div class="form-input-side">
                    <select name="whatsapp_visibility" id="whatsapp_visibility" class="form-control">
                        <option value="2" {{ old('whatsapp_visibility', $listing->whatsapp_visibility) == 2 ? 'selected' : '' }}>Semua orang bisa kirim WA ke saya</option>
                        <option value="1" {{ old('whatsapp_visibility', $listing->whatsapp_visibility) == 1 ? 'selected' : '' }}>Hanya yang sudah login yang bisa kirim WA ke saya</option>
                        <option value="0" {{ old('whatsapp_visibility', $listing->whatsapp_visibility) == 0 ? 'selected' : '' }}>Kirim WA melalui admin saja</option>
                    </select>
                    <small class="text-muted">Pilih preferensi bagaimana pengunjung dapat menghubungi Anda melalui WhatsApp.</small>
                </div>
            </div>

            <div class="form-group-horizontal">
                <label>Kolom Komentar</label>
                <div class="form-input-side">
                    <select name="comment_visibility" class="form-control">
                        <option value="1" {{ old('comment_visibility', $listing->comment_visibility) == 1 ? 'selected' : '' }}>Aktifkan</option>
                        <option value="0" {{ old('comment_visibility', $listing->comment_visibility) == 0 ? 'selected' : '' }}>Nonaktifkan</option>
                    </select>
                    <small class="text-muted">Pilih apakah pengunjung bisa meninggalkan komentar.</small>
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

@section('scripts')

{{-- ===== Modal Contoh Judul ===== --}}
<div id="modalContohJudul" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.55); align-items:center; justify-content:center; padding:20px;" onclick="if(event.target===this) this.style.display='none'">
    <div style="background:var(--surface, #fff); border-radius:16px; max-width:600px; width:100%; max-height:80vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation: modalIn .2s ease;">
        <div style="padding:20px 24px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#fff; z-index:10;">
            <h3 style="margin:0; font-size:1.1rem;"><i class="fa-solid fa-lightbulb" style="color:#f59e0b; margin-right:8px;"></i>Contoh Judul Iklan</h3>
            <button onclick="document.getElementById('modalContohJudul').style.display='none'" style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1.5rem;">&times;</button>
        </div>
        <div style="padding:20px 24px;">
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px;">Klik pada contoh untuk menggunakan judul tersebut.</p>
            <div style="display:flex; flex-direction:column; gap:24px;">
                <section>
                    <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--primary); margin-bottom:12px; border-bottom:2px solid var(--primary)22; padding-bottom:5px;">Kendaraan</h4>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div onclick="setJudul('Honda Vario 150 2022 Mulus Pajak Hidup')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Honda Vario 150 2022 Mulus Pajak Hidup</div>
                        <div onclick="setJudul('Toyota Avanza G 2018 Manual Putih Batam Kota')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Toyota Avanza G 2018 Manual Putih Batam Kota</div>
                    </div>
                </section>
                <section>
                    <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--primary); margin-bottom:12px; border-bottom:2px solid var(--primary)22; padding-bottom:5px;">Elektronik</h4>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div onclick="setJudul('iPhone 13 Pro 256GB Graphite Fullset iBox')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">iPhone 13 Pro 256GB Graphite Fullset iBox</div>
                        <div onclick="setJudul('MacBook Air M1 2020 Space Gray Like New')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">MacBook Air M1 2020 Space Gray Like New</div>
                    </div>
                </section>
                <section>
                    <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--primary); margin-bottom:12px; border-bottom:2px solid var(--primary)22; padding-bottom:5px;">Properti</h4>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div onclick="setJudul('Disewakan Rumah 3KT di Orchid Park Batam Centre')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Disewakan Rumah 3KT di Orchid Park Batam Centre</div>
                        <div onclick="setJudul('Dijual Tanah Kavling 200m2 Hook Baloi View')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Dijual Tanah Kavling 200m2 Hook Baloi View</div>
                    </div>
                </section>
                <section>
                    <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--primary); margin-bottom:12px; border-bottom:2px solid var(--primary)22; padding-bottom:5px;">Lowongan / Jasa</h4>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div onclick="setJudul('Lowongan Admin Toko Wanita Berpengalaman - Nagoya')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Lowongan Admin Toko Wanita Berpengalaman - Nagoya</div>
                        <div onclick="setJudul('Jasa Service AC Panggilan Bergaransi Batam')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Jasa Service AC Panggilan Bergaransi Batam</div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes modalIn {
    from { opacity:0; transform:translateY(-16px) scale(.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
</style>

<script>
function selectTipeIklan(id) {
    document.getElementById('modalTipeIklan').style.display = 'none';
}

function setJudul(text) {
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.value = text;
        titleInput.focus();
    }
    document.getElementById('modalContohJudul').style.display = 'none';
}
</script>
@endsection
