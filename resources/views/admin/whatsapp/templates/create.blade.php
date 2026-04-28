@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Tambah Template WA</h1>
    <p style="color: var(--text-muted);">Buat template pesan baru untuk mempercepat pengiriman WhatsApp.</p>
</div>

<div class="glass" style="padding: 40px; border-radius: var(--radius); max-width: 800px;">
    <form action="{{ route('admin.wa_templates.store') }}" method="POST">
        @csrf
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 0.95rem; color: var(--text);">Nama Template</label>
            <input type="text" name="name" placeholder="Contoh: Tagihan Iklan, Verifikasi Akun, dll" required 
                style="width: 100%; padding: 15px; border-radius: 10px; border: 2px solid #cbd5e1; background: #f8fafc; color: var(--text); font-size: 1rem; outline: none; transition: all 0.3s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);"
                onfocus="this.style.borderColor='var(--primary)'; this.style.background='white';"
                onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
            <small style="display: block; margin-top: 8px; color: var(--text-muted);">Nama ini hanya untuk identitas di dashboard.</small>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 0.95rem; color: var(--text);">Isi Pesan Template</label>
            <textarea name="content" rows="8" placeholder="Tulis isi pesan template Anda di sini..." required 
                style="width: 100%; padding: 15px; border-radius: 10px; border: 2px solid #cbd5e1; background: #f8fafc; color: var(--text); font-size: 1rem; resize: vertical; outline: none; transition: all 0.3s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);"
                onfocus="this.style.borderColor='var(--primary)'; this.style.background='white';"
                onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';"></textarea>
            <small style="display: block; margin-top: 8px; color: var(--text-muted);">Pesan ini akan disalin ke kotak pesan saat Anda memilih template ini.</small>
        </div>

        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary" style="padding: 15px 35px; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-save"></i> Simpan Template
            </button>
            <a href="{{ route('admin.wa_templates') }}" class="btn" style="padding: 15px 25px; border-radius: 10px; background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">Batal</a>
        </div>
    </form>
</div>
@endsection
