@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 700;">Kelola Pengguna</h1>
        <p style="color: var(--text-muted);">Daftar semua pengguna yang terdaftar di {{ config('app.name') }}.</p>
    </div>
    <a href="{{ route('admin.users.slot') }}" class="btn btn-primary">
        <i class="fa-solid fa-square-plus"></i> Kelola Slot Iklan
    </a>
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

<div style="margin-bottom: 25px; display: flex; flex-direction: column; gap: 20px;">
    <div style="display: flex; justify-content: flex-end; gap: 15px; align-items: center;">
        <button onclick="document.getElementById('add-user-form').style.display = 'block'; this.style.display = 'none';" class="btn btn-secondary" style="background: white; border: 1px solid var(--border); color: var(--text);">
            <i class="fa-solid fa-user-plus"></i> Tambah Pengguna
        </button>
        
        <form action="{{ route('admin.users') }}" method="GET" style="display: flex; gap: 10px; width: 100%; max-width: 400px;">
            <input type="text" name="search" style="padding: 10px 15px; border: 1px solid var(--border); border-radius: 8px; flex-grow: 1; outline: none; font-family: inherit;" placeholder="Cari nama, email, atau WA..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Cari</button>
        </form>
    </div>

    <div id="add-user-form" style="display: none; background: #f8fafc; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; animation: slideDown 0.3s ease-out; margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0; color: #1e293b;">Tambah Pengguna Instan</h3>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">Nama dan Email akan dibuat otomatis mengikuti sistem Bot WhatsApp.</p>
            </div>
            <button type="button" onclick="document.getElementById('add-user-form').style.display = 'none'; document.querySelector('.btn-secondary').style.display = 'inline-flex';" style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1.2rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.users.store') }}" method="POST" style="display: flex; gap: 15px; align-items: flex-end; max-width: 600px;">
            @csrf
            <div class="form-group" style="margin: 0; flex-grow: 1;">
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">Nomor WhatsApp</label>
                <input type="text" name="whatsapp" required autofocus style="width: 100%; padding: 12px 18px; border: 2px solid #e2e8f0; border-radius: 12px; outline: none; font-size: 1rem; transition: border-color 0.2s;" placeholder="Contoh: 08123456789" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 13px 30px; font-weight: 800; border-radius: 12px; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);">
                Simpan User
            </button>
        </form>
    </div>
</div>

<style>
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="glass" style="padding: 30px; border-radius: var(--radius);">
    <table class="data-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>WhatsApp</th>
                <th style="text-align: center;">Kuota</th>
                <th>Status</th>
                <th>Peran</th>
                <th>Bergabung</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td style="font-weight: 600;">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->whatsapp)
                        <a href="https://wa.me/{{ $user->whatsapp }}" target="_blank" style="color: #166534; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                            <i class="fa-brands fa-whatsapp"></i> {{ $user->whatsapp }}
                        </a>
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center; font-weight: 700; color: var(--primary);">{{ $user->ads_quota }}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($user->is_verified)
                            <span class="badge badge-success" style="font-size: 0.65rem;">VERIFIED</span>
                        @else
                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.65rem;">REGULAR</span>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="badge {{ $user->is_admin ? 'badge-success' : '' }}" style="background: {{ $user->is_admin ? '#dbeafe' : '#f1f5f9' }}; color: {{ $user->is_admin ? '#1e40af' : '#475569' }};">
                        {{ $user->is_admin ? 'Admin' : 'User' }}
                    </span>
                </td>
                <td>{{ $user->created_at->format('d M Y') }}</td>
                <td style="text-align: right;">
                    <div class="dropdown" style="display: inline-block;">
                        <button onclick="toggleDropdown(event, 'dropdown-{{ $user->id }}')" class="btn btn-secondary" style="padding: 8px 15px; font-size: 0.85rem; border-radius: 8px; background: white; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
                            Aksi <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem;"></i>
                        </button>
                        <div id="dropdown-{{ $user->id }}" class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 180px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 12px; border: 1px solid #f1f5f9; z-index: 100; margin-top: 5px; padding: 8px 0;">
                            
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; text-decoration: none; font-size: 0.9rem; transition: background 0.2s;">
                                <i class="fa-solid fa-user-pen" style="width: 16px; color: #0ea5e9;"></i> Edit Profil
                            </a>

                            <form action="{{ route('admin.users.toggle-verification', $user->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; cursor: pointer; font-size: 0.9rem; font-family: inherit;">
                                    <i class="fa-solid fa-certificate" style="width: 16px; color: {{ $user->is_verified ? '#0ea5e9' : '#94a3b8' }};"></i>
                                    {{ $user->is_verified ? 'Unverify User' : 'Verify User' }}
                                </button>
                            </form>

                            <form action="{{ route('admin.users.toggle-admin', $user->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #475569; cursor: pointer; font-size: 0.9rem; font-family: inherit;">
                                    <i class="fa-solid fa-user-shield" style="width: 16px; color: #6366f1;"></i>
                                    {{ $user->is_admin ? 'Remove Admin' : 'Make Admin' }}
                                </button>
                            </form>

                            <a href="{{ route('admin.listings.create', ['user_id' => $user->id]) }}" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: var(--primary); text-decoration: none; font-size: 0.9rem; transition: background 0.2s;">
                                <i class="fa-solid fa-square-plus" style="width: 16px;"></i> Pasang Iklan
                            </a>

                            <div style="height: 1px; background: #f1f5f9; margin: 5px 0;"></div>

                            <a href="{{ route('admin.whatsapp', ['phone' => $user->whatsapp]) }}" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #16a34a; text-decoration: none; font-size: 0.9rem; transition: background 0.2s;">
                                <i class="fa-brands fa-whatsapp" style="width: 16px;"></i> Kirim WA
                            </a>

                            <div style="height: 1px; background: #f1f5f9; margin: 5px 0;"></div>

                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini? Semua iklan milik pengguna ini juga akan terhapus.')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; display: flex; align-items: center; gap: 10px; padding: 10px 16px; color: #ef4444; cursor: pointer; font-size: 0.9rem; font-family: inherit;">
                                    <i class="fa-solid fa-user-xmark" style="width: 16px;"></i> Hapus Akun
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top: 20px;">
        {{ $users->links() }}
    </div>
</div>

<script>
    function toggleDropdown(event, id) {
        event.stopPropagation();
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu.id !== id) {
                menu.style.display = 'none';
            }
        });

        const menu = document.getElementById(id);
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
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
