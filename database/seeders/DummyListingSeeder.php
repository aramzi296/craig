<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Listing;
use App\Models\Category;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\Tag;
use App\Models\ListingPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DummyListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memulai seeding dummy user dan listing...');

        // 1. Buat 10 Dummy Users
        $dummyUsersData = [
            ['name' => 'Budi Santoso', 'email' => 'budi.santoso@batamcraig.com', 'whatsapp' => '6281234567801'],
            ['name' => 'Siti Aminah', 'email' => 'siti.aminah@batamcraig.com', 'whatsapp' => '6281234567802'],
            ['name' => 'Roni Wijaya', 'email' => 'roni.wijaya@batamcraig.com', 'whatsapp' => '6281234567803'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi.lestari@batamcraig.com', 'whatsapp' => '6281234567804'],
            ['name' => 'Andi Pratama', 'email' => 'andi.pratama@batamcraig.com', 'whatsapp' => '6281234567805'],
            ['name' => 'Mega Utami', 'email' => 'mega.utami@batamcraig.com', 'whatsapp' => '6281234567806'],
            ['name' => 'Hendra Wijaya', 'email' => 'hendra.wijaya@batamcraig.com', 'whatsapp' => '6281234567807'],
            ['name' => 'Indah Permata', 'email' => 'indah.permata@batamcraig.com', 'whatsapp' => '6281234567808'],
            ['name' => 'Yusuf Sulaiman', 'email' => 'yusuf.sulaiman@batamcraig.com', 'whatsapp' => '6281234567809'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri.handayani@batamcraig.com', 'whatsapp' => '6281234567810'],
        ];

        $users = collect();
        foreach ($dummyUsersData as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'whatsapp' => $u['whatsapp'],
                    'is_verified' => \DB::raw('true'),
                    'is_admin' => \DB::raw('false'),
                    'ads_quota' => 100,
                ]
            );
            $users->push($user);
        }
        $this->command->info('10 Dummy Users berhasil dibuat/diperbarui.');

        // 2. Data 30 Listings Premium
        $listingsData = [
            [
                'title' => 'Jual iPhone 13 Pro Max 256GB Mulus Lengkap - Nagoya',
                'description' => 'Jual iPhone 13 Pro Max 256GB warna Sierra Blue. Kondisi mulus 98%, iCloud aman bebas reset, battery health 88%. Kelengkapan fullset original. Minat COD area Nagoya atau Batam Centre.',
                'price' => 11500000,
                'subcategory_name' => 'Konter HP, Pulsa & Aksesoris',
                'photo_url' => 'https://images.unsplash.com/photo-1616348436168-de43ad0db179?w=800',
                'tags' => ['hp', 'iphone'],
                'is_featured' => true,
                'is_premium' => true,
            ],
            [
                'title' => 'Disewakan Rumah Minimalis 3 Kamar di Pasir Putih Batam Centre',
                'description' => 'Disewakan rumah minimalis siap huni di perumahan Pasir Putih, Batam Centre. Lokasi sangat strategis, dekat Megamall dan Pelabuhan Ferry Batam Centre. 3 Kamar Tidur, 2 Kamar Mandi, keamanan 24 jam.',
                'price' => 35000000,
                'subcategory_name' => 'Kontrakan & Sewa Rumah',
                'photo_url' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800',
                'tags' => ['sewa rumah', 'kontrakan'],
                'is_featured' => true,
                'is_premium' => false,
            ],
            [
                'title' => 'Rental Mobil Batam Murah Lepas Kunci Avanza / Xpander',
                'description' => 'Menyediakan jasa rental mobil Batam murah lepas kunci atau dengan sopir. Unit bersih, prima, dan wangi. Tersedia Avanza, Xpander, Innova Reborn. Pelayanan ramah dan profesional. Siap antar-jemput bandara.',
                'price' => 300000,
                'subcategory_name' => 'Rental Mobil',
                'photo_url' => 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=800',
                'tags' => ['rental mobil', 'sewa mobil'],
                'is_featured' => false,
                'is_premium' => true,
            ],
            [
                'title' => 'Cafe / Coffee Shop Estetik Instagramable di Batam Centre',
                'description' => 'Nikmati kopi premium dan suasana tenang di cafe estetik terbaru daerah Batam Centre. Cocok untuk WFC (Work From Cafe), nongkrong santai, atau kumpul komunitas. Tersedia aneka pastry dan makanan barat.',
                'price' => 25000,
                'subcategory_name' => 'Cafe & Tempat Nongkrong',
                'photo_url' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=800',
                'tags' => ['cafe', 'kopi', 'coffee'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Service AC Batam Panggilan Bergaransi Cuci & Perbaikan',
                'description' => 'Jasa service AC panggilan se-Batam. Menerima cuci AC, tambah freon, perbaikan AC bocor/tidak dingin, bongkar pasang AC baru/bekas. Teknisi jujur, berpengalaman, dan bergaransi. Hubungi kami sekarang!',
                'price' => 75000,
                'subcategory_name' => 'Service AC & Elektronik',
                'photo_url' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800',
                'tags' => ['ac', 'service ac'],
                'is_featured' => true,
                'is_premium' => true,
            ],
            [
                'title' => 'Baju Kaos Import Sisa Butik Murah - Bengkong',
                'description' => 'Baju kaos import sisa butik kualitas premium. Bahan katun tebal, adem, nyaman dipakai sehari-hari. Banyak pilihan warna dan motif kekinian. Lokasi Bengkong, bisa kirim pakai kurir lokal.',
                'price' => 45000,
                'subcategory_name' => 'Butik & Fashion Lokal',
                'photo_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=800',
                'tags' => ['baju', 'fashion'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Jasa Pembuatan PT & CV Murah Batam Pengurusan Cepat',
                'description' => 'Membantu pengurusan legalitas usaha Anda di Batam. Jasa pembuatan PT, CV, Yayasan, NIB, Izin Usaha Mikro Kecil (IUMK), dan pendaftaran merek. Proses cepat, legal, dan transparan. Konsultasi gratis!',
                'price' => 2500000,
                'subcategory_name' => 'Pengurusan PT / CV / OSS',
                'photo_url' => 'https://images.unsplash.com/photo-1450133064473-71024230f91b?w=800',
                'tags' => ['pt', 'cv', 'legalitas'],
                'is_featured' => true,
                'is_premium' => false,
            ],
            [
                'title' => 'Sewa Motor Nagoya Batam Harian / Mingguan Murah',
                'description' => 'Rental motor Batam harian, mingguan, atau bulanan murah. Lokasi Nagoya, sangat dekat dengan pusat perbelanjaan. Unit Beat, Scoopy, Vario dalam kondisi terawat. Gratis 2 helm dan jas hujan.',
                'price' => 75000,
                'subcategory_name' => 'Rental Motor',
                'photo_url' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?w=800',
                'tags' => ['rental motor', 'sewa motor'],
                'is_featured' => false,
                'is_premium' => true,
            ],
            [
                'title' => 'Terima Kos Pria/Wanita AC Kamar Mandi Dalam di Nagoya',
                'description' => 'Menerima kost baru pria/wanita di Nagoya Batam. Fasilitas lengkap: Kamar mandi dalam, kasur springbed, lemari pakaian, AC, kipas angin, parkir luas, free Wi-Fi, dan dapur bersama. Lingkungan aman dan bersih.',
                'price' => 1200000,
                'subcategory_name' => 'Info Kos-Kosan',
                'photo_url' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=800',
                'tags' => ['kos', 'kost'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Lowongan Kerja Barista & Kasir Cafe di Batam Centre',
                'description' => 'Dibutuhkan segera Barista & Kasir untuk cafe baru di area Batam Centre. Syarat: Pria/Wanita maks 25 tahun, berpengalaman di bidangnya min 1 tahun, berpenampilan menarik, jujur, dan komunikatif. Kirim CV!',
                'price' => 0,
                'subcategory_name' => 'Loker Toko / Cafe / Resto',
                'photo_url' => 'https://images.unsplash.com/photo-1498804103079-a6351b050096?w=800',
                'tags' => ['loker', 'barista', 'lowongan'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Les Privat Matematika & Bahasa Inggris SD/SMP/SMA Batam',
                'description' => 'Guru les privat berpengalaman datang ke rumah. Mengajar mata pelajaran Matematika, IPA, dan Bahasa Inggris untuk jenjang SD, SMP, dan SMA di wilayah Batam. Metode belajar menyenangkan dan mudah dipahami.',
                'price' => 50000,
                'subcategory_name' => 'Les Privat & Bimbel Sekolah',
                'photo_url' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800',
                'tags' => ['les', 'privat', 'guru privat'],
                'is_featured' => true,
                'is_premium' => true,
            ],
            [
                'title' => 'Barbershop Modern Pria Nagoya - Potong Rambut & Pomade',
                'description' => 'Dapatkan potongan rambut terbaik dan gaya rambut kekinian di barbershop terbaik Nagoya. Pelayanan ramah oleh kapster profesional. Paket potong rambut sudah termasuk cuci rambut, pijat kepala, dan styling pomade.',
                'price' => 35000,
                'subcategory_name' => 'Barbershop & Salon',
                'photo_url' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=800',
                'tags' => ['barbershop', 'salon'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Jasa Pijat Tradisional & Refleksi Panggilan Batam',
                'description' => 'Badan lelah, pegal-pegal, atau masuk angin? Kami siap datang ke rumah, hotel, atau apartemen Anda di Batam. Jasa pijat tradisional, refleksi, lulur, dan bekam oleh terapis profesional dan sopan.',
                'price' => 100000,
                'subcategory_name' => 'Pijat Tradisional & Refleksi',
                'photo_url' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800',
                'tags' => ['pijat', 'massage', 'refleksi'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Bengkel Spesialis Kaki-Kaki Mobil Nagoya Batam',
                'description' => 'Mengatasi masalah bunyi-bunyi pada mobil Anda, stir tidak stabil, spooring & balancing, service shockbreaker, rack steer, bushing, dan kaki-kaki mobil lainnya. Teknisi ahli dan peralatan modern di Nagoya.',
                'price' => 200000,
                'subcategory_name' => 'Bengkel Motor & Mobil',
                'photo_url' => 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?w=800',
                'tags' => ['bengkel', 'mobil'],
                'is_featured' => true,
                'is_premium' => true,
            ],
            [
                'title' => 'Laundry Kiloan & Satuan Bersih Wangi Bengkong',
                'description' => 'Jasa laundry kiloan dan satuan murah di Bengkong Batam. Menggunakan deterjen berkualitas, wangi tahan lama, pakaian dijamin bersih, rapi, dan disetrika dengan setrika uap. Layanan antar jemput gratis!',
                'price' => 7000,
                'subcategory_name' => 'Laundry & Cuci Pakaian',
                'photo_url' => 'https://images.unsplash.com/photo-1545173168-9f1947eebd01?w=800',
                'tags' => ['laundry', 'cuci'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Nasi Ayam Penyet Khas Batam Sambal Ijo Mantap',
                'description' => 'Nikmati kelezatan ayam penyet goreng bumbu ungkep tradisional yang gurih, disajikan hangat dengan nasi putih, lalapan segar, tahu, tempe, dan sambal ijo super pedas mantap khas warung kami.',
                'price' => 18000,
                'subcategory_name' => 'Rumah Makan & Warung Kuliner',
                'photo_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800',
                'tags' => ['nasi', 'ayam', 'warung'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Jasa Desain Logo & Cetak Undangan Pernikahan Murah',
                'description' => 'Menerima jasa desain grafis profesional: pembuatan logo usaha, kartu nama, brosur, banner, hingga cetak undangan pernikahan fisik dan digital. Harga bersahabat dengan revisi sepuasnya.',
                'price' => 150000,
                'subcategory_name' => 'Desain Grafis & Undangan Digital',
                'photo_url' => 'https://images.unsplash.com/photo-1561070791-26c113006238?w=800',
                'tags' => ['desain', 'undangan'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Jasa Pembuatan Website Company Profile & Toko Online Batam',
                'description' => 'Tingkatkan penjualan bisnis Anda dengan memiliki website profesional. Kami melayani jasa pembuatan website company profile, landing page, toko online, sistem kasir, dan IT support untuk UMKM Batam.',
                'price' => 1500000,
                'subcategory_name' => 'Pembuatan Website & IT Support',
                'photo_url' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=800',
                'tags' => ['website', 'web', 'it'],
                'is_featured' => true,
                'is_premium' => true,
            ],
            [
                'title' => 'Jasa Lori Pick Up Batam Pindahan Rumah & Angkut Barang',
                'description' => 'Melayani jasa angkutan barang menggunakan lori pickup/truk engkel di Batam. Cocok untuk pindahan rumah, kos, kantor, angkut barang belanjaan toko, dll. Driver ramah dan siap bantu angkut-angkut.',
                'price' => 150000,
                'subcategory_name' => 'Jasa Lori & Pindahan Rumah',
                'photo_url' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=800',
                'tags' => ['lori', 'pindahan'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Grosir Sembako Batam Murah Terlengkap',
                'description' => 'Toko Grosir Sembako Batam menyediakan berbagai macam kebutuhan pokok seperti beras, minyak goreng, gula, mie instan, telur, sabun, dll. Harga eceran rasa grosir, cocok untuk warung kelontong.',
                'price' => 100000,
                'subcategory_name' => 'Toko Kelontong & Sembako',
                'photo_url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800',
                'tags' => ['grosir', 'sembako'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Homestay Keluarga Dekat Pelabuhan Batam Centre Murah',
                'description' => 'Homestay bersih dan nyaman untuk keluarga di Batam Centre. Lokasi dekat Pelabuhan Ferry Batam Centre and Mega Mall. Fasilitas: 3 Kamar Tidur AC, dapur lengkap, ruang tamu luas, TV kabel, Wi-Fi gratis.',
                'price' => 450000,
                'subcategory_name' => 'Guest House & Homestay',
                'photo_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
                'tags' => ['homestay', 'guest house'],
                'is_featured' => true,
                'is_premium' => false,
            ],
            [
                'title' => 'Loker Admin Toko Online Wanita Batam Centre',
                'description' => 'Dibutuhkan Admin Toko Online Wanita untuk mengelola marketplace and media sosial. Syarat: Usia maks 28 tahun, menguasai komputer dasar, ramah dalam melayani chat pembeli, jujur, teliti. Kirim lamaran segera!',
                'price' => 0,
                'subcategory_name' => 'Loker Admin & Operasional',
                'photo_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=800',
                'tags' => ['loker', 'admin'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Jasa Makeup Artist (MUA) Wisuda & Pengantin Batam',
                'description' => 'Melayani jasa makeup professional untuk acara wisuda, lamaran, prewedding, photoshoot, hingga akad nikah. Menggunakan produk makeup branded terpercaya yang awet dan flawless di wajah.',
                'price' => 250000,
                'subcategory_name' => 'Jasa MUA & Wedding',
                'photo_url' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=800',
                'tags' => ['mua', 'makeup'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Jasa Foto & Video Prewedding Outdoor Batam',
                'description' => 'Abadikan momen spesial prewedding Anda bersama kami. Paket foto & video prewedding outdoor di Batam termasuk cetak foto ukuran besar, bingkai minimalis, softcopy semua file, dan video cinematic.',
                'price' => 1500000,
                'subcategory_name' => 'Fotografi & Videografi',
                'photo_url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=800',
                'tags' => ['foto', 'video', 'prewedding'],
                'is_featured' => true,
                'is_premium' => true,
            ],
            [
                'title' => 'Kursus Mengemudi Mobil Batam Cepat Mahir Bergaransi',
                'description' => 'Ingin cepat mahir menyetir mobil? Ikuti kelas kursus mengemudi kami. Didampingi instruktur sabar dan berpengalaman. Mobil latihan double pedal (aman). Pilihan jadwal fleksibel, dijamin cepat bisa!',
                'price' => 600000,
                'subcategory_name' => 'Kursus Menyetir',
                'photo_url' => 'https://images.unsplash.com/photo-1506015391300-4802dc74de2e?w=800',
                'tags' => ['kursus', 'menyetir'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Klinik Utama Sehat Batam - Dokter Umum & Spesialis',
                'description' => 'Klinik Pratama Sehat Batam memberikan pelayanan kesehatan ramah, profesional, dan terjangkau. Melayani pemeriksaan dokter umum, dokter gigi, KIA (Bidan), laboratorium dasar, dan apotek 24 jam.',
                'price' => 50000,
                'subcategory_name' => 'Klinik & Praktik Dokter/Bidan',
                'photo_url' => 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=800',
                'tags' => ['klinik', 'dokter'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Gym & Fitness Centre Modern Nagoya Batam',
                'description' => 'Pusat kebugaran terlengkap di Nagoya Batam. Alat fitness modern & lengkap, instruktur personal training bersertifikat, kelas senam zumba/aerobic, locker room nyaman, dan area parkir luas.',
                'price' => 150000,
                'subcategory_name' => 'Gym & Sanggar Senam Lokal',
                'photo_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=800',
                'tags' => ['gym', 'fitness'],
                'is_featured' => true,
                'is_premium' => false,
            ],
            [
                'title' => 'Toko Mainan Anak & Sepeda Murah Batam Centre',
                'description' => 'Menjual beraneka macam mainan edukatif anak, boneka, slime, mobil remote control, hingga sepeda anak segala ukuran dengan harga terjangkau di Batam Centre. Kualitas terjamin aman ber-SNI.',
                'price' => 120000,
                'subcategory_name' => 'Toko Mainan & Hobi',
                'photo_url' => 'https://images.unsplash.com/photo-1532330393533-443990a51d10?w=800',
                'tags' => ['mainan', 'sepeda'],
                'is_featured' => false,
                'is_premium' => false,
            ],
            [
                'title' => 'Jasa Tukang Bangunan & Renovasi Rumah Batam Bergaransi',
                'description' => 'Melayani jasa tukang bangunan borongan atau harian untuk bangun baru atau renovasi rumah, ruko, kantor, pengecatan dinding, perbaikan atap bocor, pasang keramik/granit, las besi pagar, dll.',
                'price' => 150000,
                'subcategory_name' => 'Tukang Bangunan & Renovasi',
                'photo_url' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800',
                'tags' => ['tukang', 'renovasi'],
                'is_featured' => true,
                'is_premium' => true,
            ],
            [
                'title' => 'Katering Harian Rantangan Murah & Sehat Batam',
                'description' => 'Katering harian untuk keluarga, kos, atau kantor di Batam. Menu berganti setiap hari (sehat, bergizi, tanpa MSG berlebih, halal). Tersedia rantangan isi 3-4 menu masakan rumah lezat.',
                'price' => 25000,
                'subcategory_name' => 'Katering & Kue Rumahan',
                'photo_url' => 'https://images.unsplash.com/photo-1543353071-10c8ba85a904?w=800',
                'tags' => ['katering', 'makanan'],
                'is_featured' => false,
                'is_premium' => false,
            ]
        ];

        // 3. Ambil semua Kategori dari database untuk pencocokan fallback
        $allCategories = Category::all();

        // 4. Generate 30 Listings
        $createdListingsCount = 0;
        foreach ($listingsData as $index => $item) {
            // Pilih user secara sekuensial atau acak
            $randomUser = $users->get($index % $users->count());

            // Pilih District acak
            $district = District::inRandomOrder()->first();
            if (!$district) {
                $this->command->error('Error: Tidak ada District ditemukan di database!');
                return;
            }

            // Pilih Subdistrict acak yang berada di bawah District tersebut
            $subdistrict = Subdistrict::where('district_id', $district->id)->inRandomOrder()->first();

            // Tentukan subkategori
            $subcategory = Category::where('name', $item['subcategory_name'])->first();
            
            // Jika subkategori tidak ditemukan, gunakan kategori acak yang memiliki parent_id (alias subkategori)
            if (!$subcategory) {
                $subcategory = Category::whereNotNull('parent_id')->inRandomOrder()->first();
            }

            // Fallback total jika masih kosong
            if (!$subcategory) {
                $subcategory = $allCategories->random();
            }

            // Buat Listing
            $listing = Listing::create([
                'user_id' => $randomUser->id,
                'district_id' => $district->id,
                'subdistrict_id' => $subdistrict ? $subdistrict->id : null,
                'title' => $item['title'],
                'slug' => Str::slug($item['title'] . '-' . uniqid()),
                'activation_code' => Str::random(10),
                'description' => $item['description'],
                'address' => 'Jl. Jenderal Sudirman No. ' . rand(1, 100) . ', Batam',
                'price' => $item['price'],
                'is_featured' => $item['is_featured'] ? \DB::raw('true') : \DB::raw('false'),
                'is_premium' => $item['is_premium'] ? \DB::raw('true') : \DB::raw('false'),
                'is_active' => \DB::raw('true'),
                'whatsapp_visibility' => 2,
                'comment_visibility' => 2,
                'expires_at' => now()->addDays(30),
            ]);

            // Hubungkan Kategori Pivot
            $listing->categories()->sync([$subcategory->id]);

            // Hubungkan Tags
            $tagIds = [];
            foreach ($item['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    [
                        'name' => $tagName,
                        'icon' => 'fa-solid fa-tag',
                        'is_approved' => \DB::raw('true'),
                    ]
                );
                $tagIds[] = $tag->id;
            }
            $listing->tags()->sync($tagIds);

            // Simpan foto fitur premium dari Unsplash
            ListingPhoto::create([
                'listing_id' => $listing->id,
                'photo_path' => $item['photo_url'],
                'thumbnail_path' => $item['photo_url'],
                'file_type' => 'image/jpeg',
                'file_size' => 0,
                'collection' => 'foto_fitur',
                'meta' => [
                    'source' => 'dummy_seeder',
                    'original_url' => $item['photo_url'],
                ]
            ]);

            // Perbarui kolom searchable
            $listing->updateSearchableField();

            $createdListingsCount++;
        }

        $this->command->info("Sukses! Berhasil membuat {$createdListingsCount} premium dummy listings.");
    }
}
