@extends('layouts.app')

@section('title', 'Syarat & Ketentuan – ' . parse_url(config('app.url'), PHP_URL_HOST))

@section('content')
<div class="legal-page">
    <!-- Header Title -->
    <div class="container" style="max-width: 900px; padding-top: 40px; margin-bottom: 0;">
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #111827; margin-bottom: 6px;">Syarat & Ketentuan</h1>
        <p style="font-size: 0.9rem; color: #64748b; font-weight: 600; margin-bottom: 0;">Terakhir Diperbarui: 30 Maret 2026</p>
    </div>

    <!-- Content Section -->
    <section class="legal-section" style="padding: 50px 0; background: #ffffff;">
        <div class="container" style="max-width: 900px;">
            <div style="background: white; padding: 0;">
                <div class="prose" style="color: #334155; line-height: 1.6;">
                    <p style="font-size: 1rem; color: #475569; margin-bottom: 30px;">
                        Selamat datang di <strong>{{ config('app.name') }}</strong>. Mohon luangkan waktu sejenak untuk membaca Syarat & Ketentuan berikut sebelum menggunakan layanan kami.
                    </p>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: var(--primary); color: white; width: 25px; height: 25px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">1</span>
                            Penerimaan Ketentuan
                        </h2>
                        <p style="font-size: 0.95rem;">Dengan mengakses {{ config('app.name') }}, Anda setuju untuk terikat oleh Syarat & Ketentuan ini. Jika Anda tidak setuju, mohon untuk tidak melanjutkan penggunaan layanan.</p>
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

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: var(--primary); color: white; width: 25px; height: 25px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">4</span>
                            Informasi Publik & Privasi
                        </h2>
                        <p style="font-size: 0.95rem; margin-bottom: 10px;">
                            Harap diperhatikan bahwa seluruh informasi yang Anda berikan saat memasang iklan, baik berupa <strong>nomor telepon/WhatsApp, email, deskripsi/keterangan, tautan (link), maupun foto-foto</strong>, merupakan informasi yang memang Anda inginkan agar diketahui oleh publik.
                        </p>
                        <p style="font-size: 0.95rem; color: #e53e3e; font-weight: 700;">
                            Jika Anda tidak ingin informasi pribadi atau data tertentu dilihat dan diakses oleh publik, maka jangan memasukkan, mendaftarkan, atau mempublikasikannya ke dalam website ini.
                        </p>
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
@endsection
