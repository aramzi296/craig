<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Sebatam.com')</title>
    <link rel="shortcut icon" href="{{ asset('favicon.webp') }}" type="image/webp" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    @vite(['resources/css/tailwind.css', 'resources/css/app.css', 'resources/js/app.js'])

</head>
<body>
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
                
                <div class="nav-divider"></div>

                @guest
                    <a href="{{ route('login') }}" style="font-weight: 600; color: var(--text);">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                @else
                    <div class="nav-user-info">
                        <span class="nav-greeting">Halo, <strong>{{ auth()->user()->name }}</strong></span>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline">Dashboard</a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" style="background: var(--secondary);">Admin</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-logout">Keluar</button>
                        </form>
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
                <a href="{{ route('about') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Tentang</a>
                |
                <a href="{{ route('terms.and.conditions') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Syarat & Ketentuan</a>
                |
                <a href="{{ route('privacy.policy') }}" style="color: #94a3b8; margin: 0 10px; text-decoration: none;">Kebijakan Privasi</a>
            </nav>
        </div>
    </footer>
</body>
</html>

