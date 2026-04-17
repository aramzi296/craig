@extends('layouts.app')

@section('title', 'Tentang Kami - Sebatam.com')

@section('content')
<div class="about-page">
    <!-- Hero Section -->
    <section class="hero-about" style="background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%); padding: 60px 20px 40px 20px; text-align: center; color: white; position: relative; overflow: hidden;">

        <div class="container" style="position: relative; z-index: 2;">
            <h1 style="font-size: 2.8rem; font-weight: 850; margin-bottom: 12px; letter-spacing: -1.5px; line-height: 1.2;">Satu Platform,<br>Semua Informasi Batam.</h1>

            <p style="font-size: 1.25rem; opacity: 0.9; max-width: 700px; margin: 0 auto; line-height: 1.6;">
                Sebatam.com hadir sebagai pusat rujukan digital dan papan pengumuman modern untuk menghubungkan seluruh kebutuhan warga Batam dalam satu ekosistem yang terpercaya.
            </p>
        </div>
        <!-- Decorative elements -->
        <div style="position: absolute; top: -50px; left: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -100px; right: -100px; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>

    </section>

    <!-- Visi & Misi Section -->
    <section style="padding: 40px 20px 80px 20px; background: var(--background);">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <div class="glass" style="padding: 40px; border-radius: 24px; background: white; border: 1px solid var(--border); box-shadow: var(--shadow);">
                    <div style="width: 60px; height: 60px; background: #eff6ff; color: var(--primary); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 25px;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 15px; color: var(--text);">Visi Kami</h2>
                    <p style="color: var(--text-muted); line-height: 1.7; font-size: 1.05rem;">
                        Menjadi ekosistem digital utama dan pusat rujukan informasi bagi seluruh warga Batam dalam memenuhi segala kebutuhan harian, bisnis, dan layanan lokal yang transparan dan terpercaya.
                    </p>
                </div>

                <div class="glass" style="padding: 40px; border-radius: 24px; background: white; border: 1px solid var(--border); box-shadow: var(--shadow);">
                    <div style="width: 60px; height: 60px; background: #fff7ed; color: var(--secondary); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 25px;">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 15px; color: var(--text);">Misi Kami</h2>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="display: flex; gap: 12px; margin-bottom: 12px; color: var(--text-muted);">
                            <i class="fas fa-check-circle" style="color: #10b981; margin-top: 5px;"></i>
                            <span>Mendemokrasikan akses informasi digital bagi seluruh warga Batam.</span>
                        </li>
                        <li style="display: flex; gap: 12px; margin-bottom: 12px; color: var(--text-muted);">
                            <i class="fas fa-check-circle" style="color: #10b981; margin-top: 5px;"></i>
                            <span>Mendukung UMKM lokal melalui digitalisasi layanan dan promosi.</span>
                        </li>
                        <li style="display: flex; gap: 12px; color: var(--text-muted);">
                            <i class="fas fa-check-circle" style="color: #10b981; margin-top: 5px;"></i>
                            <span>Menghubungkan penyedia jasa dan pengguna secara cepat dan efisien.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section style="padding: 80px 20px; background: white;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 60px;">
                <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px;">Apa yang Kami Tawarkan?</h2>
                <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto;">Semua yang Anda cari di Batam, kini tersedia dalam jangkauan satu klik.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
                @php
                    $features = [
                        ['icon' => 'shopping-bag', 'title' => 'Jual & Beli', 'color' => '#3b82f6'],
                        ['icon' => 'store', 'title' => 'Direktori Bisnis', 'color' => '#10b981'],
                        ['icon' => 'briefcase', 'title' => 'Lowongan Kerja', 'color' => '#8b5cf6'],
                        ['icon' => 'user-check', 'title' => 'Cari Kerja', 'color' => '#f59e0b'],
                        ['icon' => 'tag', 'title' => 'Promo & Diskon', 'color' => '#ef4444'],
                        ['icon' => 'calendar-alt', 'title' => 'Agenda Kota', 'color' => '#6366f1'],
                        ['icon' => 'box-open', 'title' => 'Barang Hilang', 'color' => '#f97316'],
                        ['icon' => 'bullhorn', 'title' => 'Pengumuman', 'color' => '#14b8a6'],
                    ];
                @endphp

                @foreach($features as $f)
                <div style="padding: 30px; border-radius: var(--radius); border: 1px solid var(--border); text-align: center; transition: all 0.3s ease; cursor: default;" 
                     onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='var(--primary)';" 
                     onmouseout="this.style.transform='none'; this.style.borderColor='var(--border)';">
                    <div style="width: 50px; height: 50px; background: {{ $f['color'] }}15; color: {{ $f['color'] }}; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin: 0 auto 15px;">
                        <i class="fas fa-{{ $f['icon'] }}"></i>
                    </div>
                    <h3 style="font-size: 1.1rem; font-weight: 700;">{{ $f['title'] }}</h3>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Bottom CTA -->
    <section style="padding: 100px 20px; background: var(--background);">
        <div class="container text-center">
            <div class="glass" style="max-width: 900px; margin: 0 auto; padding: 60px 40px; border-radius: 32px; background: white; border: 1px solid var(--border); box-shadow: var(--shadow-lg);">
                <h2 style="font-size: 2.8rem; font-weight: 850; margin-bottom: 20px;">Siap Bergabung dengan Komunitas?</h2>
                <p style="color: var(--text-muted); font-size: 1.15rem; max-width: 600px; margin: 0 auto 40px; line-height: 1.6;">
                    Pasang pengumuman, promosikan bisnis Anda, atau cari kebutuhan lainnya sekarang juga secara gratis.
                </p>
                <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                    <a href="{{ route('listings.create') }}" class="btn btn-primary" style="padding: 16px 32px; font-size: 1.1rem;">Mulai Posting Sekarang</a>
                    <a href="{{ route('home') }}" class="btn btn-outline" style="padding: 16px 32px; font-size: 1.1rem;">Lihat Informasi Terbaru</a>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    @media (max-width: 768px) {
        .hero-about h1 { font-size: 2.5rem !important; }
        .hero-about p { font-size: 1.1rem !important; }
    }
</style>
@endsection



