@extends('layouts.app')

@section('title', 'Syarat & Ketentuan – sebatam.com')

@section('content')
<div class="legal-page">
    <!-- Compact Hero -->
    <section class="hero" style="background: linear-gradient(rgba(219, 234, 254, 0.7), rgba(219, 234, 254, 0.7)), url('{{ asset('batam-hero.jpg') }}') no-repeat center center; background-size: cover; border-bottom: 1px solid #e5e7eb; padding: 60px 0;">
        <div class="container" style="max-width: 800px; text-align: center;">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 10px; letter-spacing: -0.02em;">Syarat & Ketentuan</h1>
            <p style="font-size: 0.95rem; color: #64748b; font-weight: 600;">Terakhir Diperbarui: 30 Maret 2026</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="legal-section" style="padding: 50px 0; background: #ffffff;">
        <div class="container" style="max-width: 800px;">
            <div style="background: white; padding: 0;">
                <div class="prose" style="color: #334155; line-height: 1.6;">
                    <p style="font-size: 1rem; color: #475569; margin-bottom: 30px;">
                        Selamat datang di <strong>SEBATAM</strong>. Mohon luangkan waktu sejenak untuk membaca Syarat & Ketentuan berikut sebelum menggunakan layanan kami.
                    </p>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: var(--primary); color: white; width: 25px; height: 25px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">1</span>
                            Penerimaan Ketentuan
                        </h2>
                        <p style="font-size: 0.95rem;">Dengan mengakses SEBATAM, Anda setuju untuk terikat oleh Syarat & Ketentuan ini. Jika Anda tidak setuju, mohon untuk tidak melanjutkan penggunaan layanan.</p>
                    </div>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: var(--primary); color: white; width: 25px; height: 25px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">2</span>
                            Akun & Keamanan
                        </h2>
                        <ul style="list-style: none; padding: 0; font-size: 0.95rem;">
                            <li style="display: flex; gap: 10px; margin-bottom: 8px;">
                                <i class="fas fa-check" style="color: #10b981; margin-top: 4px;"></i>
                                <span>Wajib menggunakan nomor WhatsApp yang valid untuk verifikasi.</span>
                            </li>
                            <li style="display: flex; gap: 10px; margin-bottom: 8px;">
                                <i class="fas fa-check" style="color: #10b981; margin-top: 4px;"></i>
                                <span>Anda bertanggung jawab penuh atas aktivitas yang terjadi di bawah akun Anda.</span>
                            </li>
                            <li style="display: flex; gap: 10px;">
                                <i class="fas fa-check" style="color: #10b981; margin-top: 4px;"></i>
                                <span>Kami berhak menonaktifkan akun yang melanggar aturan hukum.</span>
                            </li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: var(--primary); color: white; width: 25px; height: 25px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">3</span>
                            Aturan Publikasi
                        </h2>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div style="padding: 15px; background: #f8fafc; border-radius: 8px; border-left: 3px solid var(--primary);">
                                <strong style="display: block; font-size: 0.9rem; margin-bottom: 3px;">Akurasi Data</strong>
                                <span style="font-size: 0.85rem; color: #64748b;">Informasi harus benar dan tidak menyesatkan.</span>
                            </div>
                            <div style="padding: 15px; background: #f8fafc; border-radius: 8px; border-left: 3px solid #ef4444;">
                                <strong style="display: block; font-size: 0.9rem; margin-bottom: 3px;">Konten Terlarang</strong>
                                <span style="font-size: 0.85rem; color: #64748b;">Dilarang memposting barang/layanan ilegal.</span>
                            </div>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 40px 0;">

                    <div style="text-align: center; background: #f8fafc; padding: 35px; border-radius: 12px; border: 1px solid #f1f5f9;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 15px;">Punya Pertanyaan?</h3>
                        <a href="mailto:admin@sebatam.com" class="btn btn-primary" style="padding: 10px 25px; font-weight: 700; font-size: 0.9rem;">
                            <i class="fas fa-envelope" style="margin-right: 8px;"></i> admin@sebatam.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div>
@endsection

