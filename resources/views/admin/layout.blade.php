@extends('layouts.app')

@section('header_left')
    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars-staggered"></i>
    </button>
@endsection

@section('content')
<div class="dashboard-layout" id="dashboardLayout" style="overflow: visible !important; min-height: auto !important;">
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
                    <i class="fa-solid fa-tags"></i> Kelola Kategori
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.listings') }}" class="sidebar-link {{ request()->routeIs('admin.listings') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i> Kelola Listing
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.photos') }}" class="sidebar-link {{ request()->routeIs('admin.photos*') ? 'active' : '' }}">
                    <i class="fa-solid fa-images"></i> Gambar Sebatam
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.settings.compress-images') }}" class="sidebar-link" onclick="event.preventDefault(); if(confirm('Mulai kompresi gambar? Proses ini akan mencari dan mengompres maksimal 50 gambar berukuran besar.')) document.getElementById('compressImagesForm').submit();">
                    <i class="fa-solid fa-compress"></i> Kompres Gambar
                </a>
                <form id="compressImagesForm" action="{{ route('admin.settings.compress-images') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.n8n.listings') }}" class="sidebar-link {{ request()->routeIs('admin.n8n.listings*') ? 'active' : '' }}">
                    <i class="fa-solid fa-share-nodes"></i> n8n Listing
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.listings.json') }}" class="sidebar-link {{ request()->routeIs('admin.listings.json*') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-code"></i> Listing By JSON
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.generate-tags*') ? 'active' : '' }}" onclick="event.preventDefault(); openGenerateTagsModal();">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Buat Tagar
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.tags.deduplicate') }}" class="sidebar-link {{ request()->routeIs('admin.tags.deduplicate*') ? 'active' : '' }}">
                    <i class="fa-solid fa-broom"></i> Bersihkan Tagar
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link {{ request()->routeIs('admin.set-category*') ? 'active' : '' }}" onclick="event.preventDefault(); openSetCategoryModal();">
                    <i class="fa-solid fa-folder-open"></i> Set Kategori Listing
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.clear-category') }}" class="sidebar-link" onclick="event.preventDefault(); if(confirm('Yakin ingin mengosongkan semua kategori pada seluruh listing? Tindakan ini tidak dapat dibatalkan.')) document.getElementById('clearCategoryForm').submit();">
                    <i class="fa-solid fa-trash-can" style="color: #ef4444;"></i> Kosongkan Kategori
                </a>
                <form id="clearCategoryForm" action="{{ route('admin.clear-category') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.reports') }}" class="sidebar-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i> Laporan Usaha
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.contacts') }}" class="sidebar-link {{ request()->routeIs('admin.contacts*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope-open-text"></i> Pesan Masuk
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
                <a href="{{ route('admin.users.slot') }}" class="sidebar-link {{ request()->routeIs('admin.users.slot') ? 'active' : '' }}">
                    <i class="fa-solid fa-square-plus"></i> Slot Usaha
                </a>
            </li>

            <li class="sidebar-item">
                <a href="{{ route('admin.meilisearch.sync') }}" class="sidebar-link" onclick="event.preventDefault(); if(confirm('Yakin ingin menyinkronkan seluruh data listing ke Meilisearch? Proses ini mungkin memakan waktu beberapa detik.')) document.getElementById('syncMeiliForm').submit();">
                    <i class="fa-solid fa-server"></i> Sinkron Meili
                </a>
                <form id="syncMeiliForm" action="{{ route('admin.meilisearch.sync') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gears"></i> Parameter
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.whatsapp') }}" class="sidebar-link {{ request()->routeIs('admin.whatsapp') ? 'active' : '' }}">
                    <i class="fa-brands fa-whatsapp"></i> Kirim WA
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.whatsapp.history') }}" class="sidebar-link {{ request()->routeIs('admin.whatsapp.history') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat WA
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('admin.wa_templates') }}" class="sidebar-link {{ request()->routeIs('admin.wa_templates*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list"></i> Template WA
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

    <main class="dashboard-content" style="overflow: visible !important;">
        @if(session('success'))
            <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #4ade80; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
                <i class="fa-solid fa-circle-check" style="margin-right: 10px;"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
                <i class="fa-solid fa-circle-exclamation" style="margin-right: 10px;"></i> {{ session('error') }}
            </div>
        @endif

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

    function openGenerateTagsModal() {
        const modal = document.getElementById('generateTagsModal');
        modal.style.display = 'flex';
    }

    function closeGenerateTagsModal() {
        const modal = document.getElementById('generateTagsModal');
        modal.style.display = 'none';
    }

    function openSetCategoryModal() {
        const modal = document.getElementById('setCategoryModal');
        modal.style.display = 'flex';
    }

    function closeSetCategoryModal() {
        const modal = document.getElementById('setCategoryModal');
        modal.style.display = 'none';
    }
</script>

<!-- Modal Input Jumlah Listing Buat Tagar -->
<div id="generateTagsModal" class="report-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 10000;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 400px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid rgba(226, 232, 240, 0.8);">
        <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1.25rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-wand-magic-sparkles" style="color: #0ea5e9;"></i> Buat Tagar
        </h3>
        <p style="color: #64748b; font-size: 0.88rem; margin-bottom: 20px; line-height: 1.5;">
            Masukkan jumlah listing tanpa tagar yang ingin Anda kirimkan ke webhook n8n untuk dibuatkan tagar otomatis. (Saat ini terdapat <strong>{{ $untaggedListingsCount ?? 0 }}</strong> listing tanpa tagar).
        </p>
        <form action="{{ route('admin.generate-tags') }}" method="GET">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Jumlah Listing</label>
                <input type="number" name="limit" value="1" min="1" required style="width: 100%; padding: 12px 16px; border-radius: 12px; border: 1.5px solid #e2e8f0; font-size: 0.95rem; box-sizing: border-box; background: #f8fafc; color: #1e293b;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeGenerateTagsModal()" style="padding: 10px 18px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; background: #fff; color: #64748b; border: 1.5px solid #e2e8f0; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">Batal</button>
                <button type="submit" style="padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">Proses</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Input Jumlah Listing Set Kategori -->
<div id="setCategoryModal" class="report-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); align-items: center; justify-content: center; z-index: 10000;">
    <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 400px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid rgba(226, 232, 240, 0.8);">
        <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1.25rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-folder-open" style="color: #f59e0b;"></i> Set Kategori Listing
        </h3>
        <p style="color: #64748b; font-size: 0.88rem; margin-bottom: 20px; line-height: 1.5;">
            Masukkan jumlah listing tanpa kategori yang ingin Anda kirimkan ke webhook n8n untuk ditentukan kategorinya secara otomatis. (Saat ini terdapat <strong>{{ $uncategorizedListingsCount ?? 0 }}</strong> listing tanpa kategori).
        </p>
        <form action="{{ route('admin.set-category') }}" method="GET">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Jumlah Listing</label>
                <input type="number" name="limit" value="1" min="1" required style="width: 100%; padding: 12px 16px; border-radius: 12px; border: 1.5px solid #e2e8f0; font-size: 0.95rem; box-sizing: border-box; background: #f8fafc; color: #1e293b;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeSetCategoryModal()" style="padding: 10px 18px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; background: #fff; color: #64748b; border: 1.5px solid #e2e8f0; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">Batal</button>
                <button type="submit" style="padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">Proses</button>
            </div>
        </form>
    </div>
</div>
@endsection
