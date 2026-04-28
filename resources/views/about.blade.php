@extends('layouts.app')

@section('title', 'Tentang Kami - Sebatam.com')

@section('content')
<div class="about-page">
    <!-- Hero Section -->
    <section class="hero" style="background: linear-gradient(rgba(219, 234, 254, 0.7), rgba(219, 234, 254, 0.7)), url('{{ asset('batam-hero.jpg') }}') no-repeat center center; background-size: cover; border-bottom: 1px solid #e5e7eb; padding: 80px 0;">
        <div class="container" style="max-width: 850px; text-align: center;">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 12px; letter-spacing: -0.02em;">Satu Platform,<br>Semua Informasi Batam.</h1>
            <p style="font-size: 1.1rem; color: #374151; max-width: 650px; margin: 0 auto; line-height: 1.6; font-weight: 500;">
                SEBATAM hadir sebagai pusat rujukan digital dan papan pengumuman modern untuk seluruh warga Batam.
            </p>
        </div>
    </section>

    <!-- Visi & Misi Section -->
    <section class="legal-section" style="padding: 60px 0; background: #ffffff;">
        <div class="container" style="max-width: 900px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <div style="padding: 30px; border-radius: 12px; background: #f8fafc; border: 1px solid #f1f5f9;">
                    <div style="width: 45px; height: 45px; background: white; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 12px; color: #1e293b;">Visi Kami</h2>
                    <p style="color: #475569; line-height: 1.6; font-size: 0.95rem;">
                        Menjadi ekosistem digital utama dan pusat rujukan informasi bagi seluruh warga Batam dalam memenuhi segala kebutuhan harian dan layanan lokal.
                    </p>
                </div>

                <div style="padding: 30px; border-radius: 12px; background: #f8fafc; border: 1px solid #f1f5f9;">
                    <div style="width: 45px; height: 45px; background: white; color: var(--primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 12px; color: #1e293b;">Misi Kami</h2>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.95rem;">
                        <li style="display: flex; gap: 10px; margin-bottom: 8px; color: #475569;">
                            <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                            <span>Akses informasi digital yang adil bagi warga.</span>
                        </li>
                        <li style="display: flex; gap: 10px; margin-bottom: 8px; color: #475569;">
                            <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                            <span>Mendukung digitalisasi UMKM lokal Batam.</span>
                        </li>
                        <li style="display: flex; gap: 10px; color: #475569;">
                            <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                            <span>Menghubungkan layanan lokal secara efisien.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="legal-section" style="background: #f8fafc; padding: 60px 0;">
        <div class="container" style="max-width: 900px;">
            <div style="text-align: center; margin-bottom: 40px;">
                <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 10px; color: #1e293b;">Apa yang Kami Tawarkan?</h2>
                <p style="color: #64748b; font-size: 1rem;">Layanan digital lengkap untuk menunjang kebutuhan harian Anda.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                @php
                    $features = [
                        ['icon' => 'shopping-bag', 'title' => 'Jual & Beli'],
                        ['icon' => 'store', 'title' => 'Direktori Bisnis'],
                        ['icon' => 'briefcase', 'title' => 'Lowongan Kerja'],
                        ['icon' => 'user-check', 'title' => 'Cari Kerja'],
                        ['icon' => 'tag', 'title' => 'Promo & Diskon'],
                        ['icon' => 'calendar-alt', 'title' => 'Agenda Kota'],
                        ['icon' => 'box-open', 'title' => 'Barang Hilang'],
                        ['icon' => 'bullhorn', 'title' => 'Pengumuman'],
                    ];
                @endphp

                @foreach($features as $f)
                <div style="padding: 20px; border-radius: 8px; background: white; border: 1px solid #f1f5f9; text-align: center; transition: all 0.2s ease;" 
                     onmouseover="this.style.borderColor='var(--primary)';" 
                     onmouseout="this.style.borderColor='#f1f5f9';">
                    <div style="width: 40px; height: 40px; background: #eff6ff; color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin: 0 auto 12px;">
                        <i class="fas fa-{{ $f['icon'] }}"></i>
                    </div>
                    <h3 style="font-size: 0.9rem; font-weight: 700; color: #334155;">{{ $f['title'] }}</h3>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Bottom CTA -->
    <section style="padding: 60px 0; background: white; text-align: center;">
        <div class="container">
            <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 25px;">Siap Bergabung?</h2>
            <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('listings.create') }}" class="btn btn-primary" style="padding: 10px 25px; font-weight: 700; font-size: 0.95rem;">Mulai Posting Sekarang</a>
                <a href="{{ route('home') }}" class="btn btn-outline" style="padding: 10px 25px; font-weight: 700; font-size: 0.95rem;">Lihat Iklan Terbaru</a>
            </div>
        </div>
    </section>
</div>
</div>

<style>
    @media (max-width: 768px) {
        .hero-about h1 { font-size: 2.2rem !important; }
        .hero-about p { font-size: 1.1rem !important; }
        .legal-card { padding: 30px 20px !important; }
        .legal-section { padding: 40px 0 !important; }
    }
</style>
@endsection



