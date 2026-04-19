@extends('layouts.app')

@section('title', 'Syarat & Ketentuan – sebatam.com')

@section('content')
<div class="legal-page">
    <!-- Compact Hero -->
    <section style="background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%); padding: 50px 20px; text-align: center; color: white; position: relative; overflow: hidden;">
        <div class="container" style="position: relative; z-index: 2;">
            <h1 style="font-size: 2.5rem; font-weight: 850; margin-bottom: 10px; letter-spacing: -1px;">Syarat & Ketentuan</h1>
            <p style="font-size: 1.1rem; opacity: 0.9;">Terakhir Diperbarui: 30 Maret 2026</p>
        </div>
        <div style="position: absolute; top: -20px; right: -20px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    </section>

    <!-- Content Section -->
    <section class="legal-section">
        <div class="container">
            <div class="legal-card glass">
                <div class="prose" style="color: var(--text); line-height: 1.8;">
                    <p style="font-size: 1.1rem; color: var(--text-muted); margin-bottom: 30px;">
                        Selamat datang di <strong>{{ config('app.name') }}</strong>. Sebelum Anda mulai menggunakan layanan kami untuk memposting sesuatu di platform ini, mohon luangkan waktu sejenak untuk membaca Syarat & Ketentuan berikut.
                    </p>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">1</span>
                            Penerimaan Ketentuan
                        </h2>
                        <p>Dengan mengakses atau menggunakan situs web {{ config('app.name') }}, Anda menyatakan bahwa Anda telah membaca, memahami, dan setuju untuk terikat oleh Syarat & Ketentuan ini. Jika Anda tidak setuju, mohon untuk tidak melanjutkan penggunaan layanan kami.</p>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">2</span>
                            Akun Pengguna & Keamanan
                        </h2>
                        <ul style="list-style: none; padding: 0;">
                            <li style="display: flex; gap: 12px; margin-bottom: 10px;">
                                <i class="fas fa-check" style="color: #10b981; margin-top: 6px;"></i>
                                <span>Anda wajib menggunakan data yang valid saat mendaftar, terutama nomor WhatsApp untuk proses verifikasi.</span>
                            </li>
                            <li style="display: flex; gap: 12px; margin-bottom: 10px;">
                                <i class="fas fa-check" style="color: #10b981; margin-top: 6px;"></i>
                                <span>Anda bertanggung jawab penuh atas kerahasiaan kata sandi dan semua aktivitas yang terjadi di bawah akun Anda.</span>
                            </li>
                            <li style="display: flex; gap: 12px;">
                                <i class="fas fa-check" style="color: #10b981; margin-top: 6px;"></i>
                                <span>Kami berhak menonaktifkan akun yang terindikasi melakukan penipuan atau pelanggaran hukum lainnya.</span>
                            </li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">3</span>
                            Aturan Publikasi
                        </h2>
                        <p style="margin-bottom: 15px;">Semua konten yang Anda unggah harus memenuhi kriteria berikut:</p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div style="padding: 15px; background: #f8fafc; border-radius: 12px; border-left: 4px solid var(--secondary);">
                                <strong style="display: block; margin-bottom: 5px;">Akurasi Data</strong>
                                <span style="font-size: 0.9rem; color: var(--text-muted);">Informasi harus akurat dan tidak menyesatkan.</span>
                            </div>
                            <div style="padding: 15px; background: #f8fafc; border-radius: 12px; border-left: 4px solid #ef4444;">
                                <strong style="display: block; margin-bottom: 5px;">Konten Terlarang</strong>
                                <span style="font-size: 0.9rem; color: var(--text-muted);">Dilarang memposting barang/layanan ilegal.</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <span style="background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">4</span>
                            Moderasi & Hak Admin
                        </h2>
                        <p>Tim {{ config('app.name') }} berhak mengedit, menangguhkan, atau menghapus listing tanpa pemberitahuan jika ditemukan adanya ketidaksesuaian dengan standar komunitas kami demi keamanan bersama.</p>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--border); margin: 40px 0;">

                    <div style="text-align: center;">
                        <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 15px;">Hubungi Kami</h3>
                        <p style="color: var(--text-muted); margin-bottom: 20px;">Jika ada pertanyaan terkait konten, silakan hubungi tim kami.</p>
                        <a href="mailto:admin@sebatam.com" class="btn btn-primary" style="padding: 12px 30px;">
                            <i class="fas fa-envelope" style="margin-right: 8px;"></i> admin@sebatam.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

