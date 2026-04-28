@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Kirim Pesan WhatsApp</h1>
    <p style="color: var(--text-muted);">Kirim pesan WhatsApp dari nomor admin ke nomor mana pun.</p>
</div>

<div class="glass" style="padding: 40px; border-radius: var(--radius); max-width: 800px;">
    <h2 style="font-size: 1.2rem; margin-bottom: 30px;">
        <i class="fa-brands fa-whatsapp" style="color: #25D366; margin-right: 10px;"></i> Form Pesan Baru
    </h2>

    <form action="{{ route('admin.send_wa') }}" method="POST">
        @csrf
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 0.95rem; color: var(--text);">Nomor WhatsApp Tujuan</label>
            <input type="text" name="phone" placeholder="Contoh: 081234567890" required 
                value="{{ request('phone') }}"
                style="width: 100%; padding: 15px; border-radius: 10px; border: 2px solid #cbd5e1; background: #f8fafc; color: var(--text); font-size: 1rem; outline: none; transition: all 0.3s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);"
                onfocus="this.style.borderColor='var(--primary)'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(14, 165, 233, 0.1), inset 0 2px 4px rgba(0,0,0,0.05)';"
                onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc'; this.style.boxShadow='inset 0 2px 4px rgba(0,0,0,0.05)';">
            <small style="display: block; margin-top: 8px; color: var(--text-muted);">Gunakan format angka saja (08xx atau 62xx).</small>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 0.95rem; color: var(--text);">Pilih Template (Opsional)</label>
            <select id="templateSelector" 
                style="width: 100%; padding: 12px; border-radius: 10px; border: 2px solid #e2e8f0; background: white; font-size: 0.95rem; outline: none;">
                <option value="">-- Pilih Template Pesan --</option>
                @foreach($templates as $template)
                    <option value="{{ $template->content }}">{{ $template->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 0.95rem; color: var(--text);">Isi Pesan</label>
            <textarea id="waMessage" name="message" rows="6" placeholder="Tulis pesan Anda di sini..." required 
                style="width: 100%; padding: 15px; border-radius: 10px; border: 2px solid #cbd5e1; background: #f8fafc; color: var(--text); font-size: 1rem; resize: vertical; outline: none; transition: all 0.3s; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);"
                onfocus="this.style.borderColor='var(--primary)'; this.style.background='white'; this.style.boxShadow='0 0 0 4px rgba(14, 165, 233, 0.1), inset 0 2px 4px rgba(0,0,0,0.05)';"
                onblur="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc'; this.style.boxShadow='inset 0 2px 4px rgba(0,0,0,0.05)';"></textarea>
        </div>

        <script>
            document.getElementById('templateSelector').addEventListener('change', function() {
                const content = this.value;
                if (content) {
                    document.getElementById('waMessage').value = content;
                }
            });
        </script>

        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary" style="padding: 15px 35px; border-radius: 10px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-paper-plane"></i> Kirim Pesan Sekarang
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn" style="padding: 15px 25px; border-radius: 10px; background: rgba(255,255,255,0.1); color: white;">Batal</a>
        </div>
    </form>
</div>
@endsection
