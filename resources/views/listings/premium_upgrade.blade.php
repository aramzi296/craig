@extends('layouts.dashboard')

@section('dashboard_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">{{ $listing ? 'Upgrade ke Premium' : 'Beli Paket Premium' }}</h1>
    <p style="color: var(--text-muted);">
        {{ $listing ? 'Tingkatkan jangkauan iklan "' . $listing->title . '" Anda menjadi Premium.' : 'Beli paket premium sekarang dan gunakan kapan saja untuk iklan Anda.' }}
    </p>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div id="step1">
        <div class="glass" style="padding: 30px; border-radius: var(--radius); margin-bottom: 30px;">
            <h2 style="font-size: 1.2rem; margin-bottom: 25px;">1. Pilih Paket Premium</h2>
            
            <form id="premiumForm" action="{{ route('dashboard.premium.process') }}" method="POST">
                @csrf
                <input type="hidden" name="listing_id" value="{{ $listing->id ?? '' }}">
                <input type="hidden" name="unique_code" value="{{ $uniqueCode }}">
                
                <div style="display: grid; gap: 15px;">
                    @forelse($packages as $package)
                    <label class="package-option" style="display: flex; align-items: center; gap: 20px; padding: 20px; border: 2px solid var(--border); border-radius: 12px; cursor: pointer; transition: all 0.3s ease;">
                        <input type="radio" name="package_id" value="{{ $package->id }}" required style="width: 20px; height: 20px; accent-color: var(--primary);" 
                            onchange="updateTotal('{{ $package->name }}', {{ $package->price }})">
                        <div style="flex-grow: 1;">
                            <div style="font-weight: 700; font-size: 1.1rem;">{{ $package->name }}</div>
                            <div style="color: var(--text-muted); font-size: 0.9rem;">Durasi: {{ $package->duration_days }} Hari</div>
                        </div>
                        <div style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">
                            Rp {{ number_format($package->price, 0, ',', '.') }}
                        </div>
                    </label>
                    @empty
                    <div style="text-align: center; padding: 20px; border: 2px dashed var(--border); border-radius: 12px; color: var(--text-muted);">
                        Belum ada paket premium yang tersedia. Silakan hubungi admin.
                    </div>
                    @endforelse
                </div>

                <div style="margin-top: 30px; padding: 20px; background: #f8fafc; border-radius: 12px;">
                    <h3 style="font-size: 1rem; margin-bottom: 15px;">Ringkasan Pembayaran</h3>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Paket Terpilih:</span>
                        <span id="selectedPackageName" style="font-weight: 600;">-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Kode Unik (Verifikasi):</span>
                        <span style="color: #ef4444; font-weight: 700;">+{{ $uniqueCode }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.4rem; color: var(--primary); border-top: 2px solid var(--border); margin-top: 15px; padding-top: 15px;">
                        <span>Total Bayar:</span>
                        <span id="totalDisplaySummary">Rp 0</span>
                    </div>
                </div>

                <button type="button" onclick="goToStep2()" id="btnBayar" class="btn btn-primary" style="width: 100%; margin-top: 30px; padding: 18px; font-size: 1.1rem; font-weight: 700;" disabled>
                    Lanjut ke Pembayaran
                </button>
            </form>
        </div>
    </div>

    <div id="step2" style="display: none;">
        <div class="glass" style="padding: 40px; border-radius: var(--radius); text-align: center;">
            <h2 style="font-size: 1.5rem; margin-bottom: 10px;">2. Pembayaran QRIS</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">
                Silakan scan QRIS di bawah ini untuk menyelesaikan pembayaran 
                @if($listing)
                    iklan <b>"{{ $listing->title }}"</b>
                @else
                    <b>Paket Premium</b>
                @endif
            </p>
            
            <div style="margin: 0 auto; max-width: 350px; background: white; padding: 20px; border-radius: 20px; box-shadow: var(--shadow); margin-bottom: 30px; border: 1px solid var(--border);">
                <img src="{{ asset('qris.jpeg') }}" style="width: 100%; height: auto; border-radius: 10px;" alt="QRIS Sebatam">
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--border);">
                    <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 5px;">Total Tagihan:</div>
                    <div id="totalDisplayQRIS" style="font-size: 2rem; font-weight: 900; color: var(--primary); letter-spacing: -1px;">Rp 0</div>
                    <div style="font-size: 0.75rem; color: #ef4444; font-weight: 700; margin-top: 5px;">*WAJIB TRANSFER SAMPAI 3 DIGIT TERAKHIR</div>
                </div>
            </div>

            <div style="display: grid; gap: 15px; max-width: 400px; margin: 0 auto;">
                <button type="button" onclick="document.getElementById('premiumForm').submit()" class="btn btn-primary" style="padding: 18px; font-size: 1.1rem; font-weight: 700;">
                    Saya Sudah Bayar
                </button>
                <button type="button" onclick="goToStep1()" class="btn btn-outline" style="padding: 12px; font-size: 0.9rem;">
                    Kembali Ubah Paket
                </button>
            </div>
        </div>
    </div>
</div>


<style>
    .package-option:has(input:checked) {
        border-color: var(--primary);
        background: #f0f9ff;
    }
</style>

<script>
    const uniqueCode = {{ $uniqueCode }};

    function updateTotal(name, price) {
        document.getElementById('selectedPackageName').innerText = name;
        
        const total = price + uniqueCode;
        const formattedTotal = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(total);
        
        document.getElementById('totalDisplaySummary').innerText = formattedTotal;
        document.getElementById('totalDisplayQRIS').innerText = formattedTotal;
        
        // Enable the Pay button
        document.getElementById('btnBayar').disabled = false;
    }

    function goToStep2() {
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        window.scrollTo(0, 0);
    }

    function goToStep1() {
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step2').style.display = 'none';
        window.scrollTo(0, 0);
    }
</script>

@endsection
