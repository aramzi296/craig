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
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Ringkasan
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.categories') }}" class="sidebar-link {{ request()->routeIs('admin.categories') ? 'active' : '' }}">
                    <i class="fa-solid fa-tags"></i> Kategori
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.listings') }}" class="sidebar-link {{ request()->routeIs('admin.listings') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i> Kelola Listing
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.listing_types') }}" class="sidebar-link {{ request()->routeIs('admin.listing_types') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i> Tipe Listing
                </a>
            </li>
            <hr style="border: none; border-top: 1px solid var(--border); margin: 10px 0;">
            <li class="sidebar-item">
                <a href="{{ route('admin.premium_packages') }}" class="sidebar-link {{ request()->routeIs('admin.premium_packages*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gem"></i> Paket Premium
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.premium_requests') }}" class="sidebar-link {{ request()->routeIs('admin.premium_requests*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Permintaan Premium
                </a>
            </li>
            <hr style="border: none; border-top: 1px solid var(--border); margin: 10px 0;">

            <li class="sidebar-item">
                <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear"></i> Pengguna
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gears"></i> Parameter
                </a>
            </li>
            <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0;">
            <li class="sidebar-item">
                <a href="{{ route('dashboard') }}" class="sidebar-link">
                    <i class="fa-solid fa-user"></i> Dashboard Saya
                </a>
            </li>
        </ul>
    </aside>

    <main class="dashboard-content">
        @yield('admin_content')
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
