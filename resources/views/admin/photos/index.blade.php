@extends('admin.layout')

@section('admin_content')
<div class="dashboard-header">
    <div>
        <h1>Gambar Sebatam</h1>
        <p style="color: var(--text-muted);">Kelola semua gambar listing/usaha yang diunggah.</p>
    </div>
</div>

<div class="glass" style="padding: 20px; border-radius: var(--radius); margin-bottom: 30px;">
    <form action="{{ route('admin.photos') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 5px;">Cari Nama Usaha</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama usaha..." class="form-control" style="padding: 10px 15px;">
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.photos') }}" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center;">Reset</a>
        </div>
    </form>
</div>

<div class="glass" style="padding: 30px; border-radius: var(--radius);">
    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);">
            <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <tr>
                    <th style="padding: 15px; text-align: left; width: 100px; color: #475569; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Thumbnail</th>
                    <th style="padding: 15px; text-align: left; color: #475569; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Detail & Tautan</th>
                    <th style="padding: 15px; text-align: center; width: 120px; color: #475569; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($photos as $photo)
                    @php
                        $isCompressed = isset($photo->meta['compression_info']);
                        $originalSize = $isCompressed ? ($photo->meta['compression_info']['original_size'] ?? 0) : $photo->file_size;
                        $compressedSize = $isCompressed ? $photo->file_size : null;
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='transparent'">
                        <td style="padding: 15px; vertical-align: top;">
                            <div style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; background: #e2e8f0; position: relative;">
                                <img src="{{ $photo->getThumbnailUrl() }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Thumbnail">
                                <span style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(15, 23, 42, 0.7); color: white; text-align: center; font-size: 0.6rem; padding: 2px 0;">
                                    {{ $photo->collection === 'foto_fitur' ? 'Fitur' : 'Galeri' }}
                                </span>
                            </div>
                        </td>
                        <td style="padding: 15px; vertical-align: top;">
                            <div style="margin-bottom: 10px;">
                                @if($photo->listing)
                                    <a href="{{ route('listings.show', $photo->listing->slug) }}" target="_blank" style="font-weight: 700; font-size: 0.95rem; color: #0ea5e9; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                        {{ $photo->listing->title }}
                                    </a>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                        Pemilik: {{ $photo->listing->user->name }}
                                    </div>
                                @else
                                    <div style="font-weight: 700; font-size: 0.95rem; color: #ef4444;">
                                        Usaha tidak ditemukan (Orphan)
                                    </div>
                                @endif
                            </div>

                            <div style="font-size: 0.8rem; color: #475569; display: grid; grid-template-columns: 100px 1fr; gap: 4px; align-items: center;">
                                <span style="font-weight: 600;">Asli:</span>
                                <span>
                                    @if($isCompressed)
                                        <span style="color: #94a3b8; text-decoration: line-through;">Dihapus</span> ({{ number_format($originalSize / 1024, 2) }} KB)
                                    @else
                                        <a href="{{ $photo->getUrl() }}" target="_blank" style="color: #3b82f6; text-decoration: none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat</a> 
                                        <span style="color: #64748b;">({{ number_format($originalSize / 1024, 2) }} KB)</span>
                                    @endif
                                </span>

                                <span style="font-weight: 600;">Kompresi:</span>
                                <span>
                                    @if($isCompressed)
                                        <a href="{{ $photo->getUrl() }}" target="_blank" style="color: #10b981; text-decoration: none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat</a>
                                        <span style="color: #059669; font-weight: 600;">({{ number_format($compressedSize / 1024, 2) }} KB)</span>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">Belum dikompres</span>
                                    @endif
                                </span>

                                <span style="font-weight: 600;">Thumbnail:</span>
                                <span>
                                    @if($photo->thumbnail_path)
                                        <a href="{{ $photo->getThumbnailUrl() }}" target="_blank" style="color: #8b5cf6; text-decoration: none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat</a>
                                        <span style="color: #64748b; font-size: 0.7rem;">(Ukuran diabaikan)</span>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">Tidak ada</span>
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td style="padding: 15px; vertical-align: middle; text-align: center;">
                            <form action="{{ route('admin.listings.photos.destroy', $photo->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus gambar ini?')" style="margin: 0; display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary" style="padding: 8px 12px; font-size: 0.8rem; font-weight: 700; color: #ef4444; border-color: #fca5a5; background: #fff5f5; display: inline-flex; align-items: center; justify-content: center; gap: 6px; border-radius: 8px; transition: all 0.2s; cursor: pointer;" title="Hapus Gambar" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding: 40px; text-align: center; color: #94a3b8;">
                            <i class="fa-solid fa-images" style="font-size: 2.5rem; margin-bottom: 10px; display: block; color: #cbd5e1;"></i>
                            Tidak ada gambar yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($photos->hasPages())
        <div style="margin-top: 30px;">
            {{ $photos->links('vendor.pagination.simple-custom') }}
        </div>
    @endif
</div>
@endsection
