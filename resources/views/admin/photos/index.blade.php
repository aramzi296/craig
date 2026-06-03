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
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
        @forelse($photos as $photo)
            <div style="border: 1px solid #f1f5f9; border-radius: 12px; overflow: hidden; background: white; display: flex; flex-direction: column; position: relative;">
                <!-- Thumbnail -->
                <div style="width: 100%; aspect-ratio: 1/1; position: relative; background: #f8fafc;">
                    <img src="{{ $photo->getThumbnailUrl() }}" style="width: 100%; height: 100%; object-fit: cover;" alt="">
                    
                    <span style="position: absolute; top: 10px; left: 10px; background: rgba(15, 23, 42, 0.75); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 700; backdrop-filter: blur(4px);">
                        {{ $photo->collection === 'foto_fitur' ? 'Foto Fitur' : 'Galeri' }}
                    </span>
                </div>
                
                <!-- Listing details and delete button -->
                <div style="padding: 15px; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between; gap: 10px;">
                    <div>
                        @if($photo->listing)
                            <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                <a href="{{ route('listings.show', $photo->listing->slug) }}" target="_blank" style="color: inherit; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                    {{ $photo->listing->title }}
                                </a>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                Pemilik: {{ $photo->listing->user->name }}
                            </div>
                        @else
                            <div style="font-weight: 700; font-size: 0.85rem; color: #ef4444;">
                                Usaha tidak ditemukan (Orphan)
                            </div>
                        @endif
                    </div>
                    
                    <form action="{{ route('admin.listings.photos.destroy', $photo->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus gambar ini?')" style="margin: 0; width: 100%;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 8px 12px; font-size: 0.8rem; font-weight: 700; color: #ef4444; border-color: #fca5a5; background: #fff5f5; display: inline-flex; align-items: center; justify-content: center; gap: 6px; border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
                            <i class="fa-solid fa-trash"></i> Hapus Gambar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fa-solid fa-images" style="font-size: 2.5rem; margin-bottom: 10px; display: block;"></i>
                Tidak ada gambar yang ditemukan.
            </div>
        @endforelse
    </div>

    @if($photos->hasPages())
        <div style="margin-top: 30px;">
            {{ $photos->links('vendor.pagination.simple-custom') }}
        </div>
    @endif
</div>
@endsection
