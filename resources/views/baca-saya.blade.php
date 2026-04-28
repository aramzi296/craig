@extends('layouts.app')

@section('title', 'Panduan - sebatam.com')

@section('content')
<div class="read-me-page">
    <!-- Hero Section -->
    <section class="hero" style="background: linear-gradient(rgba(219, 234, 254, 0.7), rgba(219, 234, 254, 0.7)), url('{{ asset('batam-hero.jpg') }}') no-repeat center center; background-size: cover; border-bottom: 1px solid #e5e7eb; padding: 70px 0;">
        <div class="container" style="max-width: 850px; text-align: center;">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 12px; letter-spacing: -0.02em;">Panduan & Bantuan</h1>
            <p style="font-size: 1.1rem; color: #374151; max-width: 700px; margin: 0 auto; line-height: 1.6; font-weight: 500;">
                Selamat datang di pusat informasi {{ config('app.name') }}. Pelajari lebih dalam tentang layanan kami dan temukan jawaban atas pertanyaan Anda di sini.
            </p>
        </div>
    </section>

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

            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b;">Tanya Jawab (FAQ)</h2>
                <p style="color: #64748b;">Informasi teknis dan panduan penggunaan layanan kami.</p>
            </div>
            
            <div class="faq-container" style="display: flex; flex-direction: column; gap: 15px;">
                
                <!-- Question 1 -->
                <div class="faq-item" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: all 0.3s ease;">
                    <div class="faq-header" onclick="toggleFaq(this)" style="padding: 20px 25px; background: #f8fafc; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-rocket" style="color: var(--primary); width: 20px;"></i>
                            Apa saja yang dapat diposting di LAPAK SEBATAM?
                        </h3>
                        <i class="fas fa-chevron-down toggle-icon" style="color: #94a3b8; transition: transform 0.3s;"></i>
                    </div>
                    <div class="faq-content" style="display: none; padding: 25px; border-top: 1px solid #f1f5f9; background: white;">
                        <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;">
                            Berikut adalah tipe iklan yang dapat diposting di LAPAK SEBATAM:
                        </p>
                        <ul style="margin: 0; padding: 0; list-style: none;">
                            @foreach($listingTypes as $type)
                            <li style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; color: #4b5563; font-size: 0.9rem;">
                                <i class="fas fa-check-circle" style="color: #10b981; margin-top: 4px;"></i>
                                <span>
                                    <strong style="color: #1e293b; font-weight: 700;">{{ strtoupper($type->name) }}:</strong> 
                                    {{ $type->keterangan ?? 'Informasi mengenai ' . strtolower($type->name) . '.' }}
                                </span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Question 2 -->
                <div class="faq-item" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: all 0.3s ease;">
                    <div class="faq-header" onclick="toggleFaq(this)" style="padding: 20px 25px; background: #f8fafc; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-bolt" style="color: var(--primary); width: 20px;"></i>
                            Kenapa harus di LAPAK SEBATAM?
                        </h3>
                        <i class="fas fa-chevron-down toggle-icon" style="color: #94a3b8; transition: transform 0.3s;"></i>
                    </div>
                    <div class="faq-content" style="display: none; padding: 25px; border-top: 1px solid #f1f5f9; background: white;">
                        <ul style="margin: 0; padding: 0; list-style: none; font-size: 0.95rem; color: #4b5563;">
                            <li style="margin-bottom: 12px; display: flex; gap: 12px;"><i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i> <span><strong style="color: #1e293b;">SUMBER UTAMA:</strong> Platform rujukan utama bagi seluruh warga Batam.</span></li>
                            <li style="margin-bottom: 12px; display: flex; gap: 12px;"><i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i> <span><strong style="color: #1e293b;">FOKUS LOKAL:</strong> 100% didedikasikan untuk komunitas di Batam.</span></li>
                            <li style="margin-bottom: 12px; display: flex; gap: 12px;"><i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i> <span><strong style="color: #1e293b;">TANPA RIBET:</strong> Kami mengintegrasikan website dengan WhatsApp. Posting iklan seperti chat saja.</span></li>
                            <li style="display: flex; gap: 12px;"><i class="fas fa-check-circle" style="color: #10b981; margin-top: 3px;"></i> <span><strong style="color: #1e293b;">MODERN:</strong> Tampilan bersih dan sangat mudah digunakan melalui HP.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- Question 3 -->
                <div class="faq-item" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: all 0.3s ease;">
                    <div class="faq-header" onclick="toggleFaq(this)" style="padding: 20px 25px; background: #f8fafc; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-hand-holding-dollar" style="color: #10b981; width: 20px;"></i>
                            Apakah ini gratis?
                        </h3>
                        <i class="fas fa-chevron-down toggle-icon" style="color: #94a3b8; transition: transform 0.3s;"></i>
                    </div>
                    <div class="faq-content" style="display: none; padding: 25px; border-top: 1px solid #f1f5f9; background: white;">
                        <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                            <strong style="color: #10b981;">Ya, 100% Gratis!</strong> Memasang iklan di LAPAK SEBATAM tidak dipungut biaya. Jika nantinya ada permintaan tambahan fasilitas/fitur mungkin akan ada biaya.
                        </p>
                    </div>
                </div>

                <!-- Question 4 -->
                <div class="faq-item" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: all 0.3s ease;">
                    <div class="faq-header" onclick="toggleFaq(this)" style="padding: 20px 25px; background: #f8fafc; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-paper-plane" style="color: var(--primary); width: 20px;"></i>
                            Cara Pasang Iklan
                        </h3>
                        <i class="fas fa-chevron-down toggle-icon" style="color: #94a3b8; transition: transform 0.3s;"></i>
                    </div>
                    <div class="faq-content" style="display: none; padding: 25px; border-top: 1px solid #f1f5f9; background: white;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; margin-bottom: 8px; text-transform: uppercase;">Cara-1</div>
                                <p style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 8px;">Melalui Website</p>
                                <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5; margin: 0;">Klik tombol <strong>Pasang Iklan</strong> di navigasi website ini.</p>
                            </div>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; margin-bottom: 8px; text-transform: uppercase;">Cara-2</div>
                                <p style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 8px;">Melalui Chat Bot</p>
                                <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5; margin: 0;">Kirim pesan teks <strong>"lapak sebatam"</strong> ke nomor WA admin, kemudian ikuti instruksi bot.</p>
                            </div>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <div style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; margin-bottom: 8px; text-transform: uppercase;">Cara-3</div>
                                <p style="font-size: 0.9rem; font-weight: 700; color: #334155; margin-bottom: 8px;">Melalui Akun</p>
                                <p style="font-size: 0.85rem; color: #64748b; line-height: 1.5; margin: 0;">Anda mendaftar/login ke akun Anda, kemudian Anda bisa pasang iklan secara langsung dari dashboard.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom CTA -->
            <div style="margin-top: 50px; text-align: center; padding: 40px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 20px; border: 1px solid #bae6fd;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: #0369a1; margin-bottom: 10px;">Masih ada pertanyaan?</h3>
                <p style="color: #0c4a6e; margin-bottom: 25px;">Tim kami siap membantu Anda mendapatkan pengalaman terbaik.</p>
                <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                    <a href="{{ route('listings.create') }}" class="btn btn-primary" style="padding: 12px 30px; font-weight: 800; border-radius: 10px; box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2);">Pasang Iklan Sekarang</a>
                    <a href="{{ route('contact') }}" class="btn btn-outline" style="padding: 12px 30px; font-weight: 800; border-radius: 10px; background: white;">Hubungi Admin</a>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    function toggleFaq(header) {
        const item = header.parentElement;
        const content = item.querySelector('.faq-content');
        const icon = header.querySelector('.toggle-icon');
        const allItems = document.querySelectorAll('.faq-item');

        // Close others
        allItems.forEach(i => {
            if (i !== item) {
                i.querySelector('.faq-content').style.display = 'none';
                i.querySelector('.toggle-icon').style.transform = 'rotate(0deg)';
                i.style.borderColor = '#e2e8f0';
                i.querySelector('.faq-header').style.background = '#f8fafc';
            }
        });

        // Toggle current
        if (content.style.display === 'block') {
            content.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
            item.style.borderColor = '#e2e8f0';
            header.style.background = '#f8fafc';
        } else {
            content.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
            item.style.borderColor = 'var(--primary)';
            header.style.background = '#ffffff';
        }
    }

    // Open first item by default
    document.addEventListener('DOMContentLoaded', () => {
        const firstHeader = document.querySelector('.faq-header');
        if (firstHeader) toggleFaq(firstHeader);
    });
</script>

<style>
    .faq-item:hover {
        border-color: var(--primary) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
</style>
@endsection
