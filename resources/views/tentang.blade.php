@extends('layouts.app')

@section('title', 'Tentang - sebatam.com')

@section('content')
<div class="read-me-page">
    <!-- Header Title -->
    <div class="container" style="max-width: 900px; padding-top: 40px; margin-bottom: 20px;">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #111827; margin-bottom: 0;">Tentang & Bantuan</h1>
    </div>

    <!-- About & Mission Section (Merged) -->
    <section style="padding: 60px 0; background: #ffffff; border-bottom: 1px solid #f1f5f9;">
        <div class="container" style="max-width: 900px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 50px; align-items: start; margin-bottom: 40px;">
                <!-- Left: About Text -->
                <div>
                    <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 20px;">Tentang {{ config('app.name') }}</h2>
                    <p style="color: #475569; line-height: 1.8; font-size: 1rem;">
                        Dipersembahkan oleh <a href="https://sebatam.com" style="color: var(--primary); font-weight: 700; text-decoration: none;">sebatam.com</a>. Platform ini hadir sebagai pusat rujukan digital dan papan pengumuman modern untuk memfasilitasi jual beli barang, jasa, serta pengumuman komunitas bagi seluruh warga Batam secara ringkas dan efisien.
                    </p>
                    
                   

                    <p style="color: #475569; line-height: 1.8; font-size: 1rem; margin-top: 20px;">
                        Kami percaya bahwa kemudahan akses informasi adalah kunci pertumbuhan ekonomi lokal. Dengan teknologi yang simpel namun tepat guna, kami menghubungkan ribuan penjual dan pembeli setiap harinya di Batam.
                    </p>
                </div>

                <!-- Right: Vision & Mission -->
                <div style="background: #f8fafc; padding: 30px; border-radius: 20px; border: 1px solid #f1f5f9;">
                    <div style="margin-bottom: 25px;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-eye"></i> Visi Kami
                        </h3>
                        <p style="font-size: 0.95rem; color: #475569; line-height: 1.6; margin: 0;">
                            Menjadi ekosistem digital utama dan pusat rujukan informasi bagi seluruh warga Batam dalam memenuhi segala kebutuhan harian dan layanan lokal.
                        </p>
                    </div>
                    <div>
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-bullseye"></i> Misi Kami
                        </h3>
                        <ul style="margin: 0; padding: 0; list-style: none; font-size: 0.9rem; color: #475569;">
                            <li style="display: flex; gap: 10px; margin-bottom: 8px;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                                <span>Akses informasi digital yang adil bagi warga.</span>
                            </li>
                            <li style="display: flex; gap: 10px; margin-bottom: 8px;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                                <span>Mendukung digitalisasi UMKM lokal Batam.</span>
                            </li>
                            <li style="display: flex; gap: 10px;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i>
                                <span>Menghubungkan layanan lokal secara efisien.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
