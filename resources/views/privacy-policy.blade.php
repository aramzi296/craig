@extends('layouts.app')

@section('title', 'Kebijakan Privasi – sebatam.com')

@section('content')
<div class="legal-page">
    <!-- Compact Hero -->
    <section style="background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%); padding: 50px 20px; text-align: center; color: white; position: relative; overflow: hidden;">
        <div class="container" style="position: relative; z-index: 2;">
            <h1 style="font-size: 2.5rem; font-weight: 850; margin-bottom: 10px; letter-spacing: -1px;">Kebijakan Privasi</h1>
            <p style="font-size: 1.1rem; opacity: 0.9;">Terakhir Diperbarui: 18 Februari 2026</p>
        </div>
        <div style="position: absolute; top: -20px; right: -20px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    </section>

    <!-- Content Section -->
    <section class="legal-section">
        <div class="container">
            <div class="legal-card glass">
                <div class="prose" style="color: var(--text); line-height: 1.8;">
                    <p style="font-size: 1.1rem; color: var(--text-muted); margin-bottom: 30px;">
                        Di <strong>{{ config('app.name') }}</strong>, kami sangat menghargai privasi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi Anda saat Anda menggunakan layanan direktori kami.
                    </p>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px;">1. Informasi yang Kami Kumpulkan</h2>
                        <p>Kami mengumpulkan informasi yang Anda berikan secara sukarela, termasuk:</p>
                        <ul style="list-style: none; padding: 0;">
                            <li style="display: flex; gap: 12px; margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: var(--primary); margin-top: 6px;"></i> <span><strong>Identitas:</strong> Nama lengkap dan deskripsi profil.</span></li>
                            <li style="display: flex; gap: 12px; margin-bottom: 10px;"><i class="fas fa-check-circle" style="color: var(--primary); margin-top: 6px;"></i> <span><strong>Kontak:</strong> Alamat email dan nomor WhatsApp.</span></li>
                            <li style="display: flex; gap: 12px;"><i class="fas fa-check-circle" style="color: var(--primary); margin-top: 6px;"></i> <span><strong>Data Usaha:</strong> Nama usaha, logo, dan foto produk.</span></li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px;">2. Penggunaan Informasi</h2>
                        <p>Informasi Anda digunakan untuk:</p>
                        <ul style="list-style: none; padding: 0;">
                            <li style="display: flex; gap: 12px; margin-bottom: 10px;"><i class="fas fa-arrow-right" style="color: var(--secondary); margin-top: 6px;"></i> <span>Menampilkan profil usaha Anda di direktori publik.</span></li>
                            <li style="display: flex; gap: 12px; margin-bottom: 10px;"><i class="fas fa-arrow-right" style="color: var(--secondary); margin-top: 6px;"></i> <span>Menghubungi Anda terkait verifikasi akun atau laporan.</span></li>
                            <li style="display: flex; gap: 12px;"><i class="fas fa-arrow-right" style="color: var(--secondary); margin-top: 6px;"></i> <span>Meningkatkan performa dan keamanan situs kami.</span></li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px;">3. Keamanan Data</h2>
                        <p>Kami mengelola data Anda pada infrastruktur server pribadi (VPS) dengan standar keamanan SSL/HTTPS. Kami berkomitmen melindungi data Anda, namun harap diingat bahwa keamanan mutlak di internet tidak dapat dijamin sepenuhnya.</p>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px;">4. Hak Anda atas Data</h2>
                        <p>Anda berhak untuk mengakses, memperbarui, atau meminta penghapusan akun dan data pribadi Anda kapan saja melalui dashboard pengguna atau dengan menghubungi admin kami.</p>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--border); margin: 40px 0;">

                    <div style="text-align: center; background: #f8fafc; padding: 30px; border-radius: 16px;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 10px;">Butuh Bantuan?</h3>
                        <p style="color: var(--text-muted); margin-bottom: 0;">Hubungi kami di <a href="mailto:admin@sebatam.com" style="color: var(--primary); font-weight: 600;">admin@sebatam.com</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

