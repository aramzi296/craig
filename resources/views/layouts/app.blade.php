<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="agd-partner-manual-verification" />
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

    <style>
        /* Premium Listing Row Styles - Guaranteed Application */
        .listing-row {
            display: flex !important;
            flex-direction: row !important;
            background: #ffffff !important;
            border-radius: 24px !important;
            padding: 24px !important;
            gap: 24px !important;
            align-items: center !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            text-decoration: none !important;
            color: inherit !important;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.08) !important;
            margin-bottom: 20px !important;
            border: 1px solid rgba(226, 232, 240, 0.5) !important;
            position: relative !important;
        }

        .listing-row:hover {
            transform: translateY(-8px) scale(1.02) !important;
            box-shadow: 0 20px 40px -15px rgba(14, 165, 233, 0.2) !important;
            border-color: #0ea5e9 !important;
        }

        .listing-row-profile {
            flex-shrink: 0 !important;
            width: 100px !important;
            height: 100px !important;
        }

        .avatar-container {
            width: 100% !important;
            height: 100% !important;
            border-radius: 30px !important;
            overflow: hidden !important;
            border: 4px solid #ffffff !important;
            box-shadow: 0 8px 16px rgba(0,0,0,0.06) !important;
            background: #f8fafc !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .avatar-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }

        .avatar-fallback {
            width: 100% !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%) !important;
            color: #0369a1 !important;
            font-weight: 800 !important;
            font-size: 2.2rem !important;
        }

        .listing-row-content {
            flex: 1 !important;
            min-width: 0 !important;
        }

        .listing-row-title {
            font-size: 1.5rem !important;
            font-weight: 800 !important;
            margin: 0 0 8px 0 !important;
            color: #0f172a !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            letter-spacing: -0.01em !important;
        }

        .badge-premium {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
            color: white !important;
            font-size: 0.65rem !important;
            padding: 4px 12px !important;
            border-radius: 10px !important;
            font-weight: 900 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3) !important;
            border: none !important;
        }

        .listing-row-description {
            font-size: 1rem !important;
            color: #64748b !important;
            margin: 0 0 20px 0 !important;
            line-height: 1.6 !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
        }

        .listing-row-attributes {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 24px !important;
            flex-wrap: wrap !important;
        }

        .listing-row-price {
            font-size: 1.4rem !important;
            font-weight: 800 !important;
            color: #0ea5e9 !important;
            padding: 4px 0 !important;
        }

        .listing-row-info {
            font-size: 0.9rem !important;
            color: #94a3b8 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-weight: 500 !important;
        }

        .listing-row-info i {
            color: #cbd5e1 !important;
            font-size: 1rem !important;
        }

        .listing-row-update {
            width: 220px !important;
            text-align: right !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 15px !important;
            border-left: 2px solid #f1f5f9 !important;
            padding-left: 30px !important;
            flex-shrink: 0 !important;
        }

        .update-label {
            font-size: 0.7rem !important;
            color: #94a3b8 !important;
            text-transform: uppercase !important;
            font-weight: 700 !important;
            letter-spacing: 0.1em !important;
            margin-bottom: 2px !important;
        }

        .update-time {
            font-size: 0.9rem !important;
            color: #1e293b !important;
            font-weight: 700 !important;
        }

        .btn-whatsapp-sm {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%) !important;
            color: white !important;
            padding: 12px 24px !important;
            border-radius: 16px !important;
            font-size: 0.9rem !important;
            font-weight: 800 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;
            border: none !important;
            box-shadow: 0 10px 20px -5px rgba(37, 211, 102, 0.4) !important;
            transition: all 0.3s ease !important;
        }

        .btn-whatsapp-sm:hover {
            transform: scale(1.05) !important;
            box-shadow: 0 15px 25px -5px rgba(37, 211, 102, 0.5) !important;
        }

        @media (max-width: 768px) {
            .listing-row {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 20px !important;
                padding: 24px !important;
                border-radius: 28px !important;
            }

            .listing-row-profile {
                width: 80px !important;
                height: 80px !important;
            }

            .listing-row-update {
                width: 100% !important;
                border-left: none !important;
                border-top: 2px dashed #f1f5f9 !important;
                padding-left: 0 !important;
                padding-top: 20px !important;
                text-align: left !important;
                flex-direction: row !important;
                justify-content: space-between !important;
                align-items: center !important;
            }
            
            .btn-whatsapp-sm {
                padding: 10px 20px !important;
            }
        }

        /* Nav Dropdown Styles */
        .nav-dropdown {
            position: relative;
            display: flex;
            align-items: center;
            height: 100%;
        }

        .nav-trigger {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            color: #475569;
            transition: all 0.2s;
            padding: 10px 0;
        }

        .nav-trigger i {
            font-size: 0.7rem;
            color: #94a3b8;
            transition: transform 0.2s;
        }

        .nav-dropdown:hover .nav-trigger {
            color: var(--primary);
        }

        .nav-dropdown:hover .nav-trigger i {
            transform: rotate(180deg);
            color: var(--primary);
        }

        .nav-menu {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(10px);
            width: 260px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.15);
            padding: 12px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1100;
        }

        .nav-dropdown:hover .nav-menu {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(5px);
        }

        .nav-menu::before {
            content: '';
            position: absolute;
            top: -6px;
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            width: 12px;
            height: 12px;
            background: white;
            border-top: 1px solid #e2e8f0;
            border-left: 1px solid #e2e8f0;
        }

        .nav-menu .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.9rem;
            color: #475569;
            font-weight: 600;
            transition: all 0.2s;
            text-align: left;
        }

        .nav-menu .menu-item:hover {
            background: #f0f9ff;
            color: var(--primary);
            padding-left: 20px;
        }

        /* Mobile Submenu */
        .mobile-submenu {
            margin-left: 20px;
            border-left: 2px solid #f1f5f9;
            padding-left: 15px;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .mobile-submenu-link {
            display: block;
            padding: 10px 0;
            font-size: 0.95rem;
            font-weight: 500;
            color: #64748b;
        }

        .mobile-submenu-link.active {
            color: var(--primary);
            font-weight: 700;
        }
    </style>
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
                <span class="wa-text">Kirim pesan <strong>"lapak sebatam"</strong> ke <a href="https://wa.me/{{ config('services.whatsapp.bot_number', '6282172292230') }}" style="color: white; text-decoration: underline; font-weight: 700; background: rgba(0,0,0,0.1); padding: 1px 6px; border-radius: 4px;">{{ config('services.whatsapp.bot_number', '6282172292230') }}</a></span>
                untuk pasang iklan di <strong>Sebatam.com</strong>.
            </div>
        </div>
    </div>
    <header class="main-header">
        <div class="container nav-content">
            <!-- Logo -->
            <div class="nav-left">
                @yield('header_left')
                <a href="{{ route('home') }}" class="logo-link">
                    <span class="logo-brand" style="text-transform: none;">Sebatam<span style="color: #ff6904ff;">.com</span></span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="nav-desktop">
                <a href="{{ route('listings.index') }}" class="{{ (request()->routeIs('listings.index') && !request('type')) ? 'active' : '' }}">Lapak</a>
                
                <div class="nav-dropdown">
                    <a href="#" class="nav-trigger {{ request('type') ? 'active' : '' }}">
                        Tipe Lapak <i class="fa-solid fa-chevron-down"></i>
                    </a>
                    <div class="nav-menu">
                        @foreach($globalListingTypes as $type)
                            <a href="{{ route('listings.index', ['type' => $type->id]) }}" class="menu-item {{ request('type') == $type->id ? 'active' : '' }}">
                                {{ $type->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.index') ? 'active' : '' }}">#Hashtag</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Kontak</a>
                <a href="{{ route('tentang') }}" class="{{ request()->routeIs('tentang') ? 'active' : '' }}">Tentang</a>
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
                <a href="{{ route('listings.index') }}" class="mobile-link {{ (request()->routeIs('listings.index') && !request('type')) ? 'active' : '' }}">Lapak</a>
                
                <div class="mobile-section" style="padding: 10px 0;">
                    <span class="section-label" style="margin-bottom: 5px;">Tipe Lapak</span>
                    <div class="mobile-submenu">
                        @foreach($globalListingTypes as $type)
                            <a href="{{ route('listings.index', ['type' => $type->id]) }}" class="mobile-submenu-link {{ request('type') == $type->id ? 'active' : '' }}">
                                <i class="fa-solid fa-angle-right" style="font-size: 0.7rem; margin-right: 5px;"></i> {{ $type->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('categories.index') }}" class="mobile-link {{ request()->routeIs('categories.index') ? 'active' : '' }}">#Hashtag</a>
                <a href="{{ route('contact') }}" class="mobile-link {{ request()->routeIs('contact') ? 'active' : '' }}">Kontak</a>
                <a href="{{ route('tentang') }}" class="mobile-link {{ request()->routeIs('tentang') ? 'active' : '' }}">Tentang</a>
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
        <div class="container" style="margin-top: 20px;">
            @if(session('success'))
                <div class="alert alert-success" style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error" style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #fecaca; display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-circle-xmark" style="font-size: 1.2rem;"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning" style="background: #fef9c3; color: #854d0e; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid #fef08a; display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.2rem;"></i>
                    <div>{{ session('warning') }}</div>
                </div>
            @endif
        </div>
        @yield('content')
    </main>

    <footer style="padding: 60px 0; background: #0f172a; color: white; text-align: center; margin-top: 80px;">
        <div class="container">
            <div class="logo" style="font-size: 1.8rem; margin-bottom: 20px; color: white; justify-content: center; text-transform: none;">Sebatam<span style="color: #94a3b8;">.com</span></div>
            <p style="color: #94a3b8; margin-bottom: 30px;">Platform Penawaran dan Pengumuman No.1 di Batam, Kepulauan Riau.</p>
            <div style="display: flex; justify-content: center; gap: 20px; font-size: 1.2rem;">
                <a href="{{ config('services.social.facebook') }}"><i class="fab fa-facebook"></i></a>
                <a href="{{ config('services.social.instagram') }}"><i class="fab fa-instagram"></i></a>
                <a href="{{ config('services.social.tiktok') }}"><i class="fab fa-tiktok"></i></a>
                <a href="{{ config('services.social.youtube') }}"><i class="fab fa-youtube"></i></a>
            </div>
            <hr style="border: none; border-top: 1px solid #1e293b; margin: 40px 0;">
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 5px;">&copy; {{ date('Y') }} sebatam.com. All rights reserved.</p>
            <nav class="footer-nav" style="margin-top: 20px; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <a href="{{ route('categories.index') }}" style="color: #94a3b8; text-decoration: none; font-weight: 600;">#Hashtag</a>
                <a href="{{ route('contact') }}" style="color: #94a3b8; text-decoration: none; font-weight: 600;">Kontak</a>
                <a href="{{ route('tentang') }}" style="color: #94a3b8; text-decoration: none; font-weight: 600;">Tentang</a>
            </nav>
            <nav class="footer-legal" style="margin-top: 15px; display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; font-size: 0.8rem;">
                <a href="{{ route('terms.and.conditions') }}" style="color: #64748b; text-decoration: none;">Syarat & Ketentuan</a>
                <a href="{{ route('privacy.policy') }}" style="color: #64748b; text-decoration: none;">Kebijakan Privasi</a>
                <a href="{{ route('disclaimer') }}" style="color: #64748b; text-decoration: none;">Disclaimer</a>
            </nav>
        </div>
    </footer>
    @yield('scripts')
</body>
</html>

