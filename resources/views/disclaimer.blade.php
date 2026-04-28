@extends('layouts.app')

@section('title', 'Disclaimer – sebatam.com')

@section('content')
<div class="legal-page">
    <!-- Compact Hero -->
    <section class="hero" style="background: linear-gradient(rgba(219, 234, 254, 0.7), rgba(219, 234, 254, 0.7)), url('{{ asset('batam-hero.jpg') }}') no-repeat center center; background-size: cover; border-bottom: 1px solid #e5e7eb; padding: 60px 0;">
        <div class="container" style="max-width: 800px; text-align: center;">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 10px; letter-spacing: -0.02em;">Disclaimer</h1>
            <p style="font-size: 1.1rem; color: #374151; font-weight: 500;">Penyangkalan & batasan tanggung jawab SEBATAM</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="legal-section" style="padding: 50px 0; background: #ffffff;">
        <div class="container" style="max-width: 800px;">
            <div style="background: white; padding: 0;">
                <div class="prose" style="color: #334155; line-height: 1.6;">
                    
                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px;">1. Informasi Umum</h2>
                        <p style="font-size: 0.95rem;">Informasi yang disediakan di SEBATAM hanya untuk tujuan informasi umum. Meskipun kami berusaha menjaga informasi tetap akurat, kami tidak memberikan jaminan dalam bentuk apa pun tentang kelengkapan atau keandalan informasi yang tercantum.</p>
                    </div>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px;">2. Tanggung Jawab Pengguna</h2>
                        <p style="font-size: 0.95rem; margin-bottom: 12px;">SEBATAM adalah platform iklan dan pengumuman online. Segala bentuk transaksi atau interaksi yang terjadi antara pengguna adalah tanggung jawab penuh masing-masing pihak. Kami tidak terlibat dalam proses transaksi dan tidak bertanggung jawab atas kerugian yang mungkin terjadi.</p>
                        <p style="font-size: 0.95rem;">Dengan mempublikasikan nomor WhatsApp, Anda memahami bahwa nomor tersebut dapat dihubungi oleh siapa saja. SEBATAM tidak bertanggung jawab atas pesan spam atau gangguan lain yang mungkin timbul.</p>
                    </div>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px;">3. Konten Pihak Ketiga</h2>
                        <p style="font-size: 0.95rem;">Situs ini mungkin berisi tautan ke situs web pihak ketiga. Kami tidak memiliki kendali atas isi dan ketersediaan situs-situs tersebut. Pencantuman tautan tidak selalu menyiratkan rekomendasi terhadap isi di dalamnya.</p>
                    </div>

                    <div style="margin-bottom: 35px;">
                        <h2 style="font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 12px;">4. Risiko Penggunaan</h2>
                        <p style="font-size: 0.95rem;">Penggunaan informasi di situs ini sepenuhnya merupakan risiko Anda sendiri. Kami tidak bertanggung jawab atas segala kerusakan atau kerugian finansial yang timbul dari penggunaan layanan kami.</p>
                    </div>

                    <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 40px 0;">

                    <div style="text-align: center; background: #f8fafc; padding: 30px; border-radius: 12px; border: 1px solid #f1f5f9;">
                        <p style="color: #64748b; margin-bottom: 0; font-size: 0.9rem; font-weight: 500;">Dengan menggunakan situs kami, Anda dengan ini menyetujui disclaimer kami dan menyetujui seluruh ketentuan-ketentuannya.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div>
@endsection
