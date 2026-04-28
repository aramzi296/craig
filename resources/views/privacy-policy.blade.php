@extends('layouts.app')

@section('title', 'Kebijakan Privasi – sebatam.com')

@section('content')
<div class="legal-page">
    <!-- Compact Hero -->
    <section class="hero" style="background: linear-gradient(rgba(219, 234, 254, 0.7), rgba(219, 234, 254, 0.7)), url('{{ asset('batam-hero.jpg') }}') no-repeat center center; background-size: cover; border-bottom: 1px solid #e5e7eb; padding: 60px 0;">
        <div class="container" style="max-width: 800px; text-align: center;">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 10px; letter-spacing: -0.02em;">Kebijakan Privasi</h1>
            <p style="font-size: 0.95rem; color: #64748b; font-weight: 600;">Terakhir Diperbarui: 18 Februari 2026</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="legal-section" style="padding: 50px 0; background: #ffffff;">
        <div class="container" style="max-width: 800px;">
            <div style="background: white; padding: 0; border-radius: 0;">
                <div class="prose" style="color: #334155; line-height: 1.6;">
                    <p style="font-size: 1rem; color: #475569; margin-bottom: 30px;">
                        Di <strong>SEBATAM</strong>, kami sangat menghargai privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda.
                    </p>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--primary); margin-bottom: 12px;">1. Informasi yang Kami Kumpulkan</h2>
                        <ul style="list-style: none; padding: 0; font-size: 0.95rem;">
                            <li style="display: flex; gap: 10px; margin-bottom: 8px;"><i class="fas fa-check-circle" style="color: var(--primary); margin-top: 4px;"></i> <span><strong>Identitas:</strong> Nama lengkap dan deskripsi profil.</span></li>
                            <li style="display: flex; gap: 10px; margin-bottom: 8px;"><i class="fas fa-check-circle" style="color: var(--primary); margin-top: 4px;"></i> <span><strong>Kontak:</strong> Alamat email dan nomor WhatsApp.</span></li>
                            <li style="display: flex; gap: 10px;"><i class="fas fa-check-circle" style="color: var(--primary); margin-top: 4px;"></i> <span><strong>Data Usaha:</strong> Nama usaha, logo, dan foto produk.</span></li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--primary); margin-bottom: 12px;">2. Penggunaan Informasi</h2>
                        <ul style="list-style: none; padding: 0; font-size: 0.95rem;">
                            <li style="display: flex; gap: 10px; margin-bottom: 8px;"><i class="fas fa-arrow-right" style="color: #64748b; margin-top: 4px; font-size: 0.8rem;"></i> <span>Menampilkan profil iklan Anda di website.</span></li>
                            <li style="display: flex; gap: 10px; margin-bottom: 8px;"><i class="fas fa-arrow-right" style="color: #64748b; margin-top: 4px; font-size: 0.8rem;"></i> <span>Menghubungi Anda terkait iklan yang diterbitkan.</span></li>
                            <li style="display: flex; gap: 10px;"><i class="fas fa-arrow-right" style="color: #64748b; margin-top: 4px; font-size: 0.8rem;"></i> <span>Meningkatkan keamanan situs kami.</span></li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--primary); margin-bottom: 12px;">3. Keamanan Data</h2>
                        <p style="font-size: 0.95rem;">Kami mengelola data Anda pada infrastruktur server dengan standar keamanan SSL/HTTPS. Kami berkomitmen melindungi data Anda sesuai standar industri.</p>
                    </div>

                    <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 40px 0;">

                    <div style="text-align: center; background: #f8fafc; padding: 35px; border-radius: 12px; border: 1px solid #f1f5f9;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 8px;">Butuh Bantuan?</h3>
                        <p style="color: #64748b; margin-bottom: 20px; font-size: 0.9rem;">Hubungi admin kami jika ada pertanyaan terkait keamanan data.</p>
                        
                        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                            <a href="https://wa.me/{{ config('services.whatsapp.bot_number') }}" target="_blank" 
                               style="display: inline-flex; align-items: center; gap: 8px; background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 0.9rem;">
                                <i class="fab fa-whatsapp"></i>
                                WhatsApp
                            </a>
                            <a href="mailto:admin@sebatam.com" 
                               style="display: inline-flex; align-items: center; gap: 8px; background: white; color: #334155; border: 1px solid #e2e8f0; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                                <i class="far fa-envelope"></i>
                                Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div>
@endsection

