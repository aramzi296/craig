@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">Permintaan Premium</h1>
    <p style="color: var(--text-muted);">Verifikasi pembayaran QRIS dan aktifkan status premium iklan.</p>
</div>

<div class="glass" style="padding: 0; overflow: hidden; border-radius: var(--radius);">
    <table class="data-table">
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Iklan</th>
                <th>Paket</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $req)
            <tr>
                <td>{{ $req->user->name }}</td>
                <td>
                    <div style="font-weight: 600;">{{ $req->listing->title }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Rp {{ number_format($req->listing->price, 0, ',', '.') }}</div>
                </td>
                <td>
                    <div style="font-weight: 600;">{{ $req->package->name }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                        Rp {{ number_format($req->package->price, 0, ',', '.') }} 
                        <span style="color: #ef4444; font-weight: 700;">+ {{ $req->unique_code }}</span>
                    </div>
                    <div style="font-size: 0.85rem; font-weight: 800; color: var(--primary); margin-top: 4px;">
                        Total: Rp {{ number_format($req->package->price + $req->unique_code, 0, ',', '.') }}
                    </div>
                </td>

                <td>
                    @if($req->status == 'pending')
                        <span class="badge badge-pending">Menunggu Verifikasi</span>
                    @elseif($req->status == 'active')
                        <span class="badge badge-success">Aktif</span>
                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 4px;">Exp: {{ $req->expires_at->format('d M Y') }}</div>
                    @elseif($req->status == 'rejected')
                        <span class="badge" style="background: #fee2e2; color: #991b1b;">Ditolak</span>
                    @else
                        <span class="badge" style="background: #f1f5f9; color: #475569;">{{ ucfirst($req->status) }}</span>
                    @endif
                </td>
                <td>
                    @if($req->status == 'pending')
                    <div style="display: flex; gap: 10px;">
                        <form action="{{ route('admin.premium_requests.approve', $req->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;">
                                Setujui
                            </button>
                        </form>
                        <form action="{{ route('admin.premium_requests.reject', $req->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem; color: #ef4444; border-color: #fecaca;">
                                Tolak
                            </button>
                        </form>
                    </div>
                    @else
                        <form action="{{ route('admin.premium_requests.reset', $req->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="return confirm('Kembalikan status ke pending? Fitur premium di iklan akan dinonaktifkan.')">
                                <i class="fa-solid fa-rotate-left"></i> Reset ke Pending
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
