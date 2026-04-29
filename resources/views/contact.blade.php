@extends('layouts.app')

@section('title', 'Hubungi Kami - ' . parse_url(config('app.url'), PHP_URL_HOST))

@section('content')
<div class="legal-page">
    <!-- Compact Hero -->
    <section class="hero" style="background: linear-gradient(rgba(219, 234, 254, 0.7), rgba(219, 234, 254, 0.7)), url('{{ asset('batam-hero.jpg') }}') no-repeat center center; background-size: cover; border-bottom: 1px solid #e5e7eb; padding: 60px 0;">
        <div class="container" style="max-width: 800px; text-align: center;">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #111827; margin-bottom: 12px; letter-spacing: -0.02em;">Hubungi Kami</h1>
            <p style="font-size: 1.1rem; color: #374151; max-width: 600px; margin: 0 auto; line-height: 1.5; font-weight: 500;">Butuh bantuan atau ingin bertanya? Tim {{ config('app.name') }} siap melayani kebutuhan informasi Anda.</p>
        </div>
    </section>

    <section class="legal-section" style="padding: 60px 0; background: #ffffff;">
        <div class="container" style="max-width: 900px;">
            <div class="contact-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; align-items: start;">
                
                <!-- Contact info cards -->
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="padding: 20px; border-radius: 10px; background: #f8fafc; border: 1px solid #f1f5f9; display: flex; gap: 15px;">
                        <div style="width: 40px; height: 40px; background: white; color: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; border: 1px solid #e2e8f0; flex-shrink: 0;">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 3px; color: #1e293b;">Lokasi Kami</h3>
                            <p style="color: #64748b; font-size: 0.85rem; line-height: 1.4;">Mall Top 100 Tembesi, Blok H3 No. 1, Tembesi, Batam</p>
                        </div>
                    </div>

                    <div style="padding: 20px; border-radius: 10px; background: #f8fafc; border: 1px solid #f1f5f9; display: flex; gap: 15px;">
                        <div style="width: 40px; height: 40px; background: white; color: #10b981; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; border: 1px solid #e2e8f0; flex-shrink: 0;">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 3px; color: #1e293b;">WhatsApp Admin</h3>
                            <a href="https://wa.me/6282172292230" target="_blank" style="color: #10b981; font-weight: 700; text-decoration: none; font-size: 0.95rem;">0821-7229-2230</a>
                        </div>
                    </div>

                    <div style="padding: 20px; border-radius: 10px; background: #f8fafc; border: 1px solid #f1f5f9; display: flex; gap: 15px;">
                        <div style="width: 40px; height: 40px; background: white; color: #3b82f6; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; border: 1px solid #e2e8f0; flex-shrink: 0;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 3px; color: #1e293b;">Email Dukungan</h3>
                            <a href="mailto:support@sebatam.com" style="color: #3b82f6; font-weight: 700; text-decoration: none; font-size: 0.95rem;">support@sebatam.com</a>
                        </div>
                    </div>

                    <!-- Restored Update Terbaru Section -->
                    <div style="padding: 25px; border-radius: 10px; background: #f8fafc; border: 1px solid #f1f5f9; margin-top: 10px;">
                        <h2 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 10px; color: #1e293b;">Update Terbaru</h2>
                        <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 15px; line-height: 1.5;">
                            Ikuti kami di media sosial untuk mendapatkan info promosi dan pengumuman terbaru di Kota Batam.
                        </p>
                        
                        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <a href="https://www.facebook.com/SemuaSebatam" class="btn" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: #1877f2; color: white; padding: 0;">
                                <i class="fab fa-facebook-f" style="font-size: 0.9rem;"></i>
                            </a>
                            <a href="https://www.instagram.com/semuasebatam/" class="btn" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: linear-gradient(45deg, #f09433, #dc2743, #bc1888); color: white; padding: 0;">
                                <i class="fab fa-instagram" style="font-size: 0.9rem;"></i>
                            </a>
                            <a href="https://www.tiktok.com/@semuasebatam" class="btn" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: #000; color: white; padding: 0;">
                                <i class="fab fa-tiktok" style="font-size: 0.9rem;"></i>
                            </a>
                        </div>

                        <div style="padding: 12px; background: white; border-radius: 8px; border: 1px dashed #e2e8f0;">
                            <h4 style="font-size: 0.75rem; font-weight: 800; color: #94a3b8; margin-bottom: 4px; text-transform: uppercase;">Jam Operasional</h4>
                            <p style="color: #475569; font-size: 0.85rem; font-weight: 600;">Setiap Hari: 09.00 - 21.00 WIB</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div style="padding: 30px; border-radius: 12px; background: white; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px; color: #1e293b;">Kirim Pesan</h2>
                    
                    @if(session('success'))
                        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                            <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 6px;">Nama Lengkap</label>
                            <input type="text" name="name" required placeholder="Contoh: Budi Santoso" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-family: inherit;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 6px;">Nomor WhatsApp</label>
                            <input type="text" name="whatsapp" required placeholder="Contoh: 08123456789" style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-family: inherit;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 6px;">Pesan Anda</label>
                            <textarea name="message" required rows="4" placeholder="Tuliskan pertanyaan atau pesan Anda di sini..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-family: inherit; resize: vertical;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-weight: 700; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <i class="fas fa-paper-plane"></i> Kirim Pesan Sekarang
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>
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
