{{--
    Partial form listing, bisa digunakan untuk create & edit
    Parameter yang Wajib:
    - $form_action  : url action form
    - $form_method  : 'POST' / 'PUT'
    - $submit_label : Label button submit
    - $listing      : null untuk create, model Listing untuk edit
    - $categories, $tags, $districts, $subdistricts, ... lainnya
    - Optional: $premiumRequest dll
--}}

<form action="{{ $form_action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($form_method === 'PUT')
        @method('PUT')
    @endif
    {{--
        Tempel kode form utama dari create.blade.php,
        Lalu ganti old('xx') -> old('xx', $listing->xx ?? null)
        dan required khusus (misal required foto saat create saja)
        Section info, errors dsb biarkan parent yang handle
    --}}
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

        const charCounter = document.getElementById('description-char-count');

        function updateCharCount() {
            if (descriptionTextarea && charCounter) {
                const currentVal = descriptionTextarea.value.length;
                const currentLimit = descriptionTextarea.maxLength || charLimitStandard;
                charCounter.textContent = `${currentVal}/${currentLimit}`;
                
                if (currentVal >= currentLimit) {
                    charCounter.style.color = '#ef4444';
                } else if (currentVal >= currentLimit * 0.9) {
                    charCounter.style.color = '#f97316';
                } else {
                    charCounter.style.color = 'var(--text-muted)';
                }
            }
        }

        if (descriptionTextarea) {
            descriptionTextarea.addEventListener('input', updateCharCount);
        }

        function updateFormState() {
            const selectedPackage = document.querySelector('input[name="ad_package"]:checked')?.value;
            const isPremium = selectedPackage === 'premium';


            // Update description limit
            if (descriptionTextarea) {
                const currentLimit = isPremium ? charLimitPremium : charLimitStandard;
                descriptionTextarea.maxLength = currentLimit;
                updateCharCount();
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

<!-- Section 1: Informasi Wajib (Mandatori) -->
<div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-top: 0; margin-bottom: 20px; color: var(--text); border-bottom: 2px solid var(--primary); padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
        <span style="background: var(--primary); color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;"><i class="fa-solid fa-star"></i></span>
        Informasi Utama (Wajib)
    </h3>

    <div class="form-group-horizontal" style="margin-top: 25px;">
        <label for="title">Nama Usaha (boleh nama pribadi) <span style="color: #ef4444;">*</span></label>
        <div class="form-input-side">
            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $listing->title ?? '') }}" placeholder="Contoh: Bengkel Motor Berkah Jaya" required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-group-horizontal">
        <label for="description">Keterangan Usaha <span style="color: #ef4444;">*</span></label>
        <div class="form-input-side">
            <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Jelaskan mengenai usaha Anda, produk/jasa yang ditawarkan, jam operasional, dll." required maxlength="{{ $listing->is_premium ? get_setting('huruf_deskripsi_iklan_premium', 2000) : get_setting('huruf_deskripsi_iklan', 100) }}">{{ old('description', $listing->description ?? '') }}</textarea>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 5px;">
                <small class="text-muted" style="margin: 0;">Jelaskan detail produk/jasa, jam operasional, dll.</small>
                <small class="text-muted" id="description-char-count" style="font-weight: 600; font-size: 0.85rem; margin: 0;">0/{{ $listing->is_premium ? get_setting('huruf_deskripsi_iklan_premium', 2000) : get_setting('huruf_deskripsi_iklan', 100) }}</small>
            </div>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-group-horizontal">
        <label for="foto_fitur">Foto Fitur <span style="color: #ef4444;">*</span></label>
        <div class="form-input-side">
            <input type="file" name="foto_fitur" id="foto_fitur" class="form-control @error('foto_fitur') is-invalid @enderror" accept="image/*" {{ isset($listing) && $listing ? '' : 'required' }}>
            <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                Pilih foto fitur utama (akan muncul di daftar pencarian). <strong style="color: #ef4444;">Wajib diunggah.</strong>
            </small>
            @error('foto_fitur')
                <div class="invalid-feedback" style="display: block;">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-group-horizontal">
        <label for="address">Alamat <span style="color: #ef4444;">*</span></label>
        <div class="form-input-side">
            <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" placeholder="Contoh: Jl. Sudirman No. 12, Ruko Citra Mas" value="{{ old('address', $listing->address ?? '') }}" required>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @guest
    <div class="form-group-horizontal">
        <label for="whatsapp_number">Nomor WhatsApp <span style="color: #ef4444;">*</span></label>
        <div class="form-input-side">
            <input type="text" name="whatsapp_number" id="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', $listing->whatsapp_number ?? '') }}" placeholder="Contoh: 0812xxxx (tanpa spasi)" required>
            <small style="color: var(--text-muted); display: block; margin-top: 8px;">
                Nomor WhatsApp ini sangat diperlukan sebagai nomor yang akan dikontak pelanggan dan nomor ini juga digunakan untuk mengirimkan kode aktivasi dan login ke dasbor member.
            </small>
            @error('whatsapp_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    @endguest
</div>

<!-- Section 2: Informasi Pendukung (Opsional) -->
<div style="background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-top: 0; margin-bottom: 20px; color: var(--text); border-bottom: 2px solid #cbd5e1; padding-bottom: 8px; display: flex; align-items: center; gap: 8px;">
        <span style="background: #64748b; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;"><i class="fa-solid fa-circle-plus"></i></span>
        Informasi Pendukung (Opsional)
    </h3>

    <div class="form-group-horizontal" style="margin-top: 15px;">
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

            <input name="tags" id="tags-tagify" class="form-control" placeholder="isikan tag tanpa tanda #...." value="{{ old('tags', $listing->tags->pluck('name')->implode(',') ?? '') }}">
            
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
                    const whitelist = @json($tags->pluck('name'));
                    
                    window.tagifyInstance = new Tagify(input, {
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
        <label for="parent_category_id">Kategori Utama</label>
        <div class="form-input-side">
            <select id="parent_category_id" class="form-control" style="height: 48px; border-radius: 8px;">
                <option value="">Pilih Kategori Utama</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('parent_category_id', $listing->categories->first() ? ($listing->categories->first()->parent_id ?: $listing->categories->first()->id) : '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group-horizontal">
        <label for="category_id">Sub Kategori</label>
        <div class="form-input-side">
            <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" style="height: 48px; border-radius: 8px;" disabled>
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
                const oldParent = @json(old('parent_category_id', $listing->categories->first() ? ($listing->categories->first()->parent_id ?: $listing->categories->first()->id) : ''));
                const oldChild = @json(old('category_id', $listing->categories->first() && $listing->categories->first()->parent_id ? $listing->categories->first()->id : ''));

                if (oldParent) {
                    updateChildCategories(oldParent, oldChild);    
                }
            }
        });
    </script>

    <div class="form-group-horizontal">
        <label for="galeri">Galeri Foto</label>
        <div class="form-input-side">
            <input type="file" name="galeri[]" id="galeri" class="form-control @error('galeri') is-invalid @enderror" multiple accept="image/*">
            <small id="galeri-info" style="color: var(--text-muted); display: block; margin-top: 8px;">
                Maksimal <strong>{{ $listing->is_premium ? get_setting('max_foto_iklan_premium', 12) : get_setting('max_foto_iklan', 4) }}</strong> foto. Format: <strong>{{ strtoupper(str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp'))) }}</strong>. Ukuran maks: <strong>{{ get_setting('max_image_size', 2048) / 1024 }}MB</strong> per foto.
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
            <select name="district_id" id="district_id" class="form-control @error('district_id') is-invalid @enderror">
                <option value="">Pilih Kecamatan</option>
                @foreach($districts as $dist)
                    <option value="{{ $dist->id }}" {{ old('district_id', $listing->district_id ?? '') == $dist->id ? 'selected' : '' }}>{{ $dist->name }}</option>
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
            <select name="subdistrict_id" id="subdistrict_id" class="form-control @error('subdistrict_id') is-invalid @enderror">
                <option value="">Pilih Kelurahan</option>
            </select>
            @error('subdistrict_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const districtSelect = document.getElementById('district_id');
        const subdistrictSelect = document.getElementById('subdistrict_id');
        const subdistricts = @json($subdistricts);
        const oldSubdistrictId = "{{ old('subdistrict_id', $listing->subdistrict_id ?? '') }}";

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

        // Populate on load if old value exists
        if (districtSelect.value) {
            populateSubdistricts();
        }
    });
</script>

</form>