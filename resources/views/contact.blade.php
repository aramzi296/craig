@extends('layouts.app')

@section('title', 'Hubungi Kami - Sebatam.com')

@section('content')
<div class="legal-page">
    <!-- Compact Hero -->
    <section style="background: linear-gradient(135deg, var(--primary) 0%, #0369a1 100%); padding: 60px 20px; text-align: center; color: white; position: relative; overflow: hidden;">
        <div class="container" style="position: relative; z-index: 2;">
            <h1 style="font-size: 2.8rem; font-weight: 850; margin-bottom: 12px; letter-spacing: -1.5px;">Hubungi Kami</h1>
            <p style="font-size: 1.2rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">Butuh bantuan atau ingin bertanya? Tim Sebatam siap membantu Anda.</p>
        </div>
        <div style="position: absolute; top: -30px; left: -30px; width: 180px; height: 180px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    </section>

    <section class="legal-section">
        <div class="container">
            <div class="contact-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 40px; align-items: start;">
                
                <!-- Contact info cards -->
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="glass contact-card" style="padding: 30px; border-radius: 20px; background: white; border: 1px solid var(--border); box-shadow: var(--shadow);">
                        <div style="display: flex; gap: 20px;">
                            <div style="width: 50px; height: 50px; background: #eff6ff; color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                                <i class="fas fa-location-dot"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 5px;">Kantor Kami</h3>
                                <p style="color: var(--text-muted); line-height: 1.5;">Mall Top 100 Tembesi, Blok H3 No. 1, Tembesi, Batam</p>
                            </div>
                        </div>
                    </div>

                    <div class="glass contact-card" style="padding: 30px; border-radius: 20px; background: white; border: 1px solid var(--border); box-shadow: var(--shadow);">
                        <div style="display: flex; gap: 20px;">
                            <div style="width: 50px; height: 50px; background: #ecfdf5; color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 5px;">WhatsApp Admin</h3>
                                <p style="color: var(--text-muted); margin-bottom: 10px;">Respon cepat via WhatsApp.</p>
                                <a href="https://wa.me/6282172292230" target="_blank" style="color: #10b981; font-weight: 700; text-decoration: none;">0821-7229-2230</a>
                            </div>
                        </div>
                    </div>

                    <div class="glass contact-card" style="padding: 30px; border-radius: 20px; background: white; border: 1px solid var(--border); box-shadow: var(--shadow);">
                        <div style="display: flex; gap: 20px;">
                            <div style="width: 50px; height: 50px; background: #fff7ed; color: var(--secondary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 5px;">Email Dukungan</h3>
                                <p style="color: var(--text-muted); margin-bottom: 10px;">Kirimkan pertanyaan Anda lewat email.</p>
                                <a href="mailto:support@sebatam.com" style="color: var(--secondary); font-weight: 700; text-decoration: none;">support@sebatam.com</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message instruction / Social -->
                <div class="glass contact-card" style="padding: 40px; border-radius: 24px; background: white; border: 1px solid var(--border); box-shadow: var(--shadow-lg);">
                    <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 20px;">Hubungi Kami Online</h2>
                    <p style="color: var(--text-muted); margin-bottom: 30px; line-height: 1.6;">
                        Kami siap membantu Anda terkait pemasangan iklan, kendala teknis, atau kerjasama bisnis di Batam. Anda juga bisa mengikuti kami di media sosial untuk update terbaru.
                    </p>
                    
                    <div style="display: flex; gap: 15px; margin-bottom: 40px;">
                        <a href="https://www.facebook.com/SemuaSebatam" class="btn" style="width: 50px; height: 50px; padding: 0; border-radius: 50%; background: #1877f2; color: white;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.instagram.com/semuasebatam/" class="btn" style="width: 50px; height: 50px; padding: 0; border-radius: 50%; background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); color: white;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.tiktok.com/@semuasebatam" class="btn" style="width: 50px; height: 50px; padding: 0; border-radius: 50%; background: black; color: white;">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>

                    <div style="padding: 25px; background: #f8fafc; border-radius: 16px; border: 1px dashed var(--border);">
                        <h4 style="font-weight: 700; margin-bottom: 10px;">Jam Operasional</h4>
                        <p style="color: var(--text-muted); font-size: 0.95rem;">Setiap Hari: 09.00 - 21.00 WIB</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection

<style>
    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr !important;
            gap: 20px !important;
        }
        .contact-card {
            padding: 20px !important;
        }
        .contact-card h2 {
            font-size: 1.5rem !important;
        }
    }
</style>
