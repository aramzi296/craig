@extends('layouts.app')

@section('title', 'Baca Saya - Sebatam.com')

@section('content')
<div class="read-me-page">
    <!-- Hero Section -->
    <section class="hero" style="background: linear-gradient(rgba(219, 234, 254, 0.7), rgba(219, 234, 254, 0.7)), url('{{ asset('batam-hero.jpg') }}') no-repeat center center; background-size: cover; border-bottom: 1px solid #e5e7eb; padding: 60px 0;">
        <div class="container" style="max-width: 800px; text-align: center;">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 12px; letter-spacing: -0.02em;">Panduan Penggunaan SEBATAM</h1>
            <p style="font-size: 1.1rem; color: #374151; max-width: 650px; margin: 0 auto; line-height: 1.5; font-weight: 500;">
                Segala hal yang perlu Anda ketahui untuk mencari dan menawarkan informasi di Batam dengan cara yang paling simpel dan efektif.
            </p>
        </div>
    </section>

    <!-- Content Sections -->
    <section class="legal-section" style="background: #ffffff; padding: 50px 0;">
        <div class="container" style="max-width: 850px;">
            <div style="display: flex; flex-direction: column; gap: 25px;">
                
                <!-- Section 1: What to do -->
                <div class="faq-card" style="background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="min-width: 40px; height: 40px; background: white; color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 1px solid #e2e8f0;">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 1.25rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Apa yang bisa Anda lakukan di sini?</h2>
                            <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin-bottom: 15px;">
                                SEBATAM adalah platform pengumuman online khusus warga Batam. Berikut adalah tipe informasi yang bisa Anda temukan atau pasang:
                            </p>
                            <ul style="margin: 0; padding: 0; list-style: none;">
                                @foreach($listingTypes as $type)
                                <li style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; color: #4b5563; font-size: 0.9rem;">
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
                </div>

                <!-- Section 2: Why Us -->
                <div class="faq-card" style="background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="min-width: 40px; height: 40px; background: white; color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 1px solid #e2e8f0;">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 1.25rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Kenapa harus di SEBATAM?</h2>
                            <ul style="margin: 0; padding: 0; list-style: none; font-size: 0.9rem; color: #4b5563;">
                                <li style="margin-bottom: 8px; display: flex; gap: 8px;"><i class="fas fa-check" style="color: var(--primary);"></i> <span><strong style="color: #1e293b;">SUMBER UTAMA:</strong> Platform rujukan utama bagi seluruh warga Batam.</span></li>
                                <li style="margin-bottom: 8px; display: flex; gap: 8px;"><i class="fas fa-check" style="color: var(--primary);"></i> <span><strong style="color: #1e293b;">FOKUS LOKAL:</strong> 100% didedikasikan untuk komunitas di Batam.</span></li>
                                <li style="margin-bottom: 8px; display: flex; gap: 8px;"><i class="fas fa-check" style="color: var(--primary);"></i> <span><strong style="color: #1e293b;">TANPA RIBET:</strong> Cukup masuk via WhatsApp, tanpa email/password rumit.</span></li>
                                <li style="display: flex; gap: 8px;"><i class="fas fa-check" style="color: var(--primary);"></i> <span><strong style="color: #1e293b;">MODERN:</strong> Tampilan bersih dan sangat mudah digunakan melalui HP.</span></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Is it free? -->
                <div class="faq-card" style="background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="min-width: 40px; height: 40px; background: white; color: #10b981; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 1px solid #e2e8f0;">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                        <div>
                            <h2 style="font-size: 1.25rem; font-weight: 800; color: #1e293b; margin-bottom: 8px;">Apakah ini gratis?</h2>
                            <p style="color: #475569; font-size: 0.95rem; line-height: 1.6;">
                                <strong style="color: #10b981;">Ya, 100% Gratis!</strong> Memasang iklan reguler di SEBATAM tidak dipungut biaya. Kami ingin membantu ekonomi lokal Batam tumbuh melalui kemudahan berbagi informasi.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section 4: How to post -->
                <div class="faq-card" style="background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="min-width: 40px; height: 40px; background: white; color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 1px solid #e2e8f0;">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div style="flex: 1;">
                            <h2 style="font-size: 1.25rem; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Cara Pasang Iklan</h2>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                                <div style="background: white; padding: 12px; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0;">
                                    <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; display: block;">01</span>
                                    <p style="font-size: 0.8rem; font-weight: 700; color: #334155;">Login WA</p>
                                </div>
                                <div style="background: white; padding: 12px; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0;">
                                    <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; display: block;">02</span>
                                    <p style="font-size: 0.8rem; font-weight: 700; color: #334155;">Isi Detail</p>
                                </div>
                                <div style="background: white; padding: 12px; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0;">
                                    <span style="font-size: 0.7rem; font-weight: 800; color: #94a3b8; display: block;">03</span>
                                    <p style="font-size: 0.8rem; font-weight: 700; color: #334155;">Tayangkan</p>
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
    <section style="padding: 60px 0; background: #f8fafc; text-align: center; border-top: 1px solid #f1f5f9;">
        <div class="container">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 20px;">Masih Bingung?</h2>
            <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                <a href="{{ route('listings.create') }}" class="btn btn-primary" style="padding: 10px 25px; font-weight: 700; font-size: 0.9rem;">Mulai Pasang Iklan</a>
                <a href="{{ route('contact') }}" class="btn btn-outline" style="padding: 10px 25px; font-weight: 700; font-size: 0.9rem;">Tanya CS Kami</a>
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
