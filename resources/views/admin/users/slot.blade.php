@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Kelola Slot Iklan Gratis</h1>
    <p style="color: var(--text-muted);">Tambah atau kurangi jatah slot iklan untuk pengguna tertentu atau semua pengguna sekaligus.</p>
</div>

{{-- Info Panel --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div class="stat-card">
        <div class="stat-label">Default Slot per User Baru</div>
        <div class="stat-value" style="color: var(--primary);">{{ get_setting('jumlah_iklan_user_default', 1) }}</div>
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">dari setting <code>jumlah_iklan_user_default</code></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Pengguna</div>
        <div class="stat-value">{{ $totalUsers }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pengguna Kehabisan Slot</div>
        <div class="stat-value" style="color: #ef4444;">{{ $zeroQuotaUsers }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Slot Tersedia</div>
        <div class="stat-value" style="color: #22c55e;">{{ $totalQuota }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start;">

    {{-- Tambah Slot ke Semua Pengguna --}}
    <div class="glass" style="padding: 30px; border-radius: var(--radius);">
        <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-users" style="color: var(--primary);"></i> Semua Pengguna
        </h2>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 25px;">
            Tambah atau kurangi slot iklan untuk <strong>seluruh</strong> pengguna terdaftar sekaligus.
        </p>

        <form action="{{ route('admin.users.slot.bulk') }}" method="POST">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Tindakan</label>
                <div style="display: flex; gap: 15px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="action" value="add" checked> Tambah Slot
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="action" value="set"> Set ke Nilai Tertentu
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="action" value="reduce"> Kurangi Slot
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Jumlah Slot</label>
                <input type="number" name="amount" min="1" max="999" value="1" required
                    style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 10px; background: #f8fafc; font-size: 1rem; font-family: inherit; outline: none;">
                <small style="color: var(--text-muted); display: block; margin-top: 6px;">
                    Gunakan "Tambah" untuk menambah dari saldo saat ini. "Set" akan mengganti nilai slot ke angka ini.
                </small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px;"
                onclick="return confirm('Apakah Anda yakin ingin mengubah slot iklan untuk SEMUA pengguna?')">
                <i class="fa-solid fa-users-gear"></i> Terapkan ke Semua Pengguna
            </button>
        </form>
    </div>

    {{-- Tambah Slot ke Pengguna Tertentu --}}
    <div class="glass" style="padding: 30px; border-radius: var(--radius);">
        <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-user-gear" style="color: var(--accent);"></i> Pengguna Tertentu
        </h2>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 25px;">
            Cari pengguna berdasarkan nama, email, atau nomor WhatsApp, lalu ubah slot iklannya.
        </p>

        <form action="{{ route('admin.users.slot.single') }}" method="POST">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Pengguna</label>
                <select name="user_id" required id="user-select"
                    style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 10px; background: #f8fafc; font-size: 0.9rem; font-family: inherit; outline: none; cursor: pointer;">
                    <option value="">-- Pilih Pengguna --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">
                            {{ $u->name }} | WA: {{ $u->whatsapp ?? '-' }} | Slot: {{ $u->ads_quota }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Tindakan</label>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="action" value="add" checked> Tambah Slot
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="action" value="set"> Set ke Nilai Tertentu
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                        <input type="radio" name="action" value="reduce"> Kurangi Slot
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Jumlah Slot</label>
                <input type="number" name="amount" min="0" max="9999" value="1" required
                    style="width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 10px; background: #f8fafc; font-size: 1rem; font-family: inherit; outline: none;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; background: var(--accent); border-color: var(--accent);">
                <i class="fa-solid fa-user-check"></i> Terapkan ke Pengguna Ini
            </button>
        </form>
    </div>
</div>

{{-- Daftar Pengguna dengan Kuota --}}
<div class="glass" style="padding: 30px; border-radius: var(--radius); margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 1.2rem; font-weight: 700; margin: 0;">Daftar Slot per Pengguna</h2>
        <form action="{{ route('admin.users.slot') }}" method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / WA..."
                style="padding: 9px 14px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.875rem; outline: none;">
            <button type="submit" class="btn btn-primary" style="padding: 9px 18px; font-size: 0.875rem;">Cari</button>
            @if(request('search'))
                <a href="{{ route('admin.users.slot') }}" class="btn btn-outline" style="padding: 9px 14px; font-size: 0.875rem;">Reset</a>
            @endif
        </form>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table" style="min-width: 600px;">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>WhatsApp</th>
                    <th style="text-align: center;">Slot Tersisa</th>
                    <th style="text-align: center;">Total Iklan</th>
                    <th style="text-align: center;">Iklan Aktif</th>
                    <th style="text-align: right;">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td style="font-weight: 600;">{{ $u->name }}</td>
                    <td>
                        @if($u->whatsapp)
                            <a href="https://wa.me/{{ $u->whatsapp }}" target="_blank" style="color: #166534; display: flex; align-items: center; gap: 5px; font-size: 0.875rem;">
                                <i class="fa-brands fa-whatsapp"></i> {{ $u->whatsapp }}
                            </a>
                        @else
                            <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <span style="font-weight: 700; font-size: 1.1rem; color: {{ $u->ads_quota <= 0 ? '#ef4444' : ($u->ads_quota <= 1 ? '#f59e0b' : 'var(--primary)') }};">
                            {{ $u->ads_quota }}
                        </span>
                    </td>
                    <td style="text-align: center; color: var(--text-muted);">{{ $u->listings_count }}</td>
                    <td style="text-align: center; color: var(--text-muted);">{{ $u->active_listings_count }}</td>
                    <td>
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            {{-- +1 --}}
                            <form action="{{ route('admin.users.slot.single') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $u->id }}">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="amount" value="1">
                                <button type="submit" title="+1 Slot"
                                    style="padding: 6px 12px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 0.8rem;">
                                    +1
                                </button>
                            </form>
                            {{-- -1 --}}
                            <form action="{{ route('admin.users.slot.single') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $u->id }}">
                                <input type="hidden" name="action" value="reduce">
                                <input type="hidden" name="amount" value="1">
                                <button type="submit" title="-1 Slot"
                                    style="padding: 6px 12px; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 0.8rem;">
                                    -1
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                        Tidak ada pengguna ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 20px;">
    <div style="margin-top: 20px;">
        {{ $users->withQueryString()->links('vendor.pagination.simple-custom') }}
    </div>
    </div>
</div>
@endsection
