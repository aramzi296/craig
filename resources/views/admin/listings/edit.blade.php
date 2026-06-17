@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700; margin: 0;">Edit Usaha</h1>
        <p style="color: var(--text-muted); margin: 5px 0 0 0;">Mengubah informasi usaha: <strong>{{ $listing->title }}</strong></p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="{{ route('admin.listings') }}" class="btn btn-secondary" style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
        </a>
        <a href="{{ route('listings.show', ['slug' => $listing->slug]) }}" target="_blank" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-eye"></i> Lihat Profil
        </a>
        <form action="{{ route('admin.listings.destroy', $listing->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus listing ini?');" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn" style="display: flex; align-items: center; gap: 8px; background: #ef4444; color: white; border: none; cursor: pointer;">
                <i class="fa-solid fa-trash"></i> Hapus
            </button>
        </form>
    </div>
</div>

@if ($errors->any())
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; gap: 15px; align-items: flex-start;">
        <div style="font-size: 1.5rem; line-height: 1; color: #ef4444;"><i class="fa-solid fa-circle-exclamation"></i></div>
        <div>
            <p style="font-weight: 700; margin: 0 0 8px 0; font-size: 1.05rem;">Terdapat kesalahan:</p>
            <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem; line-height: 1.6; color: #7f1d1d;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if(session('success'))
    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        {!! session('success') !!}
    </div>
@endif

@php
    $featuredPhoto = $listing->photos->where('collection', 'foto_fitur')->first();
    $galleryPhotos = $listing->photos->where('collection', 'galeri');
@endphp

<div class="form-card">
    @include('listings._form', [
        'form_action'              => route('admin.listings.update', $listing->id),
        'form_method'              => 'PUT',
        'submit_label'             => 'Perbarui Usaha',
        'listing'                  => $listing,
        'categories'               => $categories,
        'tags'                     => $tags,
        'districts'                => $districts,
        'subdistricts'             => $subdistricts,
        'existingFeaturedPhoto'    => $featuredPhoto,
        'existingGalleryPhotos'    => $galleryPhotos,
        'deletePhotoRoute'         => 'admin.listings.photos.destroy',
        'showWebsite'              => true,
        'showCommentVisibility'    => true,
        'isAdminForm'              => true,
        'cancelUrl'                => route('admin.listings'),
    ])
</div>

{{-- Hidden Delete Forms for Photos --}}
@foreach($listing->photos as $photo)
    <form id="delete-photo-{{ $photo->id }}" action="{{ route('admin.listings.photos.destroy', $photo->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach

@endsection
