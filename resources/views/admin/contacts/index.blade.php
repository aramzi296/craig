@extends('admin.layout')

@section('admin_content')
<div class="dashboard-header">
    <div>
        <h1>Pesan Masuk</h1>
        <p style="color: var(--text-muted);">Kelola pesan kontak yang masuk dari pengunjung melalui halaman Hubungi Kami.</p>
    </div>
</div>

<div class="glass" style="padding: 20px; border-radius: var(--radius); margin-bottom: 30px;">
    <form action="{{ route('admin.contacts') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Cari Pesan</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, nomor WhatsApp, isi pesan..." class="form-control" style="padding: 10px 15px;">
        </div>
        
        <div style="width: 180px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Status</label>
            <select name="status" class="form-control" style="padding: 10px 15px;">
                <option value="">Semua Status</option>
                <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Belum Dibaca</option>
                <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Sudah Dibaca</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Filter</button>
            <a href="{{ route('admin.contacts') }}" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center; padding: 10px 20px;">Reset</a>
        </div>
    </form>
</div>

<div class="glass" style="padding: 30px; border-radius: var(--radius); overflow-x: auto;">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align: left; padding: 12px 15px;">Pengirim</th>
                <th style="text-align: left; padding: 12px 15px; width: 45%;">Isi Pesan</th>
                <th style="text-align: left; padding: 12px 15px;">Tanggal Masuk</th>
                <th style="text-align: left; padding: 12px 15px;">Status</th>
                <th style="text-align: right; padding: 12px 15px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages as $message)
            <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s; {{ $message->status === 'unread' ? 'background: rgba(239, 68, 68, 0.02); font-weight: 500;' : '' }}">
                <td style="padding: 15px;">
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text);">
                        {{ $message->name }}
                    </div>
                    @if($message->whatsapp)
                        <div style="margin-top: 5px; font-size: 0.85rem;">
                            <a href="https://wa.me/{{ $message->whatsapp }}" target="_blank" style="color: #22c55e; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                                <i class="fa-brands fa-whatsapp"></i> {{ $message->whatsapp }}
                            </a>
                        </div>
                    @endif
                </td>
                <td style="padding: 15px; line-height: 1.5; font-size: 0.9rem; color: var(--text);">
                    <div style="white-space: pre-wrap; background: rgba(0,0,0,0.01); padding: 10px; border-radius: 8px; border: 1px solid var(--border);">{{ $message->message }}</div>
                </td>
                <td style="padding: 15px; font-size: 0.85rem; color: var(--text-muted);">
                    {{ $message->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                </td>
                <td style="padding: 15px;">
                    @if($message->status === 'unread')
                        <span style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; padding: 4px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; display: inline-block;">
                            <i class="fa-solid fa-envelope" style="margin-right: 3px;"></i> Belum Dibaca
                        </span>
                    @else
                        <span style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; display: inline-block;">
                            <i class="fa-solid fa-envelope-open" style="margin-right: 3px;"></i> Sudah Dibaca
                        </span>
                    @endif
                </td>
                <td style="padding: 15px; text-align: right;">
                    <div class="dropdown" style="display: inline-block; position: relative;">
                        <button onclick="toggleDropdown(event, 'dropdown-{{ $message->id }}')" class="btn btn-secondary" style="padding: 8px 12px; font-size: 0.82rem; border-radius: 8px; background: white; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            Aksi <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i>
                        </button>
                        <div id="dropdown-{{ $message->id }}" class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 12px; border: 1px solid #f1f5f9; z-index: 100; margin-top: 5px; padding: 8px 0; text-align: left;">
                            
                            @if($message->status === 'unread')
                                <form action="{{ route('admin.contacts.read', $message->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #0369a1; cursor: pointer; font-size: 0.88rem; font-family: inherit; font-weight: 500;">
                                        <i class="fa-solid fa-envelope-open" style="width: 16px; color: #0ea5e9;"></i> Tandai Dibaca
                                    </button>
                                </form>
                            @endif

                            @if($message->whatsapp)
                                <a href="https://wa.me/{{ $message->whatsapp }}" target="_blank" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #22c55e; text-decoration: none; font-size: 0.88rem; font-weight: 500;">
                                    <i class="fa-brands fa-whatsapp" style="width: 16px; color: #22c55e;"></i> Hubungi via WA
                                </a>
                            @endif

                            <div style="height: 1px; background: #f1f5f9; margin: 5px 0;"></div>

                            <form action="{{ route('admin.contacts.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan kontak ini?')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #ef4444; cursor: pointer; font-size: 0.88rem; font-family: inherit; font-weight: 500;">
                                    <i class="fa-solid fa-trash" style="width: 16px;"></i> Hapus Pesan
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                    <div style="font-size: 2.5rem; margin-bottom: 15px; color: #cbd5e1;">
                        <i class="fa-solid fa-envelope-open"></i>
                    </div>
                    <div style="font-weight: 600; font-size: 1.1rem; color: var(--text);">Kotak Masuk Kosong</div>
                    <div style="font-size: 0.9rem; margin-top: 5px;">Pesan masuk dari pengunjung via formulir Hubungi Kami akan tampil di sini.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($messages->hasPages())
        <div style="margin-top: 30px;">
            {{ $messages->links('vendor.pagination.simple-custom') }}
        </div>
    @endif
</div>

<script>
    function toggleDropdown(event, id) {
        event.stopPropagation();
        
        const menu = document.getElementById(id);
        const isOpen = menu.style.display === 'block';

        // Close other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m.id !== id) m.style.display = 'none';
        });

        if (!isOpen) {
            menu.style.display = 'block';
            
            // Smart flipping logic
            requestAnimationFrame(() => {
                const rect = menu.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                
                if (rect.bottom > windowHeight - 20) {
                    // Not enough space below, flip to top
                    menu.style.top = 'auto';
                    menu.style.bottom = '100%';
                    menu.style.marginBottom = '10px';
                    menu.style.marginTop = '0';
                } else {
                    menu.style.top = '100%';
                    menu.style.bottom = 'auto';
                    menu.style.marginBottom = '0';
                    menu.style.marginTop = '5px';
                }
            });
        } else {
            menu.style.display = 'none';
        }
    }

    // Close dropdowns when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.btn-secondary') && !event.target.closest('.btn-secondary')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    }
</script>

<style>
    .dropdown-item {
        transition: background 0.2s, color 0.2s;
    }
    .dropdown-item:hover {
        background-color: #f8fafc !important;
    }
    .data-table tr:hover {
        background-color: rgba(0,0,0,0.01);
    }
</style>
@endsection
