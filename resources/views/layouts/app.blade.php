<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Sebatam.com')</title>
    <link rel="shortcut icon" href="{{ asset('favicon.webp') }}" type="image/webp" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/tailwind.css', 'resources/css/app.css', 'resources/js/app.js'])
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-T9KN6PDM');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T9KN6PDM"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <header class="glass">
        <div class="container nav-content">
            <div style="display: flex; align-items: center; gap: 15px;">
                @yield('header_left')
                <a href="{{ route('home') }}" class="logo">se<span>batam</span>.com</a>
            </div>
            <button class="nav-toggle" id="navToggle" onclick="toggleNav()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <nav class="nav-links" id="navLinks">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.index') ? 'active' : '' }}">Kategori</a>
                <a href="{{ route('baca-saya') }}" class="{{ request()->routeIs('baca-saya') ? 'active' : '' }}">Baca Saya</a>
                
                @guest
                    <a href="{{ route('listings.create') }}" class="{{ request()->routeIs('listings.create') ? 'active' : '' }}">Pasang Iklan</a>
                @endguest

                <div class="nav-divider"></div>

                @guest
                    <a href="{{ route('login') }}" style="font-weight: 600; color: var(--text);">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                @else
                    <div class="dropdown">
                        <div class="dropdown-trigger">
                            <img src="{{ auth()->user()->getProfilePhoto() }}" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);">
                            <span class="nav-greeting">Halo, <strong>{{ auth()->user()->name }}</strong></span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem; color: var(--text-muted);"></i>
                        </div>
                        <div class="dropdown-menu glass">
                            <a href="{{ route('listings.create') }}" class="dropdown-item" style="font-weight: 700; color: var(--primary) !important;">
                                <i class="fa-solid fa-plus-circle"></i> Pasang Iklan
                            </a>
                            <div style="border-top: 1px solid var(--border); margin: 5px 0;"></div>
                            <a href="{{ route('dashboard') }}" class="dropdown-item">
                                <i class="fa-solid fa-gauge-high"></i> Dashboard
                            </a>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="fa-solid fa-user-gear"></i> Profil Saya
                            </a>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item" style="color: var(--secondary) !important;">
                                    <i class="fa-solid fa-user-shield" style="color: var(--secondary);"></i> Admin Site
                                </a>
                            @endif
                            <div style="border-top: 1px solid var(--border); margin: 5px 0;"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; color: #ef4444 !important;">
                                    <i class="fa-solid fa-right-from-bracket" style="color: #ef4444;"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @endguest
            </nav>
        </div>
    </header>

    <script>
        function toggleNav() {
            const navLinks = document.getElementById('navLinks');
            const navToggle = document.getElementById('navToggle');
            navLinks.classList.toggle('show');
            
            // Change icon
            const icon = navToggle.querySelector('i');
            if (navLinks.classList.contains('show')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        }
    </script>

    <main>
        @yield('content')
    </main>

    <footer style="padding: 60px 0; background: #0f172a; color: white; text-align: center; margin-top: 80px;">
        <div class="container">
            <div class="logo" style="font-size: 1.8rem; margin-bottom: 20px; color: white;">se<span>batam</span>.com</div>
            <p style="color: #94a3b8; margin-bottom: 30px;">Platform Penawaran dan Pengumuman No.1 di Batam, Kepulauan Riau.</p>
            <div style="display: flex; justify-content: center; gap: 20px; font-size: 1.2rem;">
                <a href="https://www.facebook.com/SemuaSebatam"><i class="fab fa-facebook"></i></a>
                <a href="https://www.instagram.com/semuasebatam/"><i class="fab fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@semuasebatam"><i class="fab fa-tiktok"></i></a>
                <a href="https://www.youtube.com/@SemuaSebatam"><i class="fab fa-youtube"></i></a>
            </div>
            <hr style="border: none; border-top: 1px solid #1e293b; margin: 40px 0;">
            <p style="color: #64748b; font-size: 0.9rem;">&copy; 2026 sebatam.com. All rights reserved.</p>
            <nav style="margin-top: 20px;">
                <a href="{{ route('baca-saya') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Baca Saya</a>
                |
                <a href="{{ route('about') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Tentang</a>
                |
                <a href="{{ route('contact') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Kontak</a>
                |
                <a href="{{ route('terms.and.conditions') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Syarat & Ketentuan</a>

                |
                <a href="{{ route('privacy.policy') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Kebijakan Privasi</a>
            </nav>
        </div>
    </footer>
</body>
</html>

