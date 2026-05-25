@extends($layout)

@section($section)
    <div style="margin-bottom: 40px; text-align: center;">
        <h1 style="font-size: 2.5rem; font-weight: 700;">Formulir Pendaftaran Usaha Sebatam</h1>
        <p style="color: var(--text-muted);">Daftarkan usaha Anda di platform Sebatam.com untuk menjangkau seluruh komunitas di Batam.</p>
    </div>

    <div class="form-card" style="margin: 0 auto;">
        @if ($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; gap: 15px; align-items: flex-start; animation: slideUp 0.4s ease-out;">
                <div style="font-size: 1.5rem; line-height: 1; color: #ef4444;"><i class="fa-solid fa-circle-exclamation"></i></div>
                <div>
                    <p style="font-weight: 700; margin: 0 0 8px 0; font-size: 1.05rem;">Pendaftaran Gagal Disimpan:</p>
                    <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem; line-height: 1.6; color: #7f1d1d;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        @guest
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 18px; border-radius: 12px; margin-bottom: 30px; display: flex; gap: 15px; align-items: flex-start; line-height: 1.5;">
            <div style="font-size: 1.4rem; color: #3b82f6; line-height: 1;"><i class="fa-solid fa-circle-info"></i></div>
            <div>
                <p style="font-weight: 700; margin: 0 0 4px 0; font-size: 0.95rem;">Informasi Aktivasi Usaha</p>
                <p style="margin: 0; font-size: 0.88rem; color: #1e3a8a;">
                    Setelah selesai menyimpan data, Anda akan langsung diberikan <strong>Kode Aktivasi (nomor unik)</strong> untuk menerbitkan iklan usaha Anda. <strong>Mohon jangan lupakan nomor tersebut</strong> agar iklan Anda dapat diaktifkan melalui WhatsApp Bot kami.
                </p>
            </div>
        </div>
        @endguest

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

            <div class="form-group-horizontal" style="margin-top: 25px;">
                <label for="title">Nama Usaha (boleh nama pribadi)</label>
                <div class="form-input-side">
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Bengkel Motor Berkah Jaya" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group-horizontal">
                <label for="description">Keterangan Usaha</label>
                <div class="form-input-side">
                    <textarea name="description" id="description" rows="6" class="form-control @error('description') is-invalid @enderror" placeholder="Jelaskan mengenai usaha Anda, produk/jasa yang ditawarkan, jam operasional, dll." required maxlength="{{ get_setting('huruf_deskripsi_iklan', 100) }}">{{ old('description') }}</textarea>
                    <small class="text-muted">Maksimal {{ get_setting('huruf_deskripsi_iklan', 100) }} huruf.</small>
                    @error('description')
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
                <label for="district_id">Kecamatan (opsional)</label>
                <div class="form-input-side">
                    <select name="district_id" id="district_id" class="form-control @error('district_id') is-invalid @enderror">
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
                <label for="subdistrict_id">Kelurahan (opsional)</label>
                <div class="form-input-side">
                    <select name="subdistrict_id" id="subdistrict_id" class="form-control @error('subdistrict_id') is-invalid @enderror">
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
                            const whitelist = @json($tags->pluck('name'));
                            
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
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 10px; color: var(--primary-dark);"><i class="fa-solid fa-shield-check"></i> Nomor WA untuk Aktivasi</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 20px;">
                    Nomor WhatsApp ini digunakan untuk mengirimkan kode aktivasi dan login ke dasbor member di masa depan.
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
            </div>
            @endguest

            <div style="display: flex; gap: 15px; margin-top: 40px; justify-content: flex-end;">
                <a href="{{ route('home') }}" class="btn btn-outline" style="padding: 12px 30px;">Batal</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Terbitkan Iklan</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            // Populate on load if old value exists
            if (districtSelect.value) {
                populateSubdistricts();
            }
        });
    </script>
@endsection

