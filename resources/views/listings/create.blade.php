@extends($layout)

@section($section)
    <div style="margin-bottom: 40px; text-align: center;">
        <h1 style="font-size: 2.5rem; font-weight: 700;">Pasang Iklan Baru</h1>
        <p style="color: var(--text-muted);">Bagikan apa yang Anda tawarkan/umumkan ke seluruh komunitas di Batam.</p>
    </div>

    <div class="form-card" style="margin: 0 auto;">
        <form action="{{ route('listings.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            @if(isset($premiumRequest) && $premiumRequest)
                <input type="hidden" name="premium_request_id" value="{{ $premiumRequest->id }}">
                <div style="background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 12px; padding: 20px; margin-bottom: 30px; display: flex; gap: 15px; align-items: center; color: #065f46;">
                    <div style="font-size: 1.5rem;"><i class="fa-solid fa-crown"></i></div>
                    <div>
                        <p style="font-weight: 700; margin-bottom: 4px;">Menggunakan Paket Premium</p>
                        <p style="font-size: 0.9rem; margin: 0;">Anda sedang menggunakan paket <strong>{{ $premiumRequest->package->name }}</strong> yang sudah Anda miliki. Iklan ini akan otomatis mendapatkan fitur premium.</p>
                    </div>
                </div>
            @endif
            
            @if(auth()->check() && auth()->user()->ads_quota <= 0)
                <div style="background: #fef2f2; border: 1px solid #fee2e2; border-radius: 12px; padding: 20px; margin-bottom: 30px; display: flex; gap: 15px; align-items: center; color: #991b1b;">
                    <div style="font-size: 1.5rem;"><i class="fa-solid fa-circle-exclamation"></i></div>
                    <div>
                        <p style="font-weight: 700; margin-bottom: 4px;">Jatah Slot Iklan Gratis Habis</p>
                        <p style="font-size: 0.9rem; margin: 0;">Jatah slot iklan gratis Anda sudah habis. Silakan hubungi admin untuk menambah slot iklan.</p>
                    </div>
                </div>
            @endif
            
            <input type="hidden" name="ad_package" value="standard">
            {{-- 
            <div class="form-group-horizontal">
                <label>Paket Iklan</label>
                <div class="form-input-side">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        @php
                            $quotaExhausted = auth()->check() && auth()->user()->ads_quota <= 0;
                            $hasPrepaid = isset($premiumRequest) && $premiumRequest;
                        @endphp
                        <label style="display: block; cursor: {{ ($quotaExhausted || $hasPrepaid) ? 'not-allowed' : 'pointer' }}; opacity: {{ ($quotaExhausted || $hasPrepaid) ? '0.6' : '1' }};">
                            <input type="radio" name="ad_package" value="standard" {{ (old('ad_package', 'standard') == 'standard' && !$quotaExhausted && !$hasPrepaid) ? 'checked' : '' }} {{ ($quotaExhausted || $hasPrepaid) ? 'disabled' : '' }}>
                            <div style="padding: 15px; border: 2px solid var(--border); border-radius: 12px; margin-top: 5px;">
                                <div style="font-weight: 700; color: var(--text);">Standar (Gratis)</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">Gunakan kuota gratis Anda.</div>
                            </div>
                        </label>
                        <label style="display: block; cursor: pointer;">
                            <input type="radio" name="ad_package" value="premium" {{ (old('ad_package') == 'premium' || $quotaExhausted || $hasPrepaid) ? 'checked' : '' }}>
                            <div style="padding: 15px; border: 2px solid var(--primary); border-radius: 12px; margin-top: 5px; background: #f0f9ff;">
                                <div style="font-weight: 700; color: var(--primary-dark);">Premium</div>
                                <div style="font-size: 0.8rem; color: var(--primary);">Fitur lengkap & prioritas tampil.</div>
                            </div>
                        </label>
                    </div>
                    @error('ad_package')
                        <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            --}}


            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const packageRadios = document.querySelectorAll('input[name="ad_package"]');
                    const descriptionTextarea = document.getElementById('description');
                    
                    const charLimitStandard = {{ get_setting('huruf_deskripsi_iklan', 100) }};
                    const charLimitPremium = {{ get_setting('huruf_deskripsi_iklan_premium', 2000) }};
                    const maxCategoryStandard = {{ get_setting('max_category', 3) }};
                    const maxCategoryPremium = {{ get_setting('max_category_premium', 10) }};
                    const maxPhotosStandard = {{ get_setting('max_foto_iklan', 4) }};
                    const maxPhotosPremium = {{ get_setting('max_foto_iklan_premium', 12) }};
                    
                    const linkWebsite = {{ get_setting('link_website') ? 'true' : 'false' }};
                    const linkWebsitePremium = {{ get_setting('link_website_premium') ? 'true' : 'false' }};

                    function updateFormState() {
                        const selectedPackage = document.querySelector('input[name="ad_package"]:checked')?.value;
                        const isPremium = selectedPackage === 'premium';


                        // Update description limit
                        if (descriptionTextarea) {
                            const currentLimit = isPremium ? charLimitPremium : charLimitStandard;
                            descriptionTextarea.maxLength = currentLimit;
                            const small = descriptionTextarea.nextElementSibling;
                            if (small && small.tagName === 'SMALL') {
                                small.innerHTML = `Maksimal ${currentLimit} huruf. ${!isPremium ? 'Upgrade ke premium untuk tambahan hingga ' + charLimitPremium + ' huruf.' : ''}`;
                            }
                        }

                        // Update Tagify max tags
                        if (window.tagifyInstance) {
                            const currentMaxTags = isPremium ? maxCategoryPremium : maxCategoryStandard;
                            window.tagifyInstance.settings.maxTags = currentMaxTags;
                            
                            const categoryInfo = document.getElementById('tag-info');
                                 categoryInfo.innerHTML = `Masukkan tag sesuai yang Anda butuhkan, pisahkan setiap tag dengan tanda koma. (Maksimal <strong>${currentMaxTags}</strong> tag)`;
                        }

                        // Update Photo limit info
                        const photoInfo = document.getElementById('galeri-info');
                        if (photoInfo) {
                            const currentMaxPhotos = isPremium ? maxPhotosPremium : maxPhotosStandard;
                            photoInfo.innerHTML = `Maksimal <strong>${currentMaxPhotos}</strong> foto untuk paket yang dipilih.`;
                        }
                    }

                    packageRadios.forEach(radio => {
                        radio.addEventListener('change', updateFormState);
                    });
                    
                    // Delay initial check to ensure Tagify is loaded
                    setTimeout(updateFormState, 100);
                });
            </script>

            <div class="form-group-horizontal">
                <label for="listing_type_id">Tipe Iklan</label>
                <div class="form-input-side">
                    <select name="listing_type_id" id="listing_type_id" class="form-control @error('listing_type_id') is-invalid @enderror" required>
                        <option value="">Pilih Tipe Iklan</option>
                        @foreach($listingTypes as $type)
                            <option value="{{ $type->id }}" {{ old('listing_type_id') == $type->id ? 'selected' : '' }} data-description="{{ $type->keterangan }}">
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

            <div class="form-group-horizontal" style="margin-top: 25px;">
                <label for="title">Judul</label>
                <div class="form-input-side">
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Honda Vario 2022 Mulus" required>
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
                    <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Isikan detail dari apa yang Anda iklankan/umumkan." required maxlength="{{ get_setting('huruf_deskripsi_iklan', 100) }}">{{ old('description') }}</textarea>
                    <small class="text-muted">Maksimal {{ get_setting('huruf_deskripsi_iklan', 100) }} huruf.</small>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="price">Harga (Opsional)</label>
                <div class="form-input-side">
                    <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" placeholder="Contoh: 500000 (kosongkan jika tidak ada)">
                    <small class="text-muted">Kosongkan jika iklan berupa pengumuman atau informasi tanpa harga.</small>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="foto_fitur">Foto Fitur</label>
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
                    <small id="galeri-info" style="color: var(--text-muted); display: block; margin-top: 8px;">
                        Maksimal <strong>{{ get_setting('max_foto_iklan') }}</strong> foto. Format: <strong>{{ strtoupper(str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp'))) }}</strong>. Ukuran maks: <strong>{{ get_setting('max_image_size', 2048) / 1024 }}MB</strong> per foto.
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
                <label for="whatsapp_visibility">Bagaimana pengunjung menghubungi Anda dengan WA?</label>
                <div class="form-input-side">
                    <select name="whatsapp_visibility" id="whatsapp_visibility" class="form-control @error('whatsapp_visibility') is-invalid @enderror">
                        <option value="2" {{ old('whatsapp_visibility', 2) == 2 ? 'selected' : '' }}>Semua orang bisa kirim WA ke saya</option>
                        <option value="1" {{ old('whatsapp_visibility') == '1' ? 'selected' : '' }}>Hanya yang sudah login yang bisa kirim WA ke saya</option>
                        <option value="0" {{ old('whatsapp_visibility') == '0' ? 'selected' : '' }}>Kirim WA melalui admin saja</option>
                    </select>
                    @error('whatsapp_visibility')
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

                    <input name="tags" id="tags-tagify" class="form-control" placeholder="isikan tag tanpa tanda #...." value="{{ old('tags', '') }}">
                    
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
                            
                            window.tagifyInstance = new Tagify(input, {
                                whitelist: whitelist,
                                maxTags: {{ get_setting('max_category', 3) }},
                                dropdown: {
                                    maxItems: 20,
                                    classname: "tags-look",
                                    enabled: 0,
                                    closeOnSelect: true
                                }
                            });

                            // Handle max tags reached
                            tagify.on('add', function(e) {
                                if (tagify.value.length >= {{ get_setting('max_category', 3) }}) {
                                    // Optionally show some feedback
                                }
                            });
                        });
                    </script>
                </div>
            </div>




            @guest
            <div style="background: var(--primary-light, #f0f9ff); padding: 25px; border-radius: 12px; border: 1px solid var(--primary); margin: 30px 0;">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 10px; color: var(--primary-dark);"><i class="fa-solid fa-shield-check"></i> Nomor WA dan Verifikasi</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 20px;">
                    Anda bisa mengedit postingan ini nantinya dengan login ke dasbor member menggunakan nomor WA Anda.
                </p>

                <div class="form-group-horizontal">
                    <label for="whatsapp_number">Nomor WhatsApp</label>
                    <div class="form-input-side">
                        <input type="text" name="whatsapp_number" id="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number') }}" placeholder="Contoh: 0812xxxx (tanpa spasi)" required>
                        @error('whatsapp_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group-horizontal">
                    <label for="otp">Kode OTP</label>
                    <div class="form-input-side">
                        <input type="text" name="otp" id="otp" class="form-control @error('otp') is-invalid @enderror" placeholder="6 digit kode OTP" required>
                        <small style="color: var(--text-muted); display: block; margin-top: 8px; line-height: 1.5;">
                            Kirim pesan <strong style="color: var(--primary);">OTP</strong> ke nomor WhatsApp bot admin kami untuk mendapatkan kode.
                            <br>
                            <a href="https://wa.me/{{ config('services.whatsapp.bot_number') }}?text=OTP" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                <i class="fa-brands fa-whatsapp"></i> Chat Bot: {{ config('services.whatsapp.bot_number') }} (ketik OTP)
                            </a>
                        </small>
                        @error('otp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            @endguest

            <div style="display: flex; gap: 15px; margin-top: 40px; justify-content: flex-end;">
                <a href="{{ route('home') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Terbitkan Iklan</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')

<style>
@keyframes modalIn {
    from { opacity:0; transform:translateY(-16px) scale(.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
</style>

<script>

function setJudul(text) {
    const titleInput = document.getElementById('title');
    if (titleInput) {
        titleInput.value = text;
        titleInput.focus();
    }
    document.getElementById('modalContohJudul').style.display = 'none';
}
</script>

{{-- ===== Modal Contoh Judul ===== --}}
<div id="modalContohJudul" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.55); align-items:center; justify-content:center; padding:20px;" onclick="if(event.target===this) this.style.display='none'">
    <div style="background:var(--surface, #fff); border-radius:16px; max-width:600px; width:100%; max-height:80vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation: modalIn .2s ease;">
        <!-- Header -->
        <div style="padding:20px 24px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#fff; z-index:10;">
            <h3 style="margin:0; font-size:1.1rem;"><i class="fa-solid fa-lightbulb" style="color:#f59e0b; margin-right:8px;"></i>Contoh Judul Iklan</h3>
            <button onclick="document.getElementById('modalContohJudul').style.display='none'" style="background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:1.5rem;">&times;</button>
        </div>
        <!-- Body -->
        <div style="padding:20px 24px;">
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px;">Klik pada contoh untuk menggunakan judul tersebut.</p>
            
            <div style="display:flex; flex-direction:column; gap:24px;">
                <!-- Kategori Kendaraan -->
                <section>
                    <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--primary); margin-bottom:12px; border-bottom:2px solid var(--primary)22; padding-bottom:5px;">Kendaraan</h4>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div onclick="setJudul('Honda Vario 150 2022 Mulus Pajak Hidup')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Honda Vario 150 2022 Mulus Pajak Hidup</div>
                        <div onclick="setJudul('Toyota Avanza G 2018 Manual Putih Batam Kota')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Toyota Avanza G 2018 Manual Putih Batam Kota</div>
                    </div>
                </section>

                <!-- Kategori Elektronik -->
                <section>
                    <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--primary); margin-bottom:12px; border-bottom:2px solid var(--primary)22; padding-bottom:5px;">Elektronik</h4>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div onclick="setJudul('iPhone 13 Pro 256GB Graphite Fullset iBox')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">iPhone 13 Pro 256GB Graphite Fullset iBox</div>
                        <div onclick="setJudul('MacBook Air M1 2020 Space Gray Like New')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">MacBook Air M1 2020 Space Gray Like New</div>
                    </div>
                </section>

                <!-- Kategori Properti -->
                <section>
                    <h4 style="font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--primary); margin-bottom:12px; border-bottom:2px solid var(--primary)22; padding-bottom:5px;">Properti</h4>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div onclick="setJudul('Disewakan Rumah 3KT di Orchid Park Batam Centre')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Disewakan Rumah 3KT di Orchid Park Batam Centre</div>
                        <div onclick="setJudul('Dijual Tanah Kavling 200m2 Hook Baloi View')" style="padding:10px 15px; background:#f8fafc; border-radius:8px; font-size:0.9rem; cursor:pointer; border:1px solid transparent; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'; this.style.background='#fff';" onmouseout="this.style.borderColor='transparent'; this.style.background='#f8fafc';">Dijual Tanah Kavling 200m2 Hook Baloi View</div>
                    </div>
                </section>

                <!-- Lowongan Kerja -->
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
@endsection
