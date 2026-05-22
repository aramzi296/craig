@extends('layouts.app')

@section('title', 'Tentang - sebatam.com')

@section('content')
<div class="read-me-page">
    <!-- Header Title -->
    <div class="container" style="max-width: 900px; padding-top: 40px; margin-bottom: 20px;">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #111827; margin-bottom: 0;">Tentang Kami</h1>
    </div>

    <!-- About & Mission Section (Merged) -->
    <section style="padding: 60px 0; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
        <div class="container" style="max-width: 900px;">
            <!-- Barelang Bridge Banner -->
            <div style="margin-bottom: 40px; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.15); border: 1px solid #e2e8f0; position: relative;">
                <img src="{{ asset('jembatan-barelang-800x600.png') }}" alt="Jembatan Barelang Batam" style="width: 100%; height: auto; aspect-ratio: 16/7; object-fit: cover; display: block;" />
                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(15, 23, 42, 0.8)); padding: 25px 30px; display: flex; align-items: flex-end;">
                    <span style="color: #ffffff; font-size: 0.9rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; text-shadow: 0 2px 4px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-map-marker-alt" style="color: #0ea5e9;"></i> Batam, Kepulauan Riau
                    </span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 50px; align-items: start; margin-bottom: 40px;">
                <!-- Left: About Text -->
                <div>
                    
                    <p style="color: #475569; line-height: 1.8; font-size: 1rem;">
                        Dipersembahkan oleh <a href="https://sebatam.com" style="color: var(--primary); font-weight: 700; text-decoration: none;">sebatam.com</a>. Platform ini hadir sebagai <strong>rujukan digital utama</strong> yang dirancang khusus untuk mempermudah siapa saja menemukan berbagai jenis usaha, UMKM, dan layanan lokal di Batam secara cepat, akurat, dan efisien.
                    </p>
                    
                    <p style="color: #475569; line-height: 1.8; font-size: 1rem; margin-top: 20px;">
                        Dengan direktori bisnis yang terus berkembang, {{ config('app.name') }} menghubungkan para pelaku usaha lokal langsung dengan calon pelanggan potensial. Mulai dari kuliner, jasa profesional, toko retail, hingga industri kreatif—semua bidang usaha kini terkoneksi secara mudah dalam satu wadah terintegrasi.
                    </p>

                    <p style="color: #475569; line-height: 1.8; font-size: 1rem; margin-top: 20px;">
                        Kami berkomitmen mendorong pertumbuhan ekonomi lokal dengan menyediakan ruang promosi digital yang modern, inklusif, dan berdaya guna tinggi, agar setiap usaha di Batam dapat tumbuh dan lebih mudah ditemukan oleh masyarakat luas.
                    </p>
                </div>

                <!-- Right: Vision & Mission -->
                <div style="background: #f8fafc; padding: 30px; border-radius: 20px; border: 1px solid #f1f5f9;">
                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-eye"></i> Visi Kami
                        </h3>
                        <p style="font-size: 0.95rem; color: #475569; line-height: 1.6; margin: 0;">
                            Menjadi rujukan digital utama dan pusat direktori bisnis terlengkap di Batam yang menghubungkan masyarakat dengan seluruh ekosistem usaha lokal secara mudah dan terpercaya.
                        </p>
                    </div>
                    <div>
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-bullseye"></i> Misi Kami
                        </h3>
                        <ul style="margin: 0; padding: 0; list-style: none; font-size: 0.9rem; color: #475569;">
                            <li style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                                <span>Menyediakan direktori bisnis terlengkap dan terupdate untuk segala jenis usaha di Batam.</span>
                            </li>
                            <li style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                                <span>Mendorong digitalisasi UMKM lokal agar memiliki daya saing digital yang kuat.</span>
                            </li>
                            <li style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                                <span>Menghubungkan masyarakat dengan penyedia jasa dan produk lokal secara efisien.</span>
                            </li>
                            <li style="display: flex; gap: 10px;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                                <span>Memberikan akses promosi yang adil, mudah, dan efektif bagi setiap pelaku usaha.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
