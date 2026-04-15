<?php

namespace App\Services;

use App\Models\Category;
use App\Models\District;
use App\Models\Listing;
use App\Models\Subdistrict;
use App\Models\User;
use App\Models\WhatsappSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsappBotService
{
    protected WhatsappService $whatsapp;

    public function __construct(WhatsappService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function handle(string $from, string $message, ?array $media = null): void
    {
        \Illuminate\Support\Facades\Log::info("WhatsappBotService: Handing message from $from: $message");
        $phone = User::normalizeWhatsappNumber($from);
        if (!$phone) return;

        $input = trim($message);
        $lowerInput = strtolower($input);

        // Check if user is awaiting publication confirmation for a web listing
        $awaitingListingId = \Illuminate\Support\Facades\Cache::get('awaiting_publication:' . $phone);
        if ($awaitingListingId) {
            $listing = Listing::find($awaitingListingId);
            if ($listing) {
                if ($lowerInput === 'ya' || $lowerInput === 'yes') {
                    $listing->update(['is_active' => true, 'is_draft' => false]);
                    $this->whatsapp->sendMessage($phone, "✅ *Berhasil!* Postingan Anda *" . $listing->title . "* telah diterbitkan dan sekarang aktif di Sebatam.com.");
                    \Illuminate\Support\Facades\Cache::forget('awaiting_publication:' . $phone);
                    return;
                } elseif ($lowerInput === 'tidak' || $lowerInput === 'no' || $lowerInput === 'ga' || $lowerInput === 'gak') {
                    $this->whatsapp->sendMessage($phone, "👌 Baik, postingan Anda *" . $listing->title . "* tetap disimpan sebagai draft. Anda bisa menerbitkannya nanti melalui dasbor anggota.");
                    \Illuminate\Support\Facades\Cache::forget('awaiting_publication:' . $phone);
                    return;
                }
            } else {
                \Illuminate\Support\Facades\Cache::forget('awaiting_publication:' . $phone);
            }
        }



        if ($lowerInput === 'menu' || $lowerInput === 'help' || $lowerInput === 'bantuan') {
            $menuText = "🤖 *Menu Chatbot Sebatam.com*\n\n" .
                "Berikut adalah menu perintah yang tersedia:\n\n" .
                "*kode listing* — Mendapatkan kode untuk pendaftaran di website.\n\n" .
                "*buat akun* — Membuat akun Sebatam melalui WhatsApp.\n\n" .
                "*usaha sebatam* — Mendaftarkan profil usaha/bisnis Anda.\n\n" .
                "*iklan baris* — Memasang iklan baris Anda.\n\n" .
                "*batal* — Membatalkan proses yang sedang berjalan.\n\n" .
                "*menu* — Menampilkan pesan bantuan ini kembali.";
            
            $this->whatsapp->sendMessage($phone, $menuText);
            return;
        }

        if ($lowerInput === 'kode listing') {
            $user = User::where('whatsapp', $phone)->first();
            if (!$user) {
                // User not found, start registration decision
                $session = \App\Models\WhatsappSession::create([
                    'phone_number' => $phone,
                    'current_step' => 'REG_DECISION',
                    'payload' => ['flow_type' => 'get_code'],
                    'last_activity' => now(),
                ]);
                
                $msg = "👋 Halo! Nomor WhatsApp Anda belum terdaftar di Sebatam.com.\n\nUntuk mendapatkan kode listing, Anda perlu mendaftar akun terlebih dahulu. Apakah Anda ingin mendaftar sekarang? (Balas *YA* atau *TIDAK*)";
                $this->whatsapp->sendMessage($phone, $msg);
                return;
            }

            $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            \Illuminate\Support\Facades\Cache::put('registration_code:' . $code, $phone, now()->addHours(2));
            
            $msg = "🔐 *KODE LISTING ANDA: {$code}*\n\n" .
                  "Gunakan kode ini di website Sebatam.com untuk melanjutkan pendaftaran. Kode ini berlaku selama 2 jam.\n\n" .
                  "Terima kasih!";
            
            $this->whatsapp->sendMessage($phone, $msg);
            return;
        }

        $session = WhatsappSession::where('phone_number', $phone)->first();

        // If no active session, only start if keyword is "usaha sebatam" or "iklan baris"
        if (!$session) {
            if ($lowerInput !== 'usaha sebatam' && $lowerInput !== 'iklan baris') {
                return;
            }
            $flowType = ($lowerInput === 'usaha sebatam') ? 'usaha' : 'iklan';

            $session = WhatsappSession::create([
                'phone_number' => $phone,
                'current_step' => 'START',
                'payload' => ['flow_type' => $flowType],
                'last_activity' => now(),
            ]);
        } else {
            $session->update(['last_activity' => now()]);
        }

        // Global command: batal
        if ($lowerInput === 'batal') {
            $session->delete();
            $this->whatsapp->sendMessage($phone, "❌ Proses pendaftaran dibatalkan. Kirim *usaha sebatam* kapan saja untuk memulai kembali.");
            return;
        }

        $this->process($session, $input, $media);
    }

    protected function process(WhatsappSession $session, string $input, ?array $media): void
    {
        $step = $session->current_step;
        $payload = $session->payload ?? [];
        $phone = $session->phone_number;

        // Check user registration first if not already in a registration flow
        if (!str_starts_with($step, 'REG_') && $step !== 'START' && !isset($payload['user_id'])) {
            $user = User::where('whatsapp', $phone)->first();
            if (!$user) {
                $session->update(['current_step' => 'START']);
                $step = 'START';
            } else {
                $payload['user_id'] = $user->id;
                $session->update(['payload' => $payload]);
            }
        }

        switch ($step) {
            case 'REG_DECISION':
                if (strtolower($input) === 'ya' || strtolower($input) === 'yes') {
                    $session->update(['current_step' => 'REG_NAME']);
                    $this->whatsapp->sendMessage($phone, "Sip! Mari mulai pendaftarannya.\n\nSiapa *nama lengkap* Anda?");
                } elseif (strtolower($input) === 'tidak' || strtolower($input) === 'no') {
                    $session->delete();
                    $this->whatsapp->sendMessage($phone, "Baiklah. Jika Anda berubah pikiran, cukup kirim perintah *kode listing* lagi kapan saja.");
                } else {
                    $this->whatsapp->sendMessage($phone, "⚠️ Mohon jawab dengan *YA* atau *TIDAK*.");
                }
                break;

            case 'START':
                $user = User::where('whatsapp', $phone)->first();
                $flowName = ($payload['flow_type'] ?? 'usaha') === 'usaha' ? 'usaha' : 'iklan baris';
                if (!$user) {
                    $session->update(['current_step' => 'REG_NAME']);
                    $this->whatsapp->sendMessage($phone, "👋 Halo! Akun Anda belum terdaftar. Mari kita buat akun dulu sebelum mendaftarkan $flowName.\n\nSiapa nama lengkap Anda?\n\n(Ketik *batal* untuk membatalkan)");
                } else {
                    $payload['user_id'] = $user->id;
                    $session->update(['current_step' => 'ASK_TITLE', 'payload' => $payload]);
                    $this->whatsapp->sendMessage($phone, "👋 Halo " . $user->name . "! Mari daftarkan $flowName Anda.\n\nApa nama/judul $flowName Anda?\n\n(Ketik *batal* untuk membatalkan)");
                }
                break;

            case 'REG_NAME':
                if (strlen($input) < 3) {
                    $this->whatsapp->sendMessage($phone, "⚠️ Nama terlalu pendek. Mohon masukkan nama lengkap Anda:");
                    return;
                }
                $payload['reg_name'] = $input;
                $session->update(['current_step' => 'REG_EMAIL', 'payload' => $payload]);
                $this->whatsapp->sendMessage($phone, "Terima kasih, " . $input . ".\n\nApa alamat email Anda?\n(Email ini akan digunakan untuk login di website)");
                break;

            case 'REG_EMAIL':
                if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                    $this->whatsapp->sendMessage($phone, "⚠️ Format email tidak valid. Mohon masukkan email yang benar:");
                    return;
                }
                if (User::where('email', $input)->exists()) {
                    $this->whatsapp->sendMessage($phone, "⚠️ Email ini sudah terdaftar. Gunakan email lain atau hubungi admin:");
                    return;
                }

                $firstDigit = (string) random_int(1, 9);
                $password = str_repeat($firstDigit, 4) . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                $user = User::create([
                    'name' => $payload['reg_name'],
                    'email' => $input,
                    'whatsapp' => $phone,
                    'password' => Hash::make($password),
                    'role' => User::ROLE_MEMBER,
                    'is_active' => true,
                    'whatsapp_verified_at' => now(),
                ]);

                $payload['user_id'] = $user->id;

                $flowType = $payload['flow_type'] ?? 'usaha';
                if ($flowType === 'get_code') {
                    $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                    \Illuminate\Support\Facades\Cache::put('registration_code:' . $code, $phone, now()->addHours(2));
                    
                    $this->whatsapp->sendMessage($phone, "✅ Akun berhasil dibuat!\n\nEmail: " . $input . "\nPassword: " . $password . "\n\n🔐 *KODE LISTING ANDA: {$code}*\n\nGunakan kode ini di website Sebatam.com untuk melanjutkan pendaftaran. Kode ini berlaku selama 2 jam.\n\nTerima kasih!");
                    $session->delete();
                    return;
                }

                $flowName = ($flowType === 'usaha') ? 'usaha' : 'iklan baris';
                $session->update(['current_step' => 'ASK_TITLE', 'payload' => $payload]);
                $this->whatsapp->sendMessage($phone, "✅ Akun berhasil dibuat!\n\nEmail: " . $input . "\nPassword: " . $password . "\n\nAnda juga bisa login menggunakan nomor WhatsApp ini.\n(Simpan password ini. Anda bisa mengubahnya nanti di website)\n\nSekarang, mari lanjut ke pendaftaran $flowName. *Apa nama/judul $flowName Anda?*");
                break;

            case 'ASK_TITLE':
                if (strlen($input) < 5) {
                    $this->whatsapp->sendMessage($phone, "⚠️ Judul usaha terlalu pendek. Mohon berikan nama usaha yang jelas:");
                    return;
                }
                $flowName = ($payload['flow_type'] ?? 'usaha') === 'usaha' ? 'usaha' : 'iklan baris';
                $payload['title'] = $input;
                $session->update(['current_step' => 'ASK_DESC', 'payload' => $payload]);
                $this->whatsapp->sendMessage($phone, "Sip! Sekarang berikan *deskripsi singkat* tentang $flowName Anda (produk, atau layanan):");
                break;

            case 'ASK_DESC':
                if (strlen($input) < 10) {
                    $this->whatsapp->sendMessage($phone, "⚠️ Deskripsi terlalu singkat. Berikan informasi yang lebih lengkap:");
                    return;
                }
                $payload['description'] = $input;
                $payload['cat_page'] = 0;
                $session->update(['current_step' => 'ASK_CAT', 'payload' => $payload]);
                $this->sendCategoryOptions($session);
                break;

            case 'ASK_CAT':
                $page = $payload['cat_page'] ?? 0;
                $flowType = $payload['flow_type'] ?? 'usaha';
                
                $query = Category::forType($flowType)
                    ->whereNull('parent_id');

                if ($flowType === 'usaha') {
                    $categories = $query->orderBy('name')->skip($page * 8)->take(8)->get();

                    if ($input === '0') {
                        $payload['cat_page'] = $page + 1;
                        $session->update(['payload' => $payload]);
                        $this->sendCategoryOptions($session);
                        return;
                    }

                    $index = (int)$input - 1;
                    if (isset($categories[$index])) {
                        $payload['category_id'] = $categories[$index]->id;
                        $session->update(['current_step' => 'ASK_KELURAHAN', 'payload' => $payload]);
                        $this->whatsapp->sendMessage($phone, "Kategori terpilih: *" . $categories[$index]->name . "*\n\nSekarang, di *Kelurahan* mana lokasi usaha Anda?");
                    } else {
                        $this->whatsapp->sendMessage($phone, "⚠️ Pilihan tidak tersedia. Silakan balas dengan angka 1-8 atau 0 untuk kategori lainnya.");
                    }
                } else {
                    // Tampilkan semua tanpa pagination untuk iklan baris
                    $categories = $query->orderBy('order_index')->get();
                    $category = $categories->where('id', (int)$input)->first();
                    
                    if ($category) {
                        $payload['category_id'] = $category->id;
                        $session->update(['current_step' => 'ASK_KELURAHAN', 'payload' => $payload]);
                        $this->whatsapp->sendMessage($phone, "Kategori terpilih: *" . $category->name . "*\n\nSekarang, di *Kelurahan* mana lokasi iklan Anda?");
                    } else {
                        $this->whatsapp->sendMessage($phone, "⚠️ Pilihan tidak tersedia. Silakan balas dengan nomor ID Kategori yang sesuai.");
                    }
                }
                break;

            case 'ASK_KELURAHAN':
                $subdistrict = Subdistrict::whereRaw('LOWER(name) = ?', [strtolower($input)])->first();
                if (!$subdistrict) {
                    $this->whatsapp->sendMessage($phone, "⚠️ Maaf, Kelurahan '" . $input . "' tidak ditemukan di database kami. Mohon pastikan ejaan benar (Contoh: Batam Kota, Belian, dll):");
                    return;
                }

                $payload['subdistrict_id'] = $subdistrict->id;
                $payload['district_id'] = $subdistrict->district_id;
                $districtName = $subdistrict->district ? $subdistrict->district->name : '-';
                $flowNameL = ($payload['flow_type'] ?? 'usaha') === 'usaha' ? 'usaha' : 'iklan baris';
                
                if (($payload['flow_type'] ?? 'usaha') === 'iklan') {
                    $session->update(['current_step' => 'ASK_PHOTO_CONFIRM', 'payload' => $payload]);
                    $this->whatsapp->sendMessage($phone, "Lokasi terkonfirmasi: Kel. " . $subdistrict->name . ", Kec. " . $districtName . ".\n\nApakah Anda ingin mengirim *foto $flowNameL*? (Balas Ya/Tidak)");
                } else {
                    $session->update(['current_step' => 'ASK_ADDRESS', 'payload' => $payload]);
                    $this->whatsapp->sendMessage($phone, "Lokasi terkonfirmasi: Kel. " . $subdistrict->name . ", Kec. " . $districtName . ".\n\nSekarang, mohon ketikkan detail *alamat lengkap* $flowNameL Anda (Contoh: Ruko Botania 2 Blok A No. 1):");
                }
                break;

            case 'ASK_ADDRESS':
                if (strlen($input) < 5) {
                    $this->whatsapp->sendMessage($phone, "⚠️ Alamat terlalu pendek. Mohon berikan alamat yang lebih jelas:");
                    return;
                }
                $payload['address'] = $input;
                $session->update(['current_step' => 'ASK_PHOTO_CONFIRM', 'payload' => $payload]);
                $flowNameL = ($payload['flow_type'] ?? 'usaha') === 'usaha' ? 'usaha' : 'iklan baris';
                $this->whatsapp->sendMessage($phone, "✅ Alamat disimpan.\n\nApakah Anda ingin mengirim *foto $flowNameL*? (Balas Ya/Tidak)");
                break;

            case 'ASK_PHOTO_CONFIRM':
                if (strtolower($input) === 'ya') {
                    $flowNameL = ($payload['flow_type'] ?? 'usaha') === 'usaha' ? 'usaha' : 'iklan baris';
                    $session->update(['current_step' => 'ASK_PHOTO']);
                    $this->whatsapp->sendMessage($phone, "Silakan kirimkan foto $flowNameL Anda (format gambar).");
                } elseif (strtolower($input) === 'tidak') {
                    $this->finalize($session);
                } else {
                    $this->whatsapp->sendMessage($phone, "⚠️ Mohon jawab dengan *Ya* atau *Tidak*.");
                }
                break;

            case 'ASK_PHOTO':
                if ($media && isset($media['url'])) {
                    $savedPath = $this->persistWhatsappImage($media['url'], (string) ($media['mimetype'] ?? 'image/jpeg'));
                    if ($savedPath === null) {
                        $this->whatsapp->sendMessage($phone, "⚠️ Foto gagal disimpan. Mohon coba kirim ulang gambar tersebut.");

                        break;
                    }

                    $photos = $payload['photos'] ?? [];
                    $photos[] = $savedPath;
                    $payload['photos'] = $photos;

                    $limit = (int) \App\Models\LapakSettingKV::getInt('free_gallery_max', 10);

                    if (count($photos) >= $limit) {
                        $session->update(['payload' => $payload]);
                        $this->whatsapp->sendMessage($phone, "📸 Foto diterima. Anda sudah mencapai batas maksimal ($limit foto).");
                        $this->finalize($session->fresh());
                    } else {
                        $session->update(['payload' => $payload, 'current_step' => 'ASK_PHOTO_CONFIRM']);
                        $this->whatsapp->sendMessage($phone, "📸 Foto diterima. Mau kirim foto lagi? (Ya/Tidak)");
                    }
                } else {
                    $this->whatsapp->sendMessage($phone, "⚠️ Mohon kirimkan file gambar/foto.");
                }
                break;



        }
    }

    protected function sendCategoryOptions(WhatsappSession $session): void
    {
        $payload = $session->payload;
        $page = $payload['cat_page'] ?? 0;
        $phone = $session->phone_number;
        $flowType = $payload['flow_type'] ?? 'usaha';

        $query = Category::forType($flowType)
            ->whereNull('parent_id');

        if ($flowType === 'usaha') {
            $categories = $query->orderBy('name')->skip($page * 8)->take(8)->get();

            if ($categories->isEmpty() && $page > 0) {
                $payload['cat_page'] = 0;
                $session->update(['payload' => $payload]);
                $this->sendCategoryOptions($session);
                return;
            }

            $text = "Pilih Kategori Usaha (Halaman " . ($page + 1) . "):\n\n";
            foreach ($categories as $i => $cat) {
                $text .= ($i + 1) . ". " . $cat->name . "\n";
            }
            $text .= "\nBalas dengan *angka*. Balas *0* untuk kategori lainnya.";
        } else {
            $categories = $query->orderBy('order_index')->get();
            $text = "Pilih Kategori Iklan Baris:\n\n";
            foreach ($categories as $cat) {
                $text .= "{$cat->id}. {$cat->name}\n";
            }
            $text .= "\nBalas dengan *nomor ID Kategori* yang sesuai.";
        }

        $this->whatsapp->sendMessage($phone, $text);
    }

    protected function finalize(WhatsappSession $session): void
    {
        $payload = $session->payload;
        $phone = $session->phone_number;
        $flowType = $payload['flow_type'] ?? 'usaha';

        $listing = Listing::create([
            'user_id' => $payload['user_id'],
            'type' => $flowType,
            'title' => $payload['title'],
            'slug' => Str::slug($payload['title']) . '-' . uniqid(),
            'description' => $payload['description'],
            'district_id' => $payload['district_id'] ?? null,
            'subdistrict_id' => $payload['subdistrict_id'] ?? null,
            'address' => $payload['address'] ?? null,
            'whatsapp' => $phone,
            'is_active' => true,
            'is_draft' => false,
        ]);

        if (isset($payload['category_id'])) {
            $listing->categories()->attach($payload['category_id']);
        }

        if (!empty($payload['photos'])) {
            try {
                $isFirst = true;
                foreach ($payload['photos'] as $pathOrUrl) {
                    $collection = $isFirst ? 'featured' : 'gallery';
                    if (is_string($pathOrUrl) && is_file($pathOrUrl)) {
                        $listing->addMedia($pathOrUrl)->toMediaCollection($collection);
                        @unlink($pathOrUrl);
                    } else {
                        $listing->addMediaFromUrl((string) $pathOrUrl)->toMediaCollection($collection);
                    }
                    $isFirst = false;
                }
            } catch (\Exception $e) {
                Log::warning('WhatsappBotService: attach listing photos failed', [
                    'listing_id' => $listing->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $session->delete();

        $url = $flowType === 'usaha' 
            ? route('direktori.listing.show', $listing->slug) 
            : route('iklan.listing.show', $listing->slug);
            
        $flowNameL = $flowType === 'usaha' ? 'Usaha' : 'Iklan Baris';
        $loginUrl = route('login');
        $this->whatsapp->sendMessage($phone, "🎉 *Selamat!* $flowNameL Anda berhasil didaftarkan.\n\nLihat di sini: " . $url . "\n\n💡 Jika Anda ingin mengedit atau menghapus profil, silakan login ke dasbor Anda:\n" . $loginUrl . "\n\nTerima kasih telah menggunakan Sebatam.");
    }


    /**
     * Download image from GOWA/media URL and write to local temp storage.
     * Uses the same Basic Auth as the WhatsApp API when the media host matches the configured API host.
     */
    protected function persistWhatsappImage(string $mediaUrl, string $mimetype): ?string
    {
        $apiUrl = rtrim((string) config('services.whatsapp.api_url', ''), '/');
        $username = (string) config('services.whatsapp.api_user', '');
        $password = (string) config('services.whatsapp.api_pass', '');

        $request = Http::timeout(120)->accept('*/*');
        $apiHost = parse_url($apiUrl, PHP_URL_HOST);
        $mediaHost = parse_url($mediaUrl, PHP_URL_HOST);
        if ($apiHost && $mediaHost && strcasecmp((string) $apiHost, (string) $mediaHost) === 0 && $username !== '') {
            $request = $request->withBasicAuth($username, $password);
        }

        try {
            $response = $request->get($mediaUrl);
        } catch (\Throwable $e) {
            Log::warning('WhatsappBotService: media download exception', ['error' => $e->getMessage()]);

            return null;
        }

        if (!$response->successful()) {
            Log::warning('WhatsappBotService: media download HTTP error', [
                'status' => $response->status(),
                'url_host' => $mediaHost,
            ]);

            return null;
        }

        $binary = $response->body();
        if ($binary === '' || strlen($binary) < 32) {
            Log::warning('WhatsappBotService: media download empty or too small');

            return null;
        }

        $ext = $this->extensionForImageMime($mimetype);
        $dir = storage_path('app/tmp/whatsapp-bot/' . date('Y-m-d'));
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            Log::error('WhatsappBotService: could not create temp dir', ['dir' => $dir]);

            return null;
        }

        $path = $dir . '/' . Str::uuid() . '.' . $ext;
        if (file_put_contents($path, $binary) === false) {
            Log::error('WhatsappBotService: could not write media file', ['path' => $path]);

            return null;
        }

        return $path;
    }

    protected function extensionForImageMime(string $mimetype): string
    {
        $mime = strtolower(trim(explode(';', $mimetype, 2)[0]));

        return match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/bmp', 'image/x-ms-bmp' => 'bmp',
            default => 'jpg',
        };
    }

}
