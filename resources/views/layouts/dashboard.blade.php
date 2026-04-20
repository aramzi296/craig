@extends('layouts.app')

@section('header_left')
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars-staggered"></i>
    </button>
@endsection

@section('content')
<div class="dashboard-layout" id="dashboardLayout">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') && !request('tab') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            </li>
            @if(auth()->user()->is_admin)
            <li class="sidebar-item">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link" style="color: var(--secondary);">
                    <i class="fa-solid fa-user-shield"></i> Admin Panel
                </a>
            </li>
            @endif
            <li class="sidebar-item">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') && request('tab') != 'favorites' ? 'active' : '' }}">
                    <i class="fa-solid fa-list"></i> Iklan Saya
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('dashboard', ['tab' => 'favorites']) }}" class="sidebar-link {{ request('tab') == 'favorites' ? 'active' : '' }}">
                    <i class="fa-solid fa-heart"></i> Favorit
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link">
                    <i class="fa-solid fa-message"></i> Pesan
                </a>
            </li>
            <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">
            <li class="sidebar-item">
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                    <i class="fa-solid fa-user"></i> Profil
                </a>
            </li>
            <li class="sidebar-item">
                <form action="{{ route('logout') }}" method="POST" id="logout-form-sidebar" style="display: none;">@csrf</form>
                <a href="#" class="sidebar-link" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();" style="color: #ef4444;">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </a>
            </li>
        </ul>
    </aside>

    <main class="dashboard-content">
        @if(session('success'))
            <div class="alert alert-success" style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #bbf7d0;">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #fecaca;">
                <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning" style="background: #fef9c3; color: #854d0e; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #fef08a;">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ session('warning') }}
            </div>
        @endif

        @yield('dashboard_content')
    </main>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const layout = document.getElementById('dashboardLayout');
        
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        layout.classList.toggle('sidebar-collapsed');
    }
</script>
@endsection
