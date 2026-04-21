@extends('layouts.app')

@section('title', 'Baca Saya - Sebatam.com')

@section('content')
<div class="read-me-page">
    <!-- Hero Section -->
    <section class="premium-hero" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); padding: 80px 20px; text-align: center; color: white; position: relative; overflow: hidden;">
        <div class="container" style="position: relative; z-index: 2;">
            <h1 style="font-size: 3.5rem; font-weight: 900; margin-bottom: 20px; letter-spacing: -2px; line-height: 1.1;">Panduan Penggunaan Sebatam.com</h1>
            <p style="font-size: 1.4rem; opacity: 0.9; max-width: 800px; margin: 0 auto; line-height: 1.6;">
                Semua yang perlu Anda ketahui untuk mulai mencari dan menawarkan informasi di Batam.
            </p>
        </div>
        <!-- Decorative background elements -->
        <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: rgba(255,255,255,0.1); border-radius: 50%; filter: blur(50px);"></div>
        <div style="position: absolute; bottom: -150px; left: -150px; width: 500px; height: 500px; background: rgba(0,0,0,0.05); border-radius: 50%; filter: blur(50px);"></div>
    </section>

    <!-- Content Sections -->
    <section class="legal-section" style="background: #f8fafc;">
        <div class="container" style="max-width: 1000px;">
            <div style="display: flex; flex-direction: column; gap: 40px;">
                
                <!-- Question 1 -->
                <div class="faq-card legal-card glass" style="width: 100%;">
                    <div class="faq-card-content" style="display: flex; align-items: flex-start; gap: 25px;">
                        <div style="min-width: 60px; height: 60px; background: #eef2ff; color: #4f46e5; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Apa yang bisa Anda lakukan dengan website ini?</h2>
                            <p style="color: #475569; font-size: 1.15rem; line-height: 1.8;">
                                Sebatam.com adalah platform penawaran dan papan pengumuman modern khusus untuk warga Batam. Tipe penawaran atau iklan yang tersedia:
                            </p>
                            <ul style="margin: 20px 0 0 0; padding: 0; list-style: none;">
                                @foreach($listingTypes as $type)
                                <li style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; color: #64748b;">
                                    <i class="fas fa-check-circle" style="color: #10b981; margin-top: 5px;"></i>
                                    <span>
                                        <strong style="color: #1e293b; font-weight: 850;">{{ strtoupper($type->name) }}:</strong> 
                                        @if($type->keterangan)
                                            {!! nl2br(e($type->keterangan)) !!}
                                        @else
                                            Informasi mengenai {{ strtolower($type->name) }}.
                                        @endif
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Question 2: Why Us -->
                <div class="faq-card legal-card glass" style="width: 100%;">
                    <div class="faq-card-content" style="display: flex; align-items: flex-start; gap: 25px;">
                        <div style="min-width: 60px; height: 60px; background: #f5f3ff; color: #8b5cf6; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Kenapa harus di Sebatam.com?</h2>
                            <p style="color: #475569; font-size: 1.15rem; line-height: 1.8;">
                                Kami memberikan pengalaman berbeda dalam beriklan dan mencari informasi:
                            </p>
                            <ul style="margin: 20px 0 0 0; padding: 0; list-style: none;">
                                <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; color: #64748b;">
                                    <i class="fas fa-check-circle" style="color: #8b5cf6;"></i>
                                    <span><strong style="color: #1e293b; font-weight: 850;">SUMBER UTAMA:</strong> Platform ini akan menjadi sumber utama informasi bagi warga Batam. Anda tak akan rugi beriklan di sini.</span>
                                </li>
                                <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; color: #64748b;">
                                    <i class="fas fa-check-circle" style="color: #8b5cf6;"></i>
                                    <span><strong style="color: #1e293b; font-weight: 850;">FOKUS LOKAL:</strong> 100% didedikasikan untuk komunitas di Batam.</span>
                                </li>
                                <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; color: #64748b;">
                                    <i class="fas fa-check-circle" style="color: #8b5cf6;"></i>
                                    <span><strong style="color: #1e293b; font-weight: 850;">TANPA RIBET:</strong> Gak perlu email/password ribet, cukup masuk via WhatsApp.</span>
                                </li>
                                <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; color: #64748b;">
                                    <i class="fas fa-check-circle" style="color: #8b5cf6;"></i>
                                    <span><strong style="color: #1e293b; font-weight: 850;">SEARCH ENGINE FRIENDLY:</strong> Iklan Anda dirancang agar mudah ditemukan di Google.</span>
                                </li>
                                <li style="display: flex; align-items: center; gap: 12px; color: #64748b;">
                                    <i class="fas fa-check-circle" style="color: #8b5cf6;"></i>
                                    <span><strong style="color: #1e293b; font-weight: 850;">ALGORITMA MODERN:</strong> Tampilan bersih dan sangat mudah digunakan bahkan melalui HP.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                    <!-- Question 4 -->
                <div class="faq-card legal-card glass" style="width: 100%;">
                    <div class="faq-card-content" style="display: flex; align-items: flex-start; gap: 25px;">
                        <div style="min-width: 60px; height: 60px; background: #ecfdf5; color: #10b981; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Apakah ini gratis?</h2>
                            <p style="color: #475569; font-size: 1.15rem; line-height: 1.8;">
                                <strong>Ya, 100% Gratis!</strong> Memasang iklan reguler di Sebatam.com tidak dipungut biaya apapun. Kami ingin membantu ekonomi lokal Batam tumbuh melalui kemudahan berbagi informasi.
                            </p>
                        </div>
                    </div>
                </div>


                <!-- Question 3 -->
                <div class="faq-card legal-card glass" style="width: 100%;">
                    <div class="faq-card-content" style="display: flex; align-items: flex-start; gap: 25px;">
                        <div style="min-width: 60px; height: 60px; background: #fff7ed; color: #f97316; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div style="flex: 1;">
                            <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 15px;">Bagaimana cara memasang iklan?</h2>
                            <p style="color: #475569; font-size: 1.15rem; line-height: 1.8;">
                                Kami merancang proses posting yang sangat mudah dan tanpa ribet:
                            </p>
                            <div class="posting-steps-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 25px;">
                                <div style="background: #f1f5f9; padding: 20px; border-radius: 20px; text-align: center;">
                                    <span style="display: block; font-size: 1.5rem; font-weight: 900; color: #cbd5e1; margin-bottom: 10px;">01</span>
                                    <p style="font-weight: 600; color: #1e293b; margin: 0;">Daftar/Masuk via WhatsApp</p>
                                </div>
                                <div style="background: #f1f5f9; padding: 20px; border-radius: 20px; text-align: center;">
                                    <span style="display: block; font-size: 1.5rem; font-weight: 900; color: #cbd5e1; margin-bottom: 10px;">02</span>
                                    <p style="font-weight: 600; color: #1e293b; margin: 0;">Klik Pasang Iklan</p>
                                </div>
                                <div style="background: #f1f5f9; padding: 20px; border-radius: 20px; text-align: center;">
                                    <span style="display: block; font-size: 1.5rem; font-weight: 900; color: #cbd5e1; margin-bottom: 10px;">03</span>
                                    <p style="font-weight: 600; color: #1e293b; margin: 0;">Isi Detail & Publish</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            
                <!-- Question 5 -->
                <!-- <div class="faq-card legal-card glass" style="background: #1e293b; color: white;">
                    <div class="faq-card-content" style="display: flex; align-items: flex-start; gap: 25px;">
                        <div style="min-width: 60px; height: 60px; background: rgba(255,255,255,0.1); color: #fbbf24; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 1.8rem; font-weight: 800; color: white; margin-bottom: 15px;">Apa saja manfaat Listing Premium?</h2>
                            <p style="color: #94a3b8; font-size: 1.15rem; line-height: 1.8;">
                                Jika Anda ingin hasil yang lebih cepat dan jangkauan lebih luas, Listing Premium adalah solusinya:
                            </p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
                                <div style="display: flex; gap: 15px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 16px;">
                                    <i class="fas fa-arrow-up-wide-short" style="color: #fbbf24; font-size: 1.5rem;"></i>
                                    <span><strong>Posisi Teratas:</strong> Iklan Anda selalu berada di barisan paling atas kategori.</span>
                                </div>
                                <div style="display: flex; gap: 15px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 16px;">
                                    <i class="fas fa-certificate" style="color: #fbbf24; font-size: 1.5rem;"></i>
                                    <span><strong>Badge Eksklusif:</strong> Mendapatkan tanda "Premium" yang meningkatkan kepercayaan.</span>
                                </div>
                                <div style="display: flex; gap: 15px; background: rgba(255,255,255,0.05); padding: 20px; border-radius: 16px;">
                                    <i class="fas fa-eye" style="color: #fbbf24; font-size: 1.5rem;"></i>
                                    <span><strong>Jangkauan 10x:</strong> Muncul lebih sering di halaman depan dan hasil pencarian.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

            </div>
        </div>
    </section>

    <!-- Bottom CTA -->
    <section style="padding: 100px 20px; background: white; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; font-weight: 850; color: #1e293b; margin-bottom: 30px;">Punya Pertanyaan Lain?</h2>
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <a href="{{ route('contact') }}" class="btn btn-outline" style="padding: 16px 40px; font-size: 1.1rem; border-radius: 16px;">Hubungi Kami</a>
                <a href="{{ route('listings.create') }}" class="btn btn-primary" style="padding: 16px 40px; font-size: 1.1rem; border-radius: 16px; background: #4f46e5;">Mulai Pasang Iklan</a>
            </div>
        </div>
    </section>
</div>

<style>
    .faq-card:hover {
        transform: translateY(-8px);
    }
    @media (max-width: 768px) {
        .premium-hero h1 { font-size: 2.5rem !important; }
        .premium-hero p { font-size: 1.1rem !important; }
        .faq-card { padding: 25px 20px !important; border-radius: 20px !important; }
        .faq-card-content { flex-direction: column !important; gap: 15px !important; }
        .faq-card h2 { font-size: 1.5rem !important; }
        .posting-steps-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endsection
