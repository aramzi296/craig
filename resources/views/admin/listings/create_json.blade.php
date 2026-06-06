@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--text); letter-spacing: -0.025em; margin-bottom: 8px;">Listing By JSON</h1>
    <p style="color: var(--text-muted); font-size: 1.05rem;">Buat pengguna baru dan listing sekaligus menggunakan data berformat JSON.</p>
</div>

@if ($errors->any())
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; gap: 15px; align-items: flex-start; animation: slideUp 0.4s ease-out;">
        <div style="font-size: 1.5rem; line-height: 1; color: #ef4444;"><i class="fa-solid fa-circle-exclamation"></i></div>
        <div>
            <p style="font-weight: 700; margin: 0 0 8px 0; font-size: 1.05rem;">Terjadi Kesalahan:</p>
            <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem; line-height: 1.6; color: #7f1d1d;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="form-card" style="margin: 0 auto; padding: 0; border: none; background: transparent; box-shadow: none; max-width: 100%;">
    <form action="{{ route('admin.listings.json.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div style="background: #ffffff; padding: 30px; border-radius: 16px; border: 1px solid var(--border); margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <h3 style="font-size: 1.3rem; font-weight: 800; margin-top: 0; margin-bottom: 25px; color: var(--text); border-bottom: 2px solid var(--primary); padding-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                <span style="background: var(--primary); color: #fff; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.95rem;"><i class="fa-solid fa-file-code"></i></span>
                Input Data Listing
            </h3>

            <div class="form-group-horizontal" style="margin-top: 25px; display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;">
                <label for="json_data" style="font-weight: 700; color: var(--text); font-size: 0.95rem;">Payload JSON <span style="color: #ef4444;">*</span></label>
                <textarea name="json_data" id="json_data" rows="12" class="form-control @error('json_data') is-invalid @enderror" style="font-family: monospace; font-size: 0.9rem; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box; background: #f8fafc;" placeholder='{
  "judul": "Jasa Tukang Bangunan Batam",
  "nama": "Mitro Sanjay",
  "alamat": "Batam",
  "keterangan_usaha": "Melayani jasa pertukangan dan renovasi bangunan, meliputi pembangunan rumah dari nol, pembuatan dapur dan meja dapur, penyambungan rumah, pembuatan kamar mandi, pembuatan teras beserta kanopi, pembuatan septic tank, pemasangan plafon PVC, plester dan aci, serta pemasangan pipa/plumbing.",
  "nomor_wa": "6285789173762"
}' required>{{ old('json_data') }}</textarea>
                <small style="color: var(--text-muted); display: block; margin-top: 5px;">
                    Pastikan JSON menggunakan format yang valid dengan key: <code>judul</code>, <code>nama</code>, <code>alamat</code>, <code>keterangan_usaha</code>, dan <code>nomor_wa</code>.
                </small>
                @error('json_data')
                    <div class="invalid-feedback" style="display: block; color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group-horizontal" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;">
                <label for="foto" style="font-weight: 700; color: var(--text); font-size: 0.95rem;">Foto Fitur Utama <span style="color: #ef4444;">*</span></label>
                <input type="file" name="foto" id="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*" style="padding: 10px; border-radius: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box;" required>
                <small style="color: var(--text-muted); display: block; margin-top: 5px;">
                    Pilih foto fitur utama untuk listing (format: JPG, JPEG, PNG, WEBP, maks 10MB).
                </small>
                @error('foto')
                    <div class="invalid-feedback" style="display: block; color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group-horizontal" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;">
                <label for="website" style="font-weight: 700; color: var(--text); font-size: 0.95rem;">Website <span style="color: var(--text-muted); font-weight: normal;">(Opsional)</span></label>
                <input type="text" name="website" id="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website') }}" style="padding: 12px 15px; border-radius: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box;" placeholder="https://example.com">
                <small style="color: var(--text-muted); display: block; margin-top: 5px;">
                    Masukkan alamat website listing jika ada.
                </small>
                @error('website')
                    <div class="invalid-feedback" style="display: block; color: #ef4444; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 40px; justify-content: flex-end; border-top: 1px solid var(--border); padding-top: 30px;">
            <a href="{{ route('admin.listings') }}" class="btn btn-outline" style="padding: 14px 35px; border-radius: 12px; font-weight: 700; border: 1.5px solid #cbd5e1; background: transparent; color: #475569; text-decoration: none;">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding: 14px 35px; border-radius: 12px; font-weight: 700; background: var(--primary); color: white; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.25);">Simpan & Proses</button>
        </div>
    </form>
</div>
@endsection
