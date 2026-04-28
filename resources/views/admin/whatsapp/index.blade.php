@extends('admin.layout')

@section('admin_content')
<div class="dashboard-header">
    <div>
        <h1>Kirim Pesan WhatsApp</h1>
        <p style="color: var(--text-muted);">Kirim pesan WhatsApp dari nomor admin.</p>
    </div>
    <a href="{{ route('admin.whatsapp.history') }}" class="btn btn-secondary">
        <i class="fa-solid fa-clock-rotate-left"></i> Lihat Riwayat
    </a>
</div>

<div class="glass" style="padding: 40px; border-radius: var(--radius); max-width: 800px; margin: 0 auto;">
    <h2 style="font-size: 1.2rem; margin-bottom: 30px; display: flex; align-items: center; gap: 12px;">
        <i class="fa-brands fa-whatsapp" style="color: #25D366; font-size: 1.5rem;"></i> Form Pesan Baru
    </h2>

    <form action="{{ route('admin.send_wa') }}" method="POST">
        @csrf
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 0.95rem;">Nomor WhatsApp Tujuan</label>
            <input type="text" name="phone" placeholder="Contoh: 081234567890" required 
                value="{{ request('phone') }}"
                class="form-control"
                style="padding: 15px; font-size: 1.1rem;">
            <small style="display: block; margin-top: 8px; color: var(--text-muted);">Gunakan format angka saja (08xx atau 62xx).</small>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 0.95rem;">Pilih Template (Opsional)</label>
            <select id="templateSelector" class="form-control" style="padding: 12px; height: auto; font-size: 1rem;">
                <option value="">-- Pilih Template Pesan --</option>
                @foreach($templates as $template)
                    <option value="{{ $template->content }}">{{ $template->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 35px;">
            <label style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 0.95rem;">Isi Pesan</label>
            <textarea id="waMessage" name="message" rows="8" placeholder="Tulis pesan Anda di sini..." required 
                class="form-control"
                style="padding: 15px; font-size: 1rem; height: auto;"></textarea>
        </div>

        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary" style="padding: 15px 35px; border-radius: 10px; font-weight: 700; flex: 1; display: flex; align-items: center; justify-content: center; gap: 12px; font-size: 1.1rem;">
                <i class="fa-solid fa-paper-plane"></i> Kirim Pesan Sekarang
            </button>
            <a href="{{ route('admin.whatsapp.history') }}" class="btn btn-secondary" style="padding: 15px 25px; border-radius: 10px; font-weight: 600;">Riwayat</a>
        </div>
    </form>
</div>

<script>
    document.getElementById('templateSelector').addEventListener('change', function() {
        const content = this.value;
        if (content) {
            document.getElementById('waMessage').value = content;
        }
    });
</script>
@endsection
