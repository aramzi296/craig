@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700;">Template WhatsApp</h1>
        <p style="color: var(--text-muted);">Kelola pesan yang sering dikirim untuk mempermudah admin.</p>
    </div>
    <a href="{{ route('admin.wa_templates.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Template Baru
    </a>
</div>

<div class="glass" style="padding: 30px; border-radius: var(--radius);">
    @if($templates->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 200px;">Nama Template</th>
                <th>Isi Pesan</th>
                <th style="width: 150px; text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($templates as $template)
            <tr>
                <td style="font-weight: 700; color: var(--primary);">{{ $template->name }}</td>
                <td>
                    <div style="font-size: 0.9rem; color: #475569; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                        {{ $template->content }}
                    </div>
                </td>
                <td style="text-align: right;">
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('admin.wa_templates.edit', $template->id) }}" class="btn btn-secondary" style="padding: 8px 12px; font-size: 0.8rem; background: #f8fafc; border: 1px solid #e2e8f0;">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <form action="{{ route('admin.wa_templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Hapus template ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn" style="padding: 8px 12px; font-size: 0.8rem; background: #fee2e2; color: #ef4444; border: 1px solid #fecaca;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
        <i class="fa-brands fa-whatsapp" style="font-size: 4rem; opacity: 0.1; margin-bottom: 20px; display: block;"></i>
        <p style="font-size: 1.1rem;">Belum ada template pesan.</p>
        <a href="{{ route('admin.wa_templates.create') }}" class="btn btn-outline" style="margin-top: 20px;">Buat Template Pertama</a>
    </div>
    @endif
</div>
@endsection
