@extends('admin.layout')

@section('admin_content')
<div style="max-width: 1000px; margin: 0 auto; padding-bottom: 50px;">
    
    <!-- Premium Header -->
    <div style="margin-bottom: 30px; display: flex; flex-direction: column; gap: 8px;">
        <h1 style="font-size: 2rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 12px; letter-spacing: -0.025em;">
            <i class="fa-solid fa-broom" style="color: #6366f1;"></i> Bersihkan Tagar Duplikat
        </h1>
        <p style="color: #64748b; font-size: 0.95rem; margin: 0; line-height: 1.5; max-width: 600px;">
            Alat ini memindai dan menggabungkan tagar-tagar serupa yang berasal dari kata dasar yang sama (contoh: "rentalmobil" dengan "rental mobil" atau "Jasa Las" dengan "jasalas").
        </p>
    </div>

    <!-- Stat Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <!-- Card 1: Total Tags -->
        <div style="background: #ffffff; border-radius: 16px; border: 1.5px solid #e2e8f0; padding: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="background: #f1f5f9; color: #475569; width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div>
                <span style="display: block; color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Total Tagar</span>
                <span style="display: block; color: #0f172a; font-size: 1.75rem; font-weight: 800; line-height: 1;">{{ count($allTags) }}</span>
            </div>
        </div>

        <!-- Card 2: Duplicates Count -->
        @if($totalDuplicatesCount > 0)
        <div style="background: #fff5f5; border-radius: 16px; border: 1.5px solid #feb2b2; padding: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="background: #fed7d7; color: #c53030; width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <span style="display: block; color: #9b2c2c; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Duplikat Terdeteksi</span>
                <span style="display: block; color: #9b2c2c; font-size: 1.75rem; font-weight: 800; line-height: 1;">{{ $totalDuplicatesCount }}</span>
            </div>
        </div>
        @else
        <div style="background: #f0fdf4; border-radius: 16px; border: 1.5px solid #bbf7d0; padding: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="background: #dcfce7; color: #16a34a; width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span style="display: block; color: #166534; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Status Duplikasi</span>
                <span style="display: block; color: #166534; font-size: 1.25rem; font-weight: 800; line-height: 1.2;">0 Duplikat</span>
            </div>
        </div>
        @endif

        <!-- Card 3: Forbidden Tags Count -->
        @if(count($forbiddenTags) > 0)
        <div style="background: #fff5f5; border-radius: 16px; border: 1.5px solid #feb2b2; padding: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="background: #fed7d7; color: #c53030; width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <span style="display: block; color: #9b2c2c; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Tagar Terlarang</span>
                <span style="display: block; color: #9b2c2c; font-size: 1.75rem; font-weight: 800; line-height: 1;">{{ count($forbiddenTags) }}</span>
            </div>
        </div>
        @else
        <div style="background: #f0fdf4; border-radius: 16px; border: 1.5px solid #bbf7d0; padding: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="background: #dcfce7; color: #16a34a; width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <span style="display: block; color: #166534; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Kriteria Kecamatan</span>
                <span style="display: block; color: #166534; font-size: 1.25rem; font-weight: 800; line-height: 1.2;">100% Bersih</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Action Section -->
    @if($totalDuplicatesCount > 0 || count($forbiddenTags) > 0)
    <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1.5px solid #e2e8f0; border-radius: 20px; padding: 30px; margin-bottom: 40px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.02); display: flex; flex-direction: column; md-flex-direction: row; justify-content: space-between; align-items: flex-start; md-align-items: center; gap: 20px;">
        <div style="max-width: 600px;">
            <h3 style="margin: 0 0 8px 0; font-size: 1.15rem; font-weight: 800; color: #0f172a;">Siap untuk Penggabungan & Pembersihan Tagar Otomatis</h3>
            <p style="margin: 0; color: #64748b; font-size: 0.88rem; line-height: 1.5;">
                Menekan tombol di samping akan menggabungkan seluruh tagar duplikat ke dalam satu tagar utama yang sah, memutus dan menghapus tagar terlarang yang bermuatan nama kecamatan, memperbarui indeks pencarian listing yang terdampak, lalu membersihkan database secara aman.
            </p>
        </div>
        <div>
            <form action="{{ route('admin.tags.deduplicate.run') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin bersihkan tagar? Tindakan ini akan menggabungkan duplikat serta menghapus tagar terlarang yang mengandung nama kecamatan.');">
                @csrf
                <button type="submit" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: #ffffff; border: none; border-radius: 12px; padding: 14px 28px; font-size: 0.95rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 12px 20px -3px rgba(99, 102, 241, 0.4)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 10px 15px -3px rgba(99, 102, 241, 0.3)';">
                    <i class="fa-solid fa-broom"></i> Bersihkan Tagar Sekarang
                </button>
            </form>
        </div>
    </div>
    @else
    <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 16px; padding: 24px; margin-bottom: 40px; display: flex; align-items: flex-start; gap: 16px;">
        <i class="fa-solid fa-circle-check" style="color: #16a34a; font-size: 1.35rem; margin-top: 2px;"></i>
        <div>
            <h3 style="margin: 0 0 4px 0; font-size: 1rem; font-weight: 800; color: #14532d;">Kerja Bagus! Database Tagar Bersih</h3>
            <p style="margin: 0; color: #166534; font-size: 0.88rem; line-height: 1.5;">
                Tidak ada tagar duplikat maupun tagar terlarang (nama kecamatan) yang terdeteksi di database Anda saat ini.
            </p>
        </div>
    </div>
    @endif

    <!-- Forbidden Tags Preview Table -->
    @if(count($forbiddenTags) > 0)
    <div style="background: #ffffff; border: 1.5px solid #feb2b2; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); margin-bottom: 40px;">
        <div style="padding: 20px 24px; border-bottom: 1.5px solid #feb2b2; background: #fff5f5; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-exclamation" style="color: #c53030; font-size: 1.15rem;"></i>
            <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: #9b2c2c;">Tagar Terlarang Terdeteksi (Akan Dihapus)</h3>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #fff5f5; border-bottom: 1.5px solid #feb2b2;">
                        <th style="padding: 16px 24px; font-weight: 700; color: #9b2c2c; width: 40%;">Nama Tagar</th>
                        <th style="padding: 16px 24px; font-weight: 700; color: #9b2c2c; width: 40%;">Kata Kecamatan Terdeteksi</th>
                        <th style="padding: 16px 24px; font-weight: 700; color: #9b2c2c; width: 20%; text-align: center;">Total Listing Terdampak</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forbiddenTags as $tag)
                        <tr style="border-bottom: 1px solid #fee2e2; transition: background 0.15s;" onmouseover="this.style.background='#fff5f5'" onmouseout="this.style.background='none'">
                            <td style="padding: 20px 24px; vertical-align: middle;">
                                <span style="font-weight: 700; color: #c53030; font-size: 0.95rem;">#{{ $tag->name }}</span>
                            </td>
                            <td style="padding: 20px 24px; vertical-align: middle;">
                                <?php
                                    $detectedWord = '';
                                    $forbiddenWords = [
                                        "Batam Center", "Batam Kota", "Lubuk Baja", "Batu Ampar", 
                                        "Bengkong", "Nongsa", "Sungai Beduk", "Batu Aji", 
                                        "Sagulung", "Sekupang", "Bulang", "Galang", "Belakang Padang"
                                    ];
                                    foreach ($forbiddenWords as $word) {
                                        if (str_contains(strtolower($tag->name), strtolower($word))) {
                                            $detectedWord = $word;
                                            break;
                                        }
                                    }
                                ?>
                                <span style="background: #fed7d7; color: #9b2c2c; font-size: 0.75rem; font-weight: 800; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase;">
                                    {{ $detectedWord ?: 'Kecamatan' }}
                                </span>
                            </td>
                            <td style="padding: 20px 24px; text-align: center; vertical-align: middle; font-weight: 800; color: #9b2c2c; font-size: 1.1rem;">
                                <span style="background: #fee2e2; padding: 6px 14px; border-radius: 10px; border: 1px solid #fca5a5;">
                                    {{ $tag->listings()->count() }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Preview Table -->
    @if(count($duplicateGroups) > 0)
    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
        <div style="padding: 20px 24px; border-bottom: 1.5px solid #e2e8f0; background: #f8fafc;">
            <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: #0f172a;">Pratinjau Penggabungan Tagar Duplikat</h3>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                        <th style="padding: 16px 24px; font-weight: 700; color: #475569; width: 40%;">Tagar Utama (Yang Dipertahankan)</th>
                        <th style="padding: 16px 24px; font-weight: 700; color: #475569; width: 40%;">Tagar Duplikat (Yang Akan Digabungkan & Dihapus)</th>
                        <th style="padding: 16px 24px; font-weight: 700; color: #475569; width: 20%; text-align: center;">Total Listing Terdampak</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($duplicateGroups as $group)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='none'">
                            <!-- Primary Tag -->
                            <td style="padding: 20px 24px; vertical-align: top;">
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">#{{ $group['primary']->name }}</span>
                                        <span style="background: #dcfce7; color: #166534; font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Utama</span>
                                        @if($group['primary']->is_approved)
                                            <span style="background: #e0f2fe; color: #0369a1; font-size: 0.72rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-circle-check"></i> Approved</span>
                                        @endif
                                    </div>
                                    <span style="color: #64748b; font-size: 0.8rem; display: flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-link"></i> {{ $group['primary']->listings()->count() }} listing terasosiasi
                                    </span>
                                </div>
                            </td>
                            <!-- Duplicate Tags -->
                            <td style="padding: 20px 24px; vertical-align: top;">
                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    @foreach($group['duplicates'] as $duplicate)
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span style="text-decoration: line-through; color: #ef4444; font-weight: 700; font-size: 0.9rem;">#{{ $duplicate->name }}</span>
                                                <span style="background: #fee2e2; color: #991b1b; font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Duplikat</span>
                                                @if($duplicate->is_approved)
                                                    <span style="background: #e0f2fe; color: #0369a1; font-size: 0.72rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;"><i class="fa-solid fa-circle-check"></i> Approved</span>
                                                @endif
                                            </div>
                                            <span style="color: #64748b; font-size: 0.8rem; display: flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-link"></i> {{ $duplicate->listings()->count() }} listing terasosiasi
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <!-- Impacted Listings -->
                            <td style="padding: 20px 24px; text-align: center; vertical-align: middle; font-weight: 800; color: #334155; font-size: 1.1rem;">
                                <?php
                                    $primaryListings = $group['primary']->listings()->pluck('listings.id');
                                    $duplicateListings = collect();
                                    foreach ($group['duplicates'] as $dup) {
                                        $duplicateListings = $duplicateListings->merge($dup->listings()->pluck('listings.id'));
                                    }
                                    $totalUniqueListings = $primaryListings->merge($duplicateListings)->unique()->count();
                                ?>
                                <span style="background: #f1f5f9; padding: 6px 14px; border-radius: 10px; border: 1px solid #e2e8f0;">
                                    {{ $totalUniqueListings }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
