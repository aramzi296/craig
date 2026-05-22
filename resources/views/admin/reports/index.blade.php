@extends('admin.layout')

@section('admin_content')
<div class="dashboard-header">
    <div>
        <h1>Laporan Iklan</h1>
        <p style="color: var(--text-muted);">Kelola dan tinjau laporan dari pengunjung mengenai iklan yang bermasalah.</p>
    </div>
</div>

<div class="glass" style="padding: 20px; border-radius: var(--radius); margin-bottom: 30px;">
    <form action="{{ route('admin.reports') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Cari Laporan</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari alasan, keterangan, whatsapp, judul iklan..." class="form-control" style="padding: 10px 15px;">
        </div>
        
        <div style="width: 180px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Status</label>
            <select name="status" class="form-control" style="padding: 10px 15px;">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Selesai</option>
                <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Diabaikan</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Filter</button>
            <a href="{{ route('admin.reports') }}" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center; padding: 10px 20px;">Reset</a>
        </div>
    </form>
</div>

<div class="glass" style="padding: 30px; border-radius: var(--radius); overflow-x: auto;">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align: left; padding: 12px 15px;">Iklan Yang Dilaporkan</th>
                <th style="text-align: left; padding: 12px 15px;">Pelapor</th>
                <th style="text-align: left; padding: 12px 15px;">Alasan & Keterangan</th>
                <th style="text-align: left; padding: 12px 15px;">Tanggal Laporan</th>
                <th style="text-align: left; padding: 12px 15px;">Status</th>
                <th style="text-align: right; padding: 12px 15px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;">
                <td style="padding: 15px;">
                    @if($report->listing)
                        <div style="font-weight: 600; font-size: 0.95rem; color: var(--text);">
                            {{ $report->listing->title }}
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center; margin-top: 5px;">
                            <span class="badge {{ $report->listing->is_active ? 'badge-success' : 'badge-pending' }}" style="font-size: 0.7rem; padding: 2px 6px;">
                                {{ $report->listing->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <a href="{{ route('listings.show', ['slug' => $report->listing->slug, 'code' => $report->listing->activation_code]) }}" target="_blank" style="font-size: 0.8rem; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Iklan
                            </a>
                        </div>
                    @else
                        <span style="color: var(--text-muted); font-style: italic;">[Iklan sudah dihapus]</span>
                    @endif
                </td>
                <td style="padding: 15px;">
                    @if($report->user)
                        <div style="font-weight: 600; font-size: 0.9rem;">{{ $report->user->name }}</div>
                        <div style="font-size: 0.75rem; color: #0369a1; background: #e0f2fe; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 3px; font-weight: 600;">
                            <i class="fa-solid fa-user-shield" style="font-size: 0.65rem;"></i> Anggota
                        </div>
                    @else
                        <div style="font-weight: 600; font-size: 0.9rem; color: var(--text);">Tamu</div>
                        <div style="font-size: 0.75rem; color: #475569; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 3px; font-weight: 600;">
                            <i class="fa-solid fa-user-clock" style="font-size: 0.65rem;"></i> Pengunjung
                        </div>
                    @endif
                    @if($report->reporter_whatsapp)
                        <div style="margin-top: 5px; font-size: 0.82rem;">
                            <a href="https://wa.me/{{ $report->reporter_whatsapp }}" target="_blank" style="color: #22c55e; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-weight: 500;">
                                <i class="fa-brands fa-whatsapp"></i> {{ $report->reporter_whatsapp }}
                            </a>
                        </div>
                    @endif
                </td>
                <td style="padding: 15px; max-width: 300px;">
                    @php
                        $reasonColors = [
                            'Penipuan' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                            'Spam / Duplikat' => ['bg' => '#ffedd5', 'text' => '#9a3412'],
                            'Konten Tidak Layak' => ['bg' => '#f3e8ff', 'text' => '#6b21a8'],
                            'Usaha Sudah Tutup' => ['bg' => '#f1f5f9', 'text' => '#475569'],
                            'Lainnya' => ['bg' => '#e0f2fe', 'text' => '#0369a1'],
                        ];
                        $color = $reasonColors[$report->reason] ?? ['bg' => '#e2e8f0', 'text' => '#1e293b'];
                    @endphp
                    <span style="background: {{ $color['bg'] }}; color: {{ $color['text'] }}; padding: 4px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; display: inline-block;">
                        {{ $report->reason }}
                    </span>
                    @if($report->description)
                        <p style="margin: 8px 0 0 0; font-size: 0.85rem; color: var(--text-muted); font-style: italic; line-height: 1.4; background: rgba(0,0,0,0.02); padding: 8px; border-radius: 6px; border-left: 2px solid var(--border);">
                            "{{ $report->description }}"
                        </p>
                    @endif
                </td>
                <td style="padding: 15px; font-size: 0.85rem; color: var(--text-muted);">
                    {{ $report->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                </td>
                <td style="padding: 15px;">
                    @if($report->status === 'pending')
                        <span style="background: #fef3c7; color: #d97706; border: 1px solid #fde68a; padding: 4px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; display: inline-block;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 0.7rem; margin-right: 3px;"></i> Pending
                        </span>
                    @elseif($report->status === 'resolved')
                        <span style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; padding: 4px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; display: inline-block;">
                            <i class="fa-solid fa-circle-check" style="font-size: 0.7rem; margin-right: 3px;"></i> Selesai
                        </span>
                    @else
                        <span style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; display: inline-block;">
                            <i class="fa-solid fa-circle-xmark" style="font-size: 0.7rem; margin-right: 3px;"></i> Diabaikan
                        </span>
                    @endif
                </td>
                <td style="padding: 15px; text-align: right;">
                    <div class="dropdown" style="display: inline-block; position: relative;">
                        <button onclick="toggleDropdown(event, 'dropdown-{{ $report->id }}')" class="btn btn-secondary" style="padding: 8px 12px; font-size: 0.82rem; border-radius: 8px; background: white; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            Aksi <i class="fa-solid fa-chevron-down" style="font-size: 0.65rem;"></i>
                        </button>
                        <div id="dropdown-{{ $report->id }}" class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 12px; border: 1px solid #f1f5f9; z-index: 100; margin-top: 5px; padding: 8px 0; text-align: left;">
                            
                            @if($report->status === 'pending')
                                <form action="{{ route('admin.reports.resolve', $report->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #166534; cursor: pointer; font-size: 0.88rem; font-family: inherit; font-weight: 500;">
                                        <i class="fa-solid fa-check" style="width: 16px; color: #22c55e;"></i> Selesaikan
                                    </button>
                                </form>

                                <form action="{{ route('admin.reports.dismiss', $report->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; cursor: pointer; font-size: 0.88rem; font-family: inherit; font-weight: 500;">
                                        <i class="fa-solid fa-ban" style="width: 16px; color: #64748b;"></i> Abaikan
                                    </button>
                                </form>
                            @endif

                            @if($report->listing)
                                <a href="{{ route('admin.listings.edit', $report->listing->id) }}" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #0ea5e9; text-decoration: none; font-size: 0.88rem; font-weight: 500;">
                                    <i class="fa-solid fa-pen-to-square" style="width: 16px; color: #0ea5e9;"></i> Kelola Iklan
                                </a>
                            @endif

                            @if($report->reporter_whatsapp)
                                <a href="https://wa.me/{{ $report->reporter_whatsapp }}" target="_blank" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #22c55e; text-decoration: none; font-size: 0.88rem; font-weight: 500;">
                                    <i class="fa-brands fa-whatsapp" style="width: 16px; color: #22c55e;"></i> Hubungi Pelapor
                                </a>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                    <div style="font-size: 2.5rem; margin-bottom: 15px; color: #cbd5e1;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div style="font-weight: 600; font-size: 1.1rem; color: var(--text);">Belum ada laporan</div>
                    <div style="font-size: 0.9rem; margin-top: 5px;">Semua laporan iklan dari pengunjung akan tampil di halaman ini.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($reports->hasPages())
        <div style="margin-top: 30px;">
            {{ $reports->links('vendor.pagination.simple-custom') }}
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
