<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Hapus semua hubungan kategori lama dari listing (kosongkan tabel pivot)
        DB::table('category_listing')->truncate();

        // 2. Hapus kategori lama dari tabel categories
        \App\Models\Category::query()->delete();

        // 3. Baca dan parsing database/kategori.json
        $jsonPath = database_path('kategori.json');
        if (!file_exists($jsonPath)) {
            $this->command->error("File database/kategori.json tidak ditemukan!");
            return;
        }

        $categoriesData = json_decode(file_get_contents($jsonPath), true);
        if (!$categoriesData) {
            $this->command->error("Format database/kategori.json tidak valid!");
            return;
        }

        // Mapping subcategory keyword patterns for intelligent listing auto-assignment
        $keywordMapping = [
            "Cafe & Tempat Nongkrong" => ['cafe', 'kopi', 'coffee', 'nongkrong', 'angkringan'],
            "Rumah Makan & Warung Kuliner" => ['warung', 'rumah makan', 'resto', 'restoran', 'sate', 'bakso', 'mie', 'nasi', 'ayam', 'seafood', 'dapur'],
            "Pujasera & Food Court" => ['pujasera', 'food court', 'foodcourt'],
            "Jajanan Kaki Lima & Gerobakan" => ['jajanan', 'gerobak', 'cemilan', 'gorengan', 'martabak', 'snack', 'pentol', 'cilok'],
            "Katering & Kue Rumahan" => ['katering', 'catering', 'kue', 'tumpeng', 'bolu', 'snack box'],

            "Service AC & Elektronik" => ['ac', 'pendingin', 'service ac', 'kulkas', 'mesin cuci', 'elektronik', 'tv', 'pompa'],
            "Bengkel Motor & Mobil" => ['bengkel', 'motor', 'mobil', 'ban', 'oli', 'reparasi motor', 'reparasi mobil', 'service motor', 'service mobil', 'ketok magic'],
            "Laundry & Cuci Pakaian" => ['laundry', 'cuci', 'gosok', 'setrika', 'dry clean'],
            "Tukang Bangunan & Renovasi" => ['tukang', 'renovasi', 'bangunan', 'cat', 'atap', 'bocor', 'las', 'pagar', 'plafon', 'keramik'],
            "Jasa Bersih Rumah & Sedot WC" => ['sedot wc', 'sedot', 'wc', 'clean', 'cleaning', 'bersih', 'fogging', 'basmi', 'hama'],

            "Pengurusan PT / CV / OSS" => ['pt', 'cv', 'oss', 'legalitas', 'izin', 'imigrasi', 'paspor', 'npwp', 'nib', 'notaris'],
            "Desain Grafis & Undangan Digital" => ['desain', 'logo', 'design', 'undangan', 'kartu nama', 'banner', 'spanduk', 'percetakan'],
            "Pembuatan Website & IT Support" => ['website', 'web', 'it', 'software', 'aplikasi', 'coding', 'komputer', 'printer', 'jaringan', 'wifi'],
            "Agensi Iklan & Sosial Media" => ['iklan', 'social media', 'sosmed', 'instagram', 'facebook', 'tiktok', 'marketing', 'ads', 'sebar brosur'],
            "Akuntansi & Pembukuan UMKM" => ['akuntansi', 'laporan keuangan', 'pembukuan', 'pajak', 'spt', 'audit'],

            "Rental Mobil" => ['rental mobil', 'sewa mobil', 'rentcar', 'rent car'],
            "Rental Motor" => ['rental motor', 'sewa motor'],
            "Jasa Lori & Pindahan Rumah" => ['lori', 'pindahan', 'pickup', 'pick up', 'truk', 'cargo', 'ekspedisi', 'angkut'],
            "Antar Jemput Anak Sekolah" => ['antar jemput', 'jemputan', 'sekolah'],
            "Travel & Tour Guide Lokal" => ['travel', 'tour', 'wisata', 'guide', 'bandara', 'tiket', 'kegiatan Batam'],

            "Toko Kelontong & Sembako" => ['kelontong', 'sembako', 'beras', 'minyak', 'grosir sembako', 'warung sembako'],
            "Butik & Fashion Lokal" => ['butik', 'baju', 'fashion', 'pakaian', 'jilbab', 'hijab', 'tas', 'sepatu', 'gamis'],
            "Konter HP, Pulsa & Aksesoris" => ['hp', 'handphone', 'pulsa', 'kuota', 'kartu perdana', 'aksesoris hp', 'iphone', 'samsung'],
            "Toko Mainan & Hobi" => ['mainan', 'boneka', 'hobi', 'game', 'sepeda', 'pancing', 'aquarium'],
            "Supplier & Agen Grosir UMKM" => ['grosir', 'supplier', 'agen', 'distributor', 'stokis'],

            "Info Kos-Kosan" => ['kos', 'kost', 'kosan', 'terima kos'],
            "Kontrakan & Sewa Rumah" => ['kontrakan', 'sewa rumah', 'kontrak rumah', 'disewakan rumah'],
            "Guest House & Homestay" => ['guest house', 'homestay', 'villa', 'penginapan'],
            "Agen Properti Perorangan" => ['properti', 'jual rumah', 'tanah', 'ruko', 'apartemen', 'sales perumahan'],

            "Loker Toko / Cafe / Resto" => ['loker toko', 'loker cafe', 'loker resto', 'pramusaji', 'kasir', 'waiter', 'waitress', 'barista', 'penjaga toko'],
            "Loker Admin & Operasional" => ['loker admin', 'loker operasional', 'accounting loker', 'staf', 'office boy', 'ob'],
            "Loker ART & Sopir" => ['loker art', 'loker sopir', 'loker supir', 'asisten rumah tangga', 'nanny', 'driver'],
            "Jasa Penyalur Tenaga Kerja" => ['penyalur', 'tenaga kerja', 'yayasan art', 'outsourcing'],

            "Barbershop & Salon" => ['barbershop', 'potong rambut', 'salon', 'facial', 'creambath', 'smoothing', 'waxing'],
            "Jasa MUA & Wedding" => ['mua', 'makeup', 'make up', 'wedding', 'pernikahan', 'pengantin', 'dekorasi', 'wo'],
            "Fotografi & Videografi" => ['foto', 'video', 'kamera', 'studio foto', 'dokumentasi', 'prewedding'],
            "Studio Tato & Henna Art" => ['tato', 'tattoo', 'henna', 'henna art', 'lukis'],

            "Les Privat & Bimbel Sekolah" => ['les', 'privat', 'bimbel', 'bimbingan belajar', 'guru privat', 'mengajar'],
            "Kursus Menyetir" => ['kursus menyetir', 'kursus mengemudi', 'menyetir mobil'],
            "Kursus Menjahit & Kerajinan" => ['menjahit', 'craft', 'kerajinan tangan', 'rajut', 'kursus jahit'],
            "Pelatihan Musik & Olahraga" => ['musik', 'gitar', 'piano', 'vokal', 'olahraga', 'renang', 'futsal', 'badminton'],

            "Klinik & Praktik Dokter/Bidan" => ['klinik', 'dokter', 'bidan', 'bersalin', 'apotek', 'obat'],
            "Pijat Tradisional & Refleksi" => ['pijat', 'urut', 'refleksi', 'spa', 'massage', 'bekam', 'terapi'],
            "Toko Herbal & Jamu" => ['herbal', 'jamu', 'madu', 'suplemen'],
            "Gym & Sanggar Senam Lokal" => ['gym', 'fitness', 'senam', 'yoga', 'zumba', 'aerobic']
        ];

        $allSubcategories = [];
        $sortOrder = 1;

        // FontAwesome icons map for main categories
        $iconMap = [
            "Kuliner & Jajanan" => "utensils",
            "Jasa Rumah Tangga & Teknis" => "screwdriver-wrench",
            "Jasa Bisnis & Legalitas" => "briefcase",
            "Rental & Transportasi Lokal" => "car",
            "Lapak Belanja & Toko Lokal" => "store",
            "Penginapan & Properti" => "house-chimney",
            "Lowongan Kerja & Karir" => "user-tie",
            "Salon, Seni & Kecantikan" => "scissors",
            "Bimbel, Kursus & Kreatif" => "graduation-cap",
            "Kesehatan & Kebugaran Mandiri" => "heart-pulse"
        ];

        // 4. Seed categories and subcategories
        foreach ($categoriesData as $catGroup) {
            $catName = $catGroup['category'];
            $icon = $iconMap[$catName] ?? 'folder';

            $parentCategory = \App\Models\Category::create([
                'name' => $catName,
                'slug' => Str::slug($catName),
                'icon' => $icon,
                'sort_order' => $sortOrder++,
                'parent_id' => null
            ]);

            foreach ($catGroup['subcategories'] as $subName) {
                $subCategory = \App\Models\Category::create([
                    'name' => $subName,
                    'slug' => Str::slug($subName),
                    'icon' => 'hashtag',
                    'sort_order' => $sortOrder++,
                    'parent_id' => $parentCategory->id
                ]);

                $allSubcategories[$subName] = $subCategory;
            }
        }

        $this->command->info("Kategori dan Subkategori baru berhasil di-seed di tabel categories!");

        // 5. Iterasi semua Listing dan kaitkan dengan subkategori baru secara pintar
        $listings = \App\Models\Listing::all();
        $subkeys = array_keys($allSubcategories);
        $totalListings = $listings->count();
        $matchedCount = 0;

        foreach ($listings as $listing) {
            $textToSearch = strtolower($listing->title . ' ' . $listing->description);
            $matchedCategoryId = null;

            // Coba mencocokkan kata kunci subkategori
            foreach ($keywordMapping as $subName => $keywords) {
                if (isset($allSubcategories[$subName])) {
                    foreach ($keywords as $kw) {
                        if (str_contains($textToSearch, strtolower($kw))) {
                            $matchedCategoryId = $allSubcategories[$subName]->id;
                            break 2; // Keluar dari loop pencocokan
                        }
                    }
                }
            }

            // Fallback ke pencocokan nama subkategori secara langsung
            if (!$matchedCategoryId) {
                foreach ($subkeys as $subName) {
                    if (str_contains($textToSearch, strtolower($subName))) {
                        $matchedCategoryId = $allSubcategories[$subName]->id;
                        break;
                    }
                }
            }

            // Fallback akhir: berikan subkategori acak
            if (!$matchedCategoryId) {
                $randomSubName = $subkeys[array_rand($subkeys)];
                $matchedCategoryId = $allSubcategories[$randomSubName]->id;
            } else {
                $matchedCount++;
            }

            // Simpan ke category_listing pivot table
            DB::table('category_listing')->insert([
                'category_id' => $matchedCategoryId,
                'listing_id' => $listing->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Perbarui kolom searchable (untuk pencarian teks)
            $listing->updateSearchableField();
        }

        $this->command->info("Sebanyak {$totalListings} Listing berhasil dikaitkan dengan kategori baru di tabel category_listing!");
        $this->command->info("Pencocokan pintar berhasil mengklasifikasikan {$matchedCount} dari {$totalListings} listing (keakuratan " . round(($matchedCount / max($totalListings, 1)) * 100, 2) . "%).");
    }
}
