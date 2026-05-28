@extends('admin.layout')

@section('admin_content')
<div class="dashboard-header">
    <div>
        <h1>Kelola Usaha</h1>
        <p style="color: var(--text-muted);">Kelola semua usaha yang terdaftar di {{ config('app.name') }}.</p>
    </div>
    <a href="{{ route('admin.listings.create') }}" class="btn btn-primary">+ Usaha Baru</a>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {!! session('success') !!}
    </div>
@endif

<div class="glass" style="padding: 20px; border-radius: var(--radius); margin-bottom: 30px;">
    <form action="{{ route('admin.listings') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Cari Usaha</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Judul, deskripsi, atau lokasi..." class="form-control" style="padding: 10px 15px;">
        </div>
        
        <div style="width: 150px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Status</label>
            <select name="status" class="form-control" style="padding: 10px 15px;">
                <option value="">Semua Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.listings') }}" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center;">Reset</a>
        </div>
    </form>
</div>

<div class="glass" style="padding: 30px; border-radius: var(--radius);">
    <table class="data-table">
        <thead>
            <tr>
                <th>Usaha</th>
                <th>Pemilik</th>
                <th>Kategori</th>
                <th style="text-align:center;">Rank</th>
                <th style="text-align:center;">Expire</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listings as $listing)
            <tr>
                <td>
                    <div style="font-weight: 600;">{{ $listing->title }}</div>
                    @if($listing->district)
                        <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fa-solid fa-location-dot"></i> {{ $listing->district->name }}</div>
                    @endif
                    @if($listing->activation_code)
                        <div style="font-size: 0.75rem; background: #fff7ed; color: #9a3412; display: inline-block; padding: 2px 6px; border-radius: 4px; margin-top: 4px; font-weight: 700; border: 1px solid #ffedd5;">
                            <i class="fa-solid fa-key" style="font-size: 0.65rem;"></i> {{ $listing->activation_code }}
                        </div>
                    @endif
                </td>
                <td>
                    <div style="font-weight: 600;">{{ $listing->user->name }}</div>
                    @if($listing->user->whatsapp)
                        <a href="https://wa.me/{{ $listing->user->whatsapp }}" target="_blank" rel="noopener noreferrer"
                           style="font-size: 0.78rem; color: #16a34a; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 2px;"
                           title="Chat WhatsApp {{ $listing->user->name }}">
                            <i class="fa-brands fa-whatsapp"></i>
                            {{ $listing->user->whatsapp }}
                        </a>
                    @endif
                </td>
                <td>
                    @php
                        $cat = $listing->categories->first();
                        $parentCatName = $cat ? ($cat->parent ? $cat->parent->name : $cat->name) : '-';
                        $subCatName = ($cat && $cat->parent) ? $cat->name : null;
                    @endphp
                    <div style="font-weight: 600;">{{ $parentCatName }}</div>
                    @if($subCatName)
                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                            {{ $subCatName }}
                        </div>
                    @endif
                </td>

                <td style="text-align:center;">
                    @php
                        $rank = $listing->listing_rank ?? 0;
                        $rankColor = $rank <= 100 ? '#f59e0b' : ($rank <= 500 ? '#0ea5e9' : '#94a3b8');
                    @endphp
                    <span style="display:inline-block; background: {{ $rankColor }}22; color: {{ $rankColor }}; border: 1px solid {{ $rankColor }}44; border-radius: 6px; padding: 3px 10px; font-weight: 700; font-size: 0.82rem;">
                        #{{ number_format($rank, 0, ',', '.') }}
                    </span>
                </td>
                <td style="text-align:center; font-size: 0.82rem;">
                    @if($listing->expires_at)
                        @if($listing->isExpired())
                            <span style="color:#ef4444; font-weight:600;" title="{{ $listing->expires_at->format('d/m/Y') }}">
                                <i class="fa-solid fa-circle-xmark" style="margin-right:3px;"></i>
                                {{ $listing->expires_at->format('d M Y') }}
                            </span>
                        @else
                            <span style="color:#22c55e; font-weight:600;" title="Berakhir: {{ $listing->expires_at->format('d/m/Y') }}">
                                <i class="fa-solid fa-clock" style="margin-right:3px;"></i>
                                {{ $listing->expires_at->format('d M Y') }}
                            </span>
                        @endif
                    @else
                        <span style="color:var(--text-muted);">—</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $listing->is_active ? 'badge-success' : 'badge-pending' }}">
                        {{ $listing->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td style="text-align: right;">
                    <div class="dropdown" style="display: inline-block; position: relative;">
                        <button onclick="toggleDropdown(event, 'dropdown-{{ $listing->id }}')" class="btn btn-secondary" style="padding: 8px 15px; font-size: 0.85rem; border-radius: 8px; background: white; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
                            Aksi <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem;"></i>
                        </button>
                        <div id="dropdown-{{ $listing->id }}" class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 12px; border: 1px solid #f1f5f9; z-index: 100; margin-top: 5px; padding: 8px 0; text-align: left;">
                            
                            <a href="{{ route('admin.listings.edit', $listing->id) }}" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; text-decoration: none; font-size: 0.9rem; transition: background 0.2s;">
                                <i class="fa-solid fa-pen-to-square" style="width: 16px; color: #0ea5e9;"></i> Edit Usaha
                            </a>

                            <form action="{{ route('admin.listings.toggle', $listing->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; cursor: pointer; font-size: 0.9rem; font-family: inherit;">
                                    <i class="fa-solid fa-power-off" style="width: 16px; color: {{ $listing->is_active ? '#ef4444' : '#22c55e' }};"></i>
                                    {{ $listing->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            <a href="{{ route('listings.show', ['slug' => $listing->slug, 'code' => $listing->activation_code]) }}" target="_blank" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; text-decoration: none; font-size: 0.9rem; transition: background 0.2s;">
                                <i class="fa-solid fa-eye" style="width: 16px; color: #64748b;"></i> Lihat Detail
                            </a>

                            <div style="height: 1px; background: #f1f5f9; margin: 5px 0;"></div>

                            <form action="{{ route('admin.listings.destroy', $listing->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus usaha ini?')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #ef4444; cursor: pointer; font-size: 0.9rem; font-family: inherit;">
                                    <i class="fa-solid fa-trash" style="width: 16px;"></i> Hapus Usaha
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
                    <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fa-solid fa-magnifying-glass" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    Tidak ada usaha yang ditemukan dengan kriteria tersebut.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top: 20px;">
        {{ $listings->links('vendor.pagination.simple-custom') }}
    </div>
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
    .dropdown-item:hover {
        background-color: #f8fafc !important;
        color: var(--primary) !important;
    }
</style>
@endsection
