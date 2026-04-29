<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', config('app.name'))</title>
    <link rel="shortcut icon" href="{{ asset('logo-sebatam.png') }}" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    
    <!-- PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0ea5e9">
    <link rel="apple-touch-icon" href="{{ asset('logo-sebatam.png') }}">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(reg => {
                    console.log('Service worker registered.', reg);
                }).catch(err => {
                    console.log('Service worker registration failed.', err);
                });
            });
        }
    </script>

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
    <div id="waAlert" class="whatsapp-bot-alert" style="background: linear-gradient(90deg, #25D366 0%, #128C7E 100%); color: white; padding: 12px 0; text-align: center; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.1); position: relative; z-index: 1001;">
        <div class="container">
            <div class="wa-alert-content" style="display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap; font-size: 0.85rem; line-height: 1.4;">
                <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem;"></i>
                <span class="wa-text">Kirim pesan <strong>"pasang iklan"</strong> ke <a href="https://wa.me/{{ config('services.whatsapp.bot_number', '6282172292230') }}" style="color: white; text-decoration: underline; font-weight: 700; background: rgba(0,0,0,0.1); padding: 1px 6px; border-radius: 4px;">{{ config('services.whatsapp.bot_number', '6282172292230') }}</a></span>
                untuk pasang iklan secara instan.
            </div>
        </div>
    </div>
    <header class="main-header">
        <div class="container nav-content">
            <!-- Logo -->
            <div class="nav-left">
                @yield('header_left')
                <a href="{{ route('home') }}" class="logo-link">
                    <span class="logo-brand">sebatam</span><span class="logo-dot">.com</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="nav-desktop">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Lapak</a>
                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.index') ? 'active' : '' }}">Kategori</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Kontak</a>
                <a href="{{ route('baca-saya') }}" class="{{ request()->routeIs('baca-saya') ? 'active' : '' }}">Panduan</a>
            </nav>

            <!-- Actions / User Menu -->
            <div class="nav-actions">
                @guest
                    <a href="{{ route('listings.create') }}" class="btn-post-desktop">Pasang Iklan</a>
                    <a href="{{ route('login') }}" class="btn-login">Masuk</a>
                @else
                    <div class="user-dropdown">
                        <div class="user-trigger">
                            <img src="{{ auth()->user()->getProfilePhoto() }}" class="user-avatar">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                        <div class="user-menu">
                            <a href="{{ route('listings.create') }}" class="menu-item primary"><i class="fa-solid fa-plus-circle"></i> Pasang Iklan</a>
                            <hr>
                            <a href="{{ route('dashboard') }}" class="menu-item"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                            <a href="{{ route('profile.edit') }}" class="menu-item"><i class="fa-solid fa-user-gear"></i> Profil Saya</a>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="menu-item admin"><i class="fa-solid fa-user-shield"></i> Admin Site</a>
                            @endif
                            <hr>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="menu-item logout"><i class="fa-solid fa-right-from-bracket"></i> Keluar</button>
                            </form>
                        </div>
                    </div>
                @endguest

                <!-- Mobile Toggle -->
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-header">
                <span class="mobile-menu-title">Menu</span>
                <button class="mobile-close" id="mobileClose">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="mobile-menu-body">
                <a href="{{ route('home') }}" class="mobile-link {{ request()->routeIs('home') ? 'active' : '' }}">Lapak</a>
                <a href="{{ route('categories.index') }}" class="mobile-link {{ request()->routeIs('categories.index') ? 'active' : '' }}">Kategori</a>
                <a href="{{ route('contact') }}" class="mobile-link {{ request()->routeIs('contact') ? 'active' : '' }}">Kontak</a>
                <a href="{{ route('baca-saya') }}" class="mobile-link {{ request()->routeIs('baca-saya') ? 'active' : '' }}">Panduan</a>
                <hr>
                @auth
                    <div class="mobile-account-section">
                        <span class="section-label">Akun Saya</span>
                        <a href="{{ route('dashboard') }}" class="mobile-link"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                        <a href="{{ route('profile.edit') }}" class="mobile-link"><i class="fa-solid fa-user-gear"></i> Profil</a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="mobile-link admin"><i class="fa-solid fa-user-shield"></i> Admin Site</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="mobile-link logout"><i class="fa-solid fa-right-from-bracket"></i> Keluar</button>
                        </form>
                    </div>
                @endauth
                <div class="mobile-footer-actions">
                    <a href="{{ route('listings.create') }}" class="btn btn-primary" style="width: 100%;">Pasang Iklan Baru</a>
                </div>
            </div>
        </div>
    </header>

    <script>
        const mobileToggle = document.getElementById('mobileToggle');
        const mobileClose = document.getElementById('mobileClose');
        const mobileMenu = document.getElementById('mobileMenu');
        const waAlert = document.getElementById('waAlert');
        const body = document.body;

        function openMenu() {
            mobileMenu.classList.add('active');
            body.style.overflow = 'hidden';
            if (waAlert) waAlert.style.display = 'none';
        }

        function closeMenu() {
            mobileMenu.classList.remove('active');
            body.style.overflow = '';
            if (waAlert) waAlert.style.display = 'block';
        }

        mobileToggle.addEventListener('click', openMenu);
        mobileClose.addEventListener('click', closeMenu);
    </script>

    <main>
        @yield('content')
    </main>

    <footer style="padding: 60px 0; background: #0f172a; color: white; text-align: center; margin-top: 80px;">
        <div class="container">
            <div class="logo" style="font-size: 1.8rem; margin-bottom: 20px; color: white; justify-content: center;">{{ config('app.name') }}</div>
            <p style="color: #94a3b8; margin-bottom: 30px;">Platform Penawaran dan Pengumuman No.1 di Batam, Kepulauan Riau.</p>
            <div style="display: flex; justify-content: center; gap: 20px; font-size: 1.2rem;">
                <a href="{{ config('services.social.facebook') }}"><i class="fab fa-facebook"></i></a>
                <a href="{{ config('services.social.instagram') }}"><i class="fab fa-instagram"></i></a>
                <a href="{{ config('services.social.tiktok') }}"><i class="fab fa-tiktok"></i></a>
                <a href="{{ config('services.social.youtube') }}"><i class="fab fa-youtube"></i></a>
            </div>
            <hr style="border: none; border-top: 1px solid #1e293b; margin: 40px 0;">
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 5px;">&copy; {{ date('Y') }} sebatam.com. All rights reserved.</p>
            <nav style="margin-top: 20px;">
                <a href="{{ route('baca-saya') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Panduan</a>
                |
                <a href="{{ route('contact') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Kontak</a>
                |
                <a href="{{ route('terms.and.conditions') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Syarat & Ketentuan</a>

                |
                <a href="{{ route('privacy.policy') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Kebijakan Privasi</a>
                |
                <a href="{{ route('disclaimer') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Disclaimer</a>
            </nav>
        </div>
    </footer>
    @yield('scripts')
</body>
</html>

