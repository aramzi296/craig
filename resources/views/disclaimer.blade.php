@extends('layouts.app')

@section('title', 'Disclaimer – sebatam.com')

@section('content')
<div class="legal-page">
    <!-- Compact Hero -->
    <section style="background: linear-gradient(135deg, #64748b 0%, #334155 100%); padding: 50px 20px; text-align: center; color: white; position: relative; overflow: hidden;">
        <div class="container" style="position: relative; z-index: 2;">
            <h1 style="font-size: 2.5rem; font-weight: 850; margin-bottom: 10px; letter-spacing: -1px;">Disclaimer</h1>
            <p style="font-size: 1.1rem; opacity: 0.9;">Penyangkalan & batasan tanggung jawab</p>
        </div>
        <div style="position: absolute; top: -20px; left: -20px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
    </section>

    <!-- Content Section -->
    <section class="legal-section">
        <div class="container">
            <div class="legal-card glass">
                <div class="prose" style="color: var(--text); line-height: 1.8;">
                    
                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: #334155; margin-bottom: 15px;">1. Informasi Umum</h2>
                        <p>Informasi yang disediakan di sebatam.com hanya untuk tujuan informasi umum. Meskipun kami berusaha menjaga informasi tetap akurat dan mutakhir, kami tidak memberikan jaminan dalam bentuk apa pun, tersurat maupun tersirat, tentang kelengkapan, akurasi, atau keandalan informasi yang tercantum.</p>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: #334155; margin-bottom: 15px;">2. Tanggung Jawab Pengguna</h2>
                        <p>Sebatam.com adalah platform iklan dan pengumuman online. Segala bentuk transaksi, interaksi, atau kesepakatan yang terjadi antara pengguna (penjual dan pembeli) adalah tanggung jawab penuh masing-masing pihak. Kami tidak terlibat dalam proses transaksi dan tidak bertanggung jawab atas kerugian atau penipuan yang mungkin terjadi.</p>
                        <p>Dengan mempublikasikan nomor WhatsApp pada iklan, Anda memahami bahwa nomor tersebut dapat dihubungi oleh siapa saja. Sebatam.com tidak bertanggung jawab atas pesan-pesan yang tidak relevan, pesan spam, atau gangguan lain yang mungkin muncul akibat publikasi nomor kontak tersebut.</p>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: #334155; margin-bottom: 15px;">3. Konten Pihak Ketiga</h2>
                        <p>Situs ini mungkin berisi tautan ke situs web pihak ketiga yang tidak dikendalikan oleh sebatam.com. Kami tidak memiliki kendali atas isi dan ketersediaan situs-situs tersebut. Pencantuman tautan tidak selalu menyiratkan rekomendasi atau dukungan terhadap pandangan yang diungkapkan di dalamnya.</p>
                    </div>

                    <div style="margin-bottom: 40px;">
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: #334155; margin-bottom: 15px;">4. Risiko Penggunaan</h2>
                        <p>Penggunaan informasi atau materi apa pun di situs web ini sepenuhnya merupakan risiko Anda sendiri. Kami tidak bertanggung jawab atas segala kerusakan, kehilangan data, atau kerugian finansial yang timbul dari penggunaan atau ketidakmampuan untuk menggunakan layanan kami.</p>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--border); margin: 40px 0;">

                    <div style="text-align: center; background: #f8fafc; padding: 30px; border-radius: 16px;">
                        <p style="color: var(--text-muted); margin-bottom: 0;">Dengan menggunakan situs kami, Anda dengan ini menyetujui disclaimer kami dan menyetujui ketentuan-ketentuannya.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
