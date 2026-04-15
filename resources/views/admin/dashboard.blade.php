@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Admin Dashboard</h1>
    <p style="color: var(--text-muted);">Selamat datang di panel kontrol BatamCraig.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Pengguna</div>
        <div class="stat-value">{{ $stats['users'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Kategori</div>
        <div class="stat-value">{{ $stats['categories'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Listing</div>
        <div class="stat-value">{{ $stats['listings'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Iklan Premium</div>
        <div class="stat-value">{{ $stats['featured'] }}</div>
    </div>
</div>

<div class="glass" style="padding: 30px; border-radius: var(--radius); margin-top: 40px;">
    <h2 style="font-size: 1.2rem; margin-bottom: 20px;">Listing Terbaru</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Listing</th>
                <th>Pengguna</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($latestListings as $listing)
            <tr>
                <td>{{ $listing->title }}</td>
                <td>{{ $listing->user->name }}</td>
                <td>{{ $listing->categories->pluck('name')->join(', ') }}</td>
                <td>{{ $listing->location }}</td>
                <td>
                    <span class="badge {{ $listing->is_active ? 'badge-success' : 'badge-pending' }}">
                        {{ $listing->is_active ? 'Aktif' : 'Draft' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
