@extends('admin.layout')

@section('admin_content')
<div class="dashboard-header">
    <div>
        <h1>Kelola Kategori</h1>
        <p style="color: var(--text-muted);">Daftar Kategori / #Tagar listing di {{ config('app.name') }}.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">+ Kategori Baru</a>
</div>

@if(session('success'))
    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {{ session('error') }}
    </div>
@endif

<div class="glass" style="padding: 20px; border-radius: var(--radius); margin-bottom: 30px;">
    <form action="{{ route('admin.categories') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Cari Kategori</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kategori, slug..." class="form-control" style="padding: 10px 15px;">
        </div>
        
        <div style="width: 180px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Tipe</label>
            <select name="type" class="form-control" style="padding: 10px 15px;">
                <option value="">Semua Tipe</option>
                <option value="parent" {{ request('type') === 'parent' ? 'selected' : '' }}>Kategori Utama</option>
                <option value="sub" {{ request('type') === 'sub' ? 'selected' : '' }}>Sub Kategori</option>
            </select>
        </div>

        <div style="width: 180px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Status</label>
            <select name="status" class="form-control" style="padding: 10px 15px;">
                <option value="">Semua Status</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="unapproved" {{ request('status') === 'unapproved' ? 'selected' : '' }}>Unapproved</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Filter</button>
            <a href="{{ route('admin.categories') }}" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center; padding: 10px 20px;">Reset</a>
        </div>
    </form>
</div>

<div class="glass" style="padding: 30px; border-radius: var(--radius); overflow-x: auto;">
    <table class="data-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align: left; padding: 12px 15px;">Ikon</th>
                <th style="text-align: left; padding: 12px 15px;">Nama</th>
                <th style="text-align: left; padding: 12px 15px;">Tipe</th>
                <th style="text-align: left; padding: 12px 15px;">Kategori Induk</th>
                <th style="text-align: left; padding: 12px 15px;">Status</th>
                <th style="text-align: left; padding: 12px 15px;">Urutan</th>
                <th style="text-align: left; padding: 12px 15px;">Total Listing</th>
                <th style="text-align: right; padding: 12px 15px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
            <tr style="border-bottom: 1px solid var(--border); {{ $category->parent_id ? 'background: #f8fafc;' : '' }}">
                <td style="padding: 15px;"><i class="fa-solid fa-{{ $category->icon }}" style="font-size: 1.2rem; color: var(--primary);"></i></td>
                <td style="padding: 15px; font-weight: 600;">
                    @if($category->parent_id)
                        <div style="padding-left: 20px; display: flex; align-items: center; gap: 8px; color: #475569; font-weight: 500;">
                            <i class="fa-solid fa-turn-up" style="transform: rotate(90deg); font-size: 0.8rem; color: #cbd5e1; margin-top: -4px;"></i>
                            <span>{{ $category->name }}</span>
                        </div>
                    @else
                        <span style="color: #0f172a; font-weight: 700; font-size: 0.95rem;">{{ $category->name }}</span>
                    @endif
                    @if(!$category->is_approved)
                        <span style="background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 5px;">PENDING</span>
                    @endif
                </td>
                <td style="padding: 15px;">
                    @if($category->parent_id)
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.7rem;">Sub Kategori</span>
                    @else
                        <span class="badge" style="background: #f0fdf4; color: #15803d; font-size: 0.7rem;">Kategori Utama</span>
                    @endif
                </td>
                <td style="padding: 15px;">
                    @if($category->parent)
                        <span style="font-weight: 500; color: var(--text);">{{ $category->parent->name }}</span>
                    @else
                        <span style="color: var(--text-muted); font-style: italic;">-</span>
                    @endif
                </td>
                <td style="padding: 15px;">
                    <form action="{{ route('admin.categories.toggle-approval', $category->id) }}" method="POST">
                        @csrf
                        <button type="submit" style="background: none; border: none; cursor: pointer; padding: 0;">
                            @if($category->is_approved)
                                <span class="badge badge-success" style="font-size: 0.65rem;">APPROVED</span>
                            @else
                                <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.65rem;">UNAPPROVED</span>
                            @endif
                        </button>
                    </form>
                </td>
                <td style="padding: 15px;">{{ $category->sort_order }}</td>
                <td style="padding: 15px;">{{ $category->listings_count }}</td>
                <td style="padding: 15px; text-align: right;">
                    <div style="display: flex; gap: 15px; align-items: center; justify-content: flex-end;">
                        <a href="{{ route('admin.categories.edit', $category->id) }}" style="color: var(--primary);"><i class="fa-solid fa-pen"></i></a>
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                    <div style="font-size: 2.5rem; margin-bottom: 15px; color: #cbd5e1;">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <div style="font-weight: 600; font-size: 1.1rem; color: var(--text);">Kategori tidak ditemukan</div>
                    <div style="font-size: 0.9rem; margin-top: 5px;">Tidak ada kategori yang cocok dengan filter atau kata kunci pencarian Anda.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
