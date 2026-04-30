@extends('admin.layout')

@section('admin_content')
<div class="dashboard-header">
    <div>
        <h1>Riwayat WhatsApp</h1>
        <p style="color: var(--text-muted);">Daftar semua pesan WhatsApp yang pernah dikirim oleh admin.</p>
    </div>
    <a href="{{ route('admin.whatsapp') }}" class="btn btn-primary">
        <i class="fa-solid fa-paper-plane"></i> Kirim Pesan Baru
    </a>
</div>

<div class="glass" style="padding: 30px; border-radius: var(--radius);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 20px;">
        <h2 style="font-size: 1.1rem; display: flex; align-items: center; gap: 10px; white-space: nowrap;">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Daftar Riwayat
        </h2>
        
        <form action="{{ route('admin.whatsapp.history') }}" method="GET" style="display: flex; gap: 10px; flex: 1; max-width: 500px;">
            <input type="text" name="search" placeholder="Cari nomor, pesan, atau catatan admin..." value="{{ request('search') }}"
                class="form-control"
                style="padding: 8px 15px; border-radius: 20px; font-size: 0.85rem;">
            <button type="submit" class="btn btn-primary" style="padding: 8px 15px; border-radius: 20px; font-size: 0.85rem;">
                <i class="fa-solid fa-search"></i>
            </button>
            @if(request('search'))
                <a href="{{ route('admin.whatsapp.history') }}" class="btn btn-secondary" style="padding: 8px 15px; border-radius: 20px; font-size: 0.85rem;">Reset</a>
            @endif
        </form>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 180px;">Tujuan & Waktu</th>
                    <th>Isi Pesan</th>
                    <th style="width: 300px;">Catatan Admin</th>
                    <th style="width: 80px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #25D366; font-size: 1rem;">{{ $log->phone }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                <i class="fa-regular fa-calendar-alt"></i> {{ $log->created_at->format('d M Y') }}
                                <br>
                                <i class="fa-regular fa-clock"></i> {{ $log->created_at->format('H:i') }}
                            </div>
                            <div style="margin-top: 8px;">
                                @if($log->status == 'sent')
                                    <span class="badge badge-success" style="font-size: 0.65rem; padding: 3px 10px; border-radius: 50px;">
                                        <i class="fa-solid fa-check"></i> Terkirim
                                    </span>
                                @else
                                    <span class="badge" style="font-size: 0.65rem; padding: 3px 10px; border-radius: 50px; background: #fee2e2; color: #991b1b;">
                                        <i class="fa-solid fa-xmark"></i> Gagal
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem; max-height: 120px; overflow-y: auto; white-space: pre-wrap; line-height: 1.6; color: #334155;">{{ $log->message }}</div>
                        </td>
                        <td>
                            <form action="{{ route('admin.whatsapp.logs.update', $log->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <textarea name="admin_notes" 
                                    class="form-control"
                                    style="padding: 10px; font-size: 0.85rem; height: auto; min-height: 80px; background: rgba(255,255,255,0.7); line-height: 1.4;"
                                    placeholder="Klik untuk menambah catatan admin..."
                                    onblur="this.form.submit()">{{ $log->admin_notes }}</textarea>
                                <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 4px; text-align: right;">
                                    Auto-save saat kursor keluar
                                </div>
                            </form>
                        </td>
                        <td>
                            <div style="display: flex; justify-content: center;">
                                <form action="{{ route('admin.whatsapp.logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat pesan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: #fee2e2; border: 1px solid #fecaca; color: #ef4444; width: 36px; height: 36px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" 
                                        onmouseover="this.style.background='#ef4444'; this.style.color='white';"
                                        onmouseout="this.style.background='#fee2e2'; this.style.color='#ef4444';"
                                        title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 60px; color: var(--text-muted);">
                            <div style="opacity: 0.4;">
                                <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                                <p style="font-size: 1.1rem; font-weight: 600;">Belum ada riwayat pesan</p>
                                <p style="font-size: 0.9rem;">Pesan yang Anda kirim akan muncul di sini.</p>
                            </div>
                            <a href="{{ route('admin.whatsapp') }}" class="btn btn-primary" style="margin-top: 20px;">Kirim Pesan Sekarang</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px;">
    <div style="margin-top: 20px;">
        {{ $logs->links('vendor.pagination.simple-custom') }}
    </div>
    </div>
</div>
@endsection
