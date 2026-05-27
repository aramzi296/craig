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
        @include('listings._form', [
    'form_action' => route('listings.store'),
    'form_method' => 'POST',
    'submit_label' => 'Terbitkan Iklan',
    'listing' => null,
    'categories' => $categories,
    'tags' => $tags,
    'districts' => $districts,
    'subdistricts' => $subdistricts,
    'premiumRequest' => $premiumRequest ?? null
])
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

