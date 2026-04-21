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
                            <li style="display: flex; gap: 12px; margin-bottom: 10px;"><i class="fas fa-arrow-right" style="color: var(--secondary); margin-top: 6px;"></i> <span>Menampilkan profil iklan dan pengumuman Anda di halaman website.</span></li>
                            <li style="display: flex; gap: 12px; margin-bottom: 10px;"><i class="fas fa-arrow-right" style="color: var(--secondary); margin-top: 6px;"></i> <span>Menghubungi Anda terkait iklan dan pengumuman yang Anda terbitkan.</span></li>
                            <li style="display: flex; gap: 12px;"><i class="fas fa-arrow-right" style="color: var(--secondary); margin-top: 6px;"></i> <span>Meningkatkan performa dan keamanan situs kami.</span></li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px;">3. Keamanan Data</h2>
                        <p>Kami mengelola data Anda pada infrastruktur server pribadi (VPS) dengan standar keamanan SSL/HTTPS. Kami berkomitmen melindungi data Anda, namun harap diingat bahwa keamanan mutlak di internet tidak dapat dijamin sepenuhnya.</p>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px;">4. Hak Anda atas Data</h2>
                        <p>Anda berhak untuk mengakses, memperbarui, atau menghapus akun dan data pribadi Anda kapan saja melalui dashboard pengguna atau dengan menghubungi admin kami.</p>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--border); margin: 40px 0;">

                    <div style="text-align: center; background: #f8fafc; padding: 40px 30px; border-radius: 20px; border: 1px solid var(--border);">
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary); margin-bottom: 8px;">Butuh Bantuan?</h3>
                        <p style="color: var(--text-muted); margin-bottom: 25px; max-width: 500px; margin-left: auto; margin-right: auto;">Tim kami siap membantu Anda terkait pertanyaan kebijakan privasi atau keamanan data di platform kami.</p>
                        
                        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                            <a href="https://wa.me/{{ config('services.whatsapp.bot_number') }}" target="_blank" 
                               style="display: inline-flex; align-items: center; gap: 10px; background: #25D366; color: white; padding: 14px 28px; border-radius: 14px; text-decoration: none; font-weight: 700; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 15px rgba(37, 211, 102, 0.25); filter: drop-shadow(0 0 0 transparent);"
                               onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(37, 211, 102, 0.4)';"
                               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(37, 211, 102, 0.25)';"
                            >
                                <i class="fab fa-whatsapp" style="font-size: 1.4rem;"></i>
                                Chat Admin WhatsApp
                            </a>
                            
                            <a href="mailto:admin@sebatam.com" 
                               style="display: inline-flex; align-items: center; gap: 10px; background: white; color: var(--text); border: 1px solid var(--border); padding: 14px 28px; border-radius: 14px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;"
                               onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='var(--primary)';"
                               onmouseout="this.style.background='white'; this.style.borderColor='var(--border)';"
                            >
                                <i class="far fa-envelope"></i>
                                Email Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

