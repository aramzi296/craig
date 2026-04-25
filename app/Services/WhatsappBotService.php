<?php

namespace App\Services;

use App\Models\User;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\District;
use App\Models\ListingType;
use App\Models\Category;
use App\Models\PremiumPackage;
use App\Models\PremiumRequest;
use App\Services\ImageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * WhatsappBotService — handles the "login" keyword chatbot flow.
 *
 * Flow:
 *  1. User mengirim "otp" atau "login"
 *  2. Sistem cek apakah nomor WA sudah terdaftar:
 *     a. Belum terdaftar → buatkan akun otomatis (user-XXXXXX)
 *     b. Sudah terdaftar → langsung ke langkah 3
 *  3. Buat SATU kode OTP dan kirim ke user
 *  4. User memasukkan nomor WA + OTP di halaman /wa-login atau form iklan
 *  5. Jika valid → Login user
 */
class WhatsappBotService
{
    // Cache key prefix for multi-step registration state
    private const REG_CACHE_PREFIX = 'wa_reg:';
    private const REG_TTL          = 86400; // 24 jam

    public function __construct(
        protected WhatsappService $whatsapp,
        protected ImageService    $imageService
    ) {}

    /**
     * Main entry point called from WebhookController.
     */
    public function handle(string $from, string $message, array $payload = []): void
    {
        $phone = self::normalize($from);
        if ($phone === '') {
            return;
        }

        $text      = trim($message);
        $lowerText = strtolower($text);

        // ── Check Maintenance Mode ──────────────────────────────────────────
        if (get_setting('is_maintenance') === '1') {
            $user = User::where('whatsapp', $phone)->first();
            // Allow admins to skip maintenance check in bot if needed? 
            // Usually not necessary for bot flow, let's just block everyone with a nice message.
            if (!$user || !$user->is_admin) {
                $this->whatsapp->sendMessage(
                    $phone,
                    "🛠️ *Sistem Sedang Maintenance*\n\n" .
                    get_setting('maintenance_message') . "\n\n" .
                    "_Mohon hubungi admin jika ada keperluan mendesak._"
                );
                return;
            }
        }

        // ── Keyword: otp / login ───────────────────────────────────────────
        if ($lowerText === 'otp' || $lowerText === 'login') {
            $this->handleOtpRequest($phone);
            return;
        }

        // ── Keyword: pasang iklan ──────────────────────────────────────────
        if ($lowerText === 'pasang iklan') {
            $this->handleAdPostingRequest($phone);
            return;
        }

        // ── Keyword: menu ──────────────────────────────────────────────────
        if ($lowerText === 'menu') {
            $this->handleMenuRequest($phone);
            return;
        }

        // ── Keyword: kuota iklan ───────────────────────────────────────────
        if ($lowerText === 'kuota iklan') {
            $this->handleQuotaRequest($phone);
            return;
        }

        // ── Keyword: cek paket ─────────────────────────────────────────────
        if ($lowerText === 'cek paket' || $lowerText === 'paket') {
            $this->handleCheckPackageRequest($phone);
            return;
        }

        // ── State machine for registration sub-flow ─────────────────────────
        $state = $this->getState($phone);
        if ($state !== null) {
            if (str_starts_with($state['step'], 'awaiting_reg_')) {
                $this->processState($phone, $text, $lowerText, $state);
            } else {
                $this->processAdState($phone, $text, $lowerText, $state, $payload);
            }
            return;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  LOGIN keyword handler
    // ─────────────────────────────────────────────────────────────────────────

    private function handleMenuRequest(string $phone): void
    {
        $this->whatsapp->sendMessage(
            $phone,
            "🤖 *Menu Sebatam Bot*\n\n" .
            "Berikut adalah perintah yang dapat Anda gunakan:\n\n" .
            "1️⃣ *login* atau *otp*\n" .
            "Untuk mendapatkan kode akses login ke website.\n\n" .
            "2️⃣ *pasang iklan*\n" .
            "Untuk mulai memasang iklan baru secara langsung melalui WhatsApp ini.\n\n" .
            "3️⃣ *kuota iklan*\n" .
            "Untuk melihat sisa jatah iklan Anda.\n\n" .
            "4️⃣ *cek paket*\n" .
            "Untuk melihat paket premium yang Anda miliki.\n\n" .
            "5️⃣ *menu*\n" .
            "Untuk menampilkan daftar perintah ini kembali.\n\n" .
            "_Silakan ketik salah satu kata kunci di atas untuk memulai._"
        );
    }

    private function handleQuotaRequest(string $phone): void
    {
        $user = User::where('whatsapp', $phone)->first();

        if (!$user) {
            $this->whatsapp->sendMessage($phone, "❌ Nomor WhatsApp Anda belum terdaftar. Silakan ketik *pasang iklan* untuk memulai.");
            return;
        }

        $activeAdsCount = $user->listings()->where('is_active', true)->count();
        $unusedPremium = PremiumRequest::where('user_id', $user->id)
            ->whereNull('listing_id')
            ->whereIn('status', ['pending', 'active'])
            ->count();

        $msg = "📊 *Status Kuota Iklan*\n\n" .
               "Halo, *{$user->name}*!\n" .
               "Berikut adalah informasi kuota iklan Anda:\n\n" .
               "✅ Sisa Kuota Iklan: *{$user->ads_quota}*\n" .
               "📢 Iklan Aktif Saat Ini: *{$activeAdsCount}*\n";

        if ($unusedPremium > 0) {
            $msg .= "💎 Paket Premium Tersedia: *{$unusedPremium}*\n";
            $msg .= "\n_Anda memiliki paket premium yang siap digunakan! Ketik *pasang iklan* untuk menggunakannya._";
        } else {
            $msg .= "\n_Ketik *pasang iklan* untuk menggunakan kuota Anda atau hubungi admin untuk menambah kuota._";
        }

        $this->whatsapp->sendMessage($phone, $msg);
    }

    private function handleCheckPackageRequest(string $phone): void
    {
        $user = User::where('whatsapp', $phone)->first();

        if (!$user) {
            $this->whatsapp->sendMessage($phone, "❌ Nomor WhatsApp Anda belum terdaftar.");
            return;
        }

        $premiumRequests = PremiumRequest::where('user_id', $user->id)
            ->whereNull('listing_id')
            ->with('package')
            ->get();

        if ($premiumRequests->isEmpty()) {
            $this->whatsapp->sendMessage(
                $phone,
                "💎 *Paket Premium Saya*\n\n" .
                "Anda belum memiliki paket premium yang tersedia.\n\n" .
                "Ketik *pasang iklan* jika Anda ingin membeli paket premium untuk iklan baru Anda."
            );
            return;
        }

        $msg = "💎 *Paket Premium Tersedia*\n\n";
        $msg .= "Berikut adalah paket premium Anda yang belum digunakan:\n\n";

        foreach ($premiumRequests as $req) {
            $statusStr = $req->status === 'active' ? "✅ Aktif (Siap Pakai)" : "⏳ Menunggu Verifikasi";
            $msg .= "📦 *{$req->package->name}*\n" .
                   "   Status: {$statusStr}\n" .
                   "   ID: PREM-{$req->id}\n\n";
        }

        $msg .= "_Ketik *pasang iklan* untuk menggunakan paket ini pada iklan baru Anda._";

        $this->whatsapp->sendMessage($phone, $msg);
    }

    private function handleOtpRequest(string $phone): void
    {
        $user = User::where('whatsapp', $phone)->first();

        if (!$user) {
            // Jika belum ada, buatkan user baru otomatis (Registrasi Cepat)
            $randomSuffix = rand(100, 999);
            $email = $phone . '+' . $randomSuffix . '@sebatam.com';
            $password = Str::random(10);

            try {
                $user = User::create([
                    'name'      => 'user-' . rand(100000, 999999),
                    'whatsapp'  => $phone,
                    'email'     => $email,
                    'password'  => Hash::make($password),
                    'ads_quota' => get_setting('jumlah_iklan_user_default', 1),
                ]);
                Log::info('WA Bot: dynamic user created for OTP request', ['phone' => $phone, 'user_id' => $user->id]);
            } catch (\Throwable $e) {
                Log::error('WA Bot: failed to create dynamic user', ['error' => $e->getMessage()]);
                $this->whatsapp->sendMessage($phone, "❌ Gagal menyiapkan akun. Silakan coba lagi nanti.");
                return;
            }

            // Kirim pesan selamat datang untuk user baru
            $this->whatsapp->sendMessage(
                $phone,
                "🎉 *Selamat Datang di Sebatam!*\n\n" .
                "Akun Anda telah dibuat secara otomatis.\n" .
                "📧 Email: *{$email}*\n\n" .
                "Anda bisa login menggunakan nomor WA ini."
            );
        }

        // Kirim Kode OTP
        $this->issueOtp($phone, $user);
    }

    private function issueOtp(string $phone, User $user): void
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'wa_otp1'            => Hash::make($otp),
            'wa_otp1_lookup'     => hash('sha256', $otp),
            'wa_otp1_expires_at' => now()->addMinutes(15),
            // Reset OTP2 for cleanliness
            'wa_otp2'            => null,
            'wa_otp2_expires_at' => null,
        ]);

        $loginUrl = rtrim(config('app.url', 'http://localhost'), '/') . '/wa-login';

        $this->whatsapp->sendMessage(
            $phone,
            "🔐 *Login Via WhatsApp*\n\n" .
            "Halo, *{$user->name}*! Berikut kode login Anda:\n\n" .
            "🔑 *Nomor WA : {$phone}*\n" .
            "🔑 *OTP      : {$otp}*\n\n" .
            "Kode ini berlaku selama *15 menit*.\n" .
            "Gunakan nomor WA ini dan kode di atas untuk login atau pasang iklan.\n\n" .
            "Halaman Login:\n{$loginUrl}\n\n" .
            "_Jangan berikan kode ini kepada siapapun._"
        );

        Log::info('WA Bot: single OTP issued', [
            'user_id'   => $user->id,
            'phone_sfx' => $this->sfx($phone),
        ]);
    }

    /**
     * Generate OTP1 & OTP2 (unik, berbeda satu sama lain), simpan ke user,
     * dan kirim via WA beserta link halaman login.
     *
     * OTP1 disimpan:
     *  - bcrypt (wa_otp1)           → untuk verifikasi aman
     *  - SHA-256 (wa_otp1_lookup)   → untuk query/pencarian user di DB
     * OTP2 disimpan bcrypt (wa_otp2) → verifikasi setelah user ditemukan.
     *
     * Login URL yang dikirim adalah URL statis /wa-login — tidak ada nonce.
     */
    private function issueLoginOtps(string $phone, User $user): void
    {
        // ── Generate dua OTP 6-digit yang saling berbeda ─────────────────
        $otp1 = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        do {
            $otp2 = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while ($otp2 === $otp1);

        // ── Simpan ke DB ──────────────────────────────────────────────────
        $user->update([
            'wa_otp1'                   => Hash::make($otp1),           // bcrypt
            'wa_otp1_lookup'            => hash('sha256', $otp1),       // SHA-256 untuk lookup
            'wa_otp1_expires_at'        => now()->addMinutes(10),
            'wa_otp2'                   => Hash::make($otp2),           // bcrypt
            'wa_otp2_expires_at'        => now()->addMinutes(10),
            'wa_login_token'            => null,
            'wa_login_token_expires_at' => null,
        ]);

        $loginUrl = rtrim(config('app.url', 'http://localhost'), '/') . '/wa-login';

        $this->whatsapp->sendMessage(
            $phone,
            "🔐 *Login Via WhatsApp*\n\n" .
            "Halo, *{$user->name}*! Berikut kode login Anda:\n\n" .
            "🔑 *Nomor WA : {$phone}*\n" .
            "🔑 *OTP      : {$otp1}*\n\n" .
            "Kode ini berlaku selama *10 menit*.\n\n" .
            "Buka halaman login di:\n{$loginUrl}\n" .
            "Lalu masukkan nomor WA dan OTP di atas.\n\n" .
            "_Jangan berikan kode ini kepada siapapun._"
        );

        Log::info('WA Bot: dual OTP issued for login', [
            'user_id'   => $user->id,
            'phone_sfx' => $this->sfx($phone),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Registration sub-flow (state machine)
    // ─────────────────────────────────────────────────────────────────────────

    private function processState(string $phone, string $text, string $lower, array $state): void
    {
        $step = $state['step'] ?? '';

        match ($step) {
            'awaiting_reg_confirm' => $this->handleRegConfirm($phone, $lower),
            'awaiting_name'        => $this->handleRegName($phone, $text),
            'awaiting_email'       => $this->handleRegEmail($phone, $text, $state),
            default                => $this->abortUnknownStep($phone),
        };
    }

    private function handleRegConfirm(string $phone, string $lower): void
    {
        if (in_array($lower, ['ya', 'y', 'yes', 'iya', 'ok', 'oke'], true)) {
            $this->setState($phone, ['step' => 'awaiting_name']);
            $this->whatsapp->sendMessage(
                $phone,
                "📝 *Langkah 1/2 — Nama Lengkap*\n\n" .
                "Silakan kirim *nama lengkap* Anda.\n\n" .
                "_Ketik *batal* untuk membatalkan._"
            );
            return;
        }

        if (in_array($lower, ['tidak', 'n', 'no', 'gak', 'nggak', 'batal'], true)) {
            $this->clearState($phone);
            $this->whatsapp->sendMessage($phone, "Baik, pendaftaran dibatalkan. Kirim *login* kapan saja untuk mencoba lagi.");
            return;
        }

        $this->whatsapp->sendMessage($phone, "Mohon balas *YA* untuk mendaftar atau *TIDAK* untuk batal.");
    }

    private function handleRegName(string $phone, string $name): void
    {
        if (strtolower($name) === 'batal') {
            $this->clearState($phone);
            $this->whatsapp->sendMessage($phone, "Pendaftaran dibatalkan. Kirim *login* kapan saja untuk mencoba lagi.");
            return;
        }

        $name = trim($name);
        if (mb_strlen($name) < 2) {
            $this->whatsapp->sendMessage($phone, "⚠️ Nama terlalu pendek (minimal 2 karakter). Silakan coba lagi.");
            return;
        }
        if (mb_strlen($name) > 255) {
            $this->whatsapp->sendMessage($phone, "⚠️ Nama terlalu panjang. Maksimal 255 karakter.");
            return;
        }
        if (str_contains($name, '@')) {
            $this->whatsapp->sendMessage($phone, "⚠️ Sepertinya itu alamat email. Mohon kirim *nama Anda* saja terlebih dahulu.");
            return;
        }

        $this->setState($phone, ['step' => 'awaiting_email', 'name' => $name]);
        $this->whatsapp->sendMessage(
            $phone,
            "📩 *Langkah 2/2 — Alamat Email*\n\n" .
            "Terima kasih, *{$name}*!\n" .
            "Sekarang kirim *alamat email aktif* Anda untuk login di website.\n\n" .
            "_Contoh: nama@email.com_\n" .
            "_Ketik *batal* untuk membatalkan._"
        );
    }

    private function handleRegEmail(string $phone, string $emailRaw, array $state): void
    {
        if (strtolower(trim($emailRaw)) === 'batal') {
            $this->clearState($phone);
            $this->whatsapp->sendMessage($phone, "Pendaftaran dibatalkan. Kirim *login* kapan saja untuk mencoba lagi.");
            return;
        }

        $email = strtolower(trim($emailRaw));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->whatsapp->sendMessage($phone, "⚠️ Format email tidak valid. Contoh: nama@email.com. Coba lagi.");
            return;
        }

        if (User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            $this->whatsapp->sendMessage(
                $phone,
                "⚠️ Email *{$email}* sudah digunakan. Mohon gunakan email lain."
            );
            return;
        }

        $name = $state['name'] ?? null;
        if (!$name) {
            $this->clearState($phone);
            $this->whatsapp->sendMessage($phone, "❌ Sesi tidak valid. Kirim *login* untuk memulai lagi.");
            return;
        }

        // Buat password sementara
        $password = \Illuminate\Support\Str::random(10);

        try {
            $user = User::create([
                'name'      => $name,
                'email'     => $email,
                'password'  => Hash::make($password),
                'whatsapp'  => $phone,
                'ads_quota' => get_setting('jumlah_iklan_user_default', 1),
            ]);
        } catch (\Throwable $e) {
            Log::error('WA Bot: failed to create user', ['error' => $e->getMessage(), 'phone_sfx' => $this->sfx($phone)]);
            $this->whatsapp->sendMessage($phone, "❌ Gagal membuat akun. Silakan coba lagi nanti.");
            return;
        }

        $this->clearState($phone);
        Log::info('WA Bot: user registered via login flow', ['user_id' => $user->id]);

        // 1. Kirim konfirmasi akun + password sementara DAHULU
        $this->whatsapp->sendMessage(
            $phone,
            "🎉 *Akun Berhasil Dibuat!*\n\n" .
            "📧 Email    : *{$email}*\n" .
            "🔑 Password : *{$password}*\n\n" .
            "_Simpan password ini. Anda bisa mengubahnya nanti di profil website._"
        );

        // 2. Baru kirim dua OTP untuk login pertama kali
        $this->issueLoginOtps($phone, $user);
    }

    private function abortUnknownStep(string $phone): void
    {
        $this->clearState($phone);
        $this->whatsapp->sendMessage($phone, "❌ Sesi tidak dikenali. Kirim *login* untuk memulai kembali.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  State helpers (Cache-backed)
    // ─────────────────────────────────────────────────────────────────────────

    private function getState(string $phone): ?array
    {
        $val = Cache::get(self::REG_CACHE_PREFIX . $phone);
        return is_array($val) ? $val : null;
    }

    private function setState(string $phone, array $state): void
    {
        Cache::put(self::REG_CACHE_PREFIX . $phone, $state, self::REG_TTL);
    }

    private function handleAdPostingRequest(string $phone): void
    {
        $user = User::where('whatsapp', $phone)->first();

        if (!$user) {
            // Register automatically if not exists
            $randomSuffix = rand(100, 999);
            $email = $phone . '+' . $randomSuffix . '@sebatam.com';
            $password = Str::random(10);

            try {
                $user = User::create([
                    'name'      => 'user-' . rand(100000, 999999),
                    'whatsapp'  => $phone,
                    'email'     => $email,
                    'password'  => Hash::make($password),
                    'ads_quota' => get_setting('jumlah_iklan_user_default', 1),
                ]);
            } catch (\Throwable $e) {
                $this->whatsapp->sendMessage($phone, "❌ Gagal menyiapkan akun. Silakan coba lagi nanti.");
                return;
            }

            $this->whatsapp->sendMessage(
                $phone,
                "🎉 *Selamat Datang di Sebatam!*\n\n" .
                "Akun Anda telah dibuat secara otomatis untuk mulai pasang iklan.\n" .
                "📧 Email: *{$email}*"
            );
        }

        // --- NEW: Check for unused premium packages ---
        $unusedRequests = PremiumRequest::where('user_id', $user->id)
            ->whereNull('listing_id')
            ->whereIn('status', ['pending', 'active'])
            ->with('package')
            ->get();

        if ($unusedRequests->isNotEmpty()) {
            $grouped = $unusedRequests->groupBy('package_id');
            $msg = "👋 Halo! Kami menemukan Anda memiliki paket premium yang belum digunakan.\n\n";
            $packageMap = [];
            $idx = 1;
            foreach ($grouped as $packageId => $requests) {
                $pkg = $requests->first()->package;
                $packageName = $pkg ? $pkg->name : 'Paket Tidak Dikenal';
                $count = $requests->count();
                $msg .= "{$idx}. {$packageName} ({$count} slot)\n";
                $packageMap[$idx] = $packageId;
                $idx++;
            }
            $msg .= "\n*Pilih paket mana* yang akan Anda gunakan untuk iklan Anda ini?\n\n";
            $msg .= "_Atau ketik *TIDAK* untuk iklan reguler, atau *BELI* untuk beli paket baru._";

            $this->setState($phone, [
                'step'    => 'awaiting_use_existing_premium',
                'user_id' => $user->id,
                'package_map' => $packageMap,
                'ad_data' => [],
                'photos'  => []
            ]);
            $this->whatsapp->sendMessage($phone, $msg);
            return;
        }

        if ($user->ads_quota <= 0) {
            $this->setState($phone, [
                'step'    => 'awaiting_premium_upsell',
                'user_id' => $user->id,
                'ad_data' => [],
                'photos'  => []
            ]);
            $this->whatsapp->sendMessage(
                $phone,
                "⚠️ *Kuota Iklan Habis*\n\n" .
                "Maaf, kuota iklan untuk nomor WA ini sudah habis.\n\n" .
                "Apakah Anda mau menambah slot iklan? (Ya/Tidak)"
            );
            return;
        }

        // Start flow with confirmation
        $this->setState($phone, [
            'step'    => 'awaiting_start_confirmation',
            'user_id' => $user->id,
            'ad_data' => [],
            'photos'  => []
        ]);

        $this->whatsapp->sendMessage(
            $phone,
            "📣 *Pasang Iklan Baru*\n\n" .
            "Halo! Anda akan memulai proses pemasangan iklan di Sebatam.\n" .
            "Kami akan memandu Anda langkah demi langkah untuk mengisi informasi iklan Anda.\n\n" .
            "Dengan melanjutkan pemasangan iklan di Sebatam.com, Anda menyatakan bersedia untuk mengisi data dengan benar.\n\n" .
            "Apakah Anda ingin melanjutkan? (Ya/Tidak)"
        );
    }

    private function processAdState(string $phone, string $text, string $lower, array $state, array $payload): void
    {
        $step = $state['step'] ?? '';

        if ($lower === 'batal') {
            $this->clearState($phone);
            $this->whatsapp->sendMessage($phone, "❌ Pemasangan iklan dibatalkan.");
            return;
        }

        match ($step) {
            'awaiting_use_existing_premium' => $this->handleUseExistingPremium($phone, $lower, $state),
            'awaiting_premium_upsell'     => $this->handlePremiumUpsell($phone, $lower, $state),
            'awaiting_package_selection'  => $this->handlePackageSelection($phone, $text, $state),
            'awaiting_payment_confirmation' => $this->handlePaymentConfirmation($phone, $text, $state),
            'awaiting_start_confirmation' => $this->handleAdStartConfirmation($phone, $lower, $state),
            'awaiting_title'           => $this->handleAdTitle($phone, $text, $state),
            'awaiting_detail'          => $this->handleAdDetail($phone, $text, $state),
            'awaiting_price'           => $this->handleAdPrice($phone, $text, $state),
            'awaiting_photo_ask'       => $this->handleAdPhotoAsk($phone, $lower, $state),
            'awaiting_photo_upload'    => $this->handleAdPhotoUpload($phone, $payload, $state),
            'awaiting_category'        => $this->handleAdCategory($phone, $text, $state),
            'awaiting_location'        => $this->handleAdLocation($phone, $text, $state),
            'awaiting_type'            => $this->handleAdType($phone, $text, $state),
            'awaiting_wa_button'       => $this->handleAdWaButton($phone, $lower, $state),
            'awaiting_comment_section' => $this->handleAdCommentSection($phone, $lower, $state),
            'awaiting_confirmation'    => $this->handleAdConfirmation($phone, $lower, $state),
            default                    => $this->abortUnknownStep($phone),
        };
    }

    private function handleUseExistingPremium(string $phone, string $lower, array $state): void
    {
        // Handle numeric selection
        if (is_numeric($lower)) {
            $choice = (int) $lower;
            $packageId = $state['package_map'][$choice] ?? null;
            
            if ($packageId) {
                // Find one request of this package
                $request = PremiumRequest::where('user_id', $state['user_id'])
                    ->where('package_id', $packageId)
                    ->whereNull('listing_id')
                    ->whereIn('status', ['pending', 'active'])
                    ->with('package')
                    ->first();
                
                if ($request) {
                    $state['step'] = 'awaiting_title';
                    $state['premium_request_id'] = $request->id;
                    unset($state['package_map']);
                    $this->setState($phone, $state);
                    
                    $this->whatsapp->sendMessage(
                        $phone,
                        "✅ Baik, iklan ini akan menggunakan paket *{$request->package->name}* Anda.\n\n" .
                        "📝 *Langkah 1 — Judul Iklan*\n\n" .
                        "Silakan kirim *Judul Iklan* Anda."
                    );
                    return;
                }
            }
        }

        if (in_array($lower, ['ya', 'y', 'yes', 'oke', 'ok'], true)) {
            // If they just say "YA", and there's only one type of package, we can auto-select it
            if (isset($state['package_map']) && count($state['package_map']) === 1) {
                return $this->handleUseExistingPremium($phone, '1', $state);
            }

            $this->whatsapp->sendMessage($phone, "Mohon ketik *nomor paket* yang ingin Anda gunakan.");
            return;
        }

        if (in_array($lower, ['beli', 'paket', 'premium'], true)) {
            $this->showPremiumPackages($phone, $state);
            return;
        }

        if (in_array($lower, ['tidak', 't', 'no'], true)) {
            // User doesn't want to use the existing premium for THIS ad.
            // Check quota for normal ad.
            $user = User::find($state['user_id']);
            if ($user && $user->ads_quota <= 0) {
                $state['step'] = 'awaiting_premium_upsell';
                $this->setState($phone, $state);
                $this->whatsapp->sendMessage(
                    $phone,
                    "⚠️ *Kuota Iklan Habis*\n\n" .
                    "Maaf, kuota iklan reguler Anda sudah habis. Apakah Anda ingin membeli paket premium baru? (Ya/Tidak)"
                );
                return;
            }

            // Continue as normal ad creation
            unset($state['premium_request_id']);
            $state['step'] = 'awaiting_start_confirmation';
            $this->setState($phone, $state);
            $this->whatsapp->sendMessage($phone, "Baik, iklan ini akan dipasang sebagai iklan reguler.\n\nApakah Anda ingin melanjutkan? (Ya/Tidak)");
            return;
        }

        $this->whatsapp->sendMessage(
            $phone,
            "Mohon balas:\n" .
            "- *Nomor Paket* untuk menggunakan paket premium\n" .
            "- *TIDAK* untuk pasang iklan reguler\n" .
            "- *BELI* untuk beli paket baru"
        );
    }

    private function handlePremiumUpsell(string $phone, string $lower, array $state): void
    {
        if (in_array($lower, ['ya', 'y', 'yes', 'oke', 'ok'], true)) {
            $this->showPremiumPackages($phone, $state);
            return;
        }

        $this->whatsapp->sendMessage($phone, "Baik, terima kasih. Kirim *pasang iklan* kapan saja jika Anda ingin menambah slot nanti.");
        $this->clearState($phone);
    }

    /**
     * Helper to show premium packages to user
     */
    private function showPremiumPackages(string $phone, array $state): void
    {
        $packages = PremiumPackage::where('is_active', true)->orderBy('price')->get();
        
        if ($packages->isEmpty()) {
            $this->whatsapp->sendMessage($phone, "❌ Maaf, saat ini belum ada paket premium yang tersedia. Silakan hubungi admin.");
            $this->clearState($phone);
            return;
        }

        $list = "💎 *Pilih Paket Iklan Premium*\n\n";
        $idx = 1;
        $packageMap = [];
        foreach ($packages as $pkg) {
            $list .= "{$idx}. *{$pkg->name}*\n   Harga: Rp " . number_format($pkg->price, 0, ',', '.') . "\n   Durasi: {$pkg->duration_days} hari\n\n";
            $packageMap[$idx] = $pkg->id;
            $idx++;
        }
        $list .= "_Ketik nomor paket yang Anda pilih._";

        $state['step'] = 'awaiting_package_selection';
        $state['package_map'] = $packageMap;
        $this->setState($phone, $state);
        $this->whatsapp->sendMessage($phone, $list);
    }

    private function handlePackageSelection(string $phone, string $text, array $state): void
    {
        $choice = (int) $text;
        $packageId = $state['package_map'][$choice] ?? null;

        if (!$packageId) {
            $this->whatsapp->sendMessage($phone, "⚠️ Pilihan tidak valid. Mohon ketik nomor paket yang tersedia.");
            return;
        }

        $package = PremiumPackage::find($packageId);
        if (!$package) {
            $this->whatsapp->sendMessage($phone, "❌ Paket tidak ditemukan. Silakan coba lagi.");
            return;
        }

        $uniqueCode = rand(100, 999);
        $total = $package->price + $uniqueCode;

        $state['step'] = 'awaiting_payment_confirmation';
        $state['premium_package_id'] = $package->id;
        $state['unique_code'] = $uniqueCode;
        $this->setState($phone, $state);

        // Kirim QRIS dengan info total bayar
        $qrisUrl = rtrim((string)config('app.url'), '/') . '/qris.jpeg';
        $this->whatsapp->sendImage(
            $phone, 
            $qrisUrl, 
            "🙏 *Terima kasih sudah memilih paket {$package->name}*\n\n" .
            "💰 *Total Bayar: Rp " . number_format($total, 0, ',', '.') . "*\n" .
            "⚠️ _Penting: Mohon transfer tepat sesuai nominal di atas (termasuk 3 digit terakhir) agar verifikasi otomatis lebih cepat._\n\n" .
            "Silakan simpan/scan QRIS di atas untuk melakukan pembayaran."
        );

        $this->whatsapp->sendMessage(
            $phone,
            "Apakah Anda sudah bayar? (Ya/Tidak)"
        );
    }

    private function handlePaymentConfirmation(string $phone, string $text, array $state): void
    {
        $lower = strtolower(trim($text));
        
        if (in_array($lower, ['1', 'ya', 'y', 'yes', 'sudah'], true)) {
            // Create Premium Request immediately so it's persisted even if user cancels ad creation later
            $premiumRequest = PremiumRequest::create([
                'user_id' => $state['user_id'],
                'listing_id' => null,
                'package_id' => $state['premium_package_id'],
                'unique_code' => $state['unique_code'] ?? 0,
                'status' => 'pending',
            ]);

            $state['premium_request_id'] = $premiumRequest->id;
            $state['step'] = 'awaiting_title';
            $this->setState($phone, $state);

            $this->whatsapp->sendMessage($phone, "✅ Terima kasih! Admin akan melakukan verifikasi pembayaran Anda.\n\nSekarang, mari kita lanjutkan ke proses pembuatan iklan Anda.");
            
            $this->whatsapp->sendMessage(
                $phone,
                "📝 *Langkah 1 — Judul Iklan*\n\n" .
                "Silakan kirim *Judul Iklan* Anda.\n" .
                "(Contoh: Jual Honda Vario 2020 Mulus)\n\n" .
                "_Ketik *batal* untuk membatalkan._"
            );
            return;
        }

        if (in_array($lower, ['2', 'tidak', 't', 'no', 'batal', 'belum'], true)) {
            $this->clearState($phone);
            $this->whatsapp->sendMessage($phone, "❌ Pembayaran belum dilakukan atau dibatalkan. Proses pasang iklan dihentikan. Silakan ketik *pasang iklan* kembali jika sudah siap.");
            return;
        }

        $this->whatsapp->sendMessage($phone, "Mohon balas *YA* jika Anda sudah melakukan pembayaran, atau *TIDAK* untuk membatalkan.");
    }

    private function handleAdStartConfirmation(string $phone, string $lower, array $state): void
    {
        if (in_array($lower, ['ya', 'y', 'yes', 'oke', 'ok', 'lanjut', 'lanjutkan'], true)) {
            $state['step'] = 'awaiting_title';
            $this->setState($phone, $state);

            $this->whatsapp->sendMessage(
                $phone,
                "📝 *Langkah 1 — Judul Iklan*\n\n" .
                "Silakan kirim *Judul Iklan* Anda.\n" .
                "(Contoh: Jual Honda Vario 2020 Mulus)\n\n" .
                "_Ketik *batal* untuk membatalkan._"
            );
            return;
        }

        if (in_array($lower, ['tidak', 'n', 'no', 'gak', 'nggak', 'batal'], true)) {
            $this->clearState($phone);
            $this->whatsapp->sendMessage($phone, "Baik, pemasangan iklan dibatalkan. Kirim *pasang iklan* kapan saja untuk mencoba lagi.");
            return;
        }

        $this->whatsapp->sendMessage($phone, "Mohon balas *YA* untuk melanjutkan atau *TIDAK* untuk batal.");
    }

    private function handleAdTitle(string $phone, string $text, array $state): void
    {
        if (strlen($text) < 5) {
            $this->whatsapp->sendMessage($phone, "⚠️ Judul terlalu pendek. Minimal 5 karakter.");
            return;
        }

        $state['ad_data']['title'] = $text;
        $state['step'] = 'awaiting_detail';
        $this->setState($phone, $state);

        $this->whatsapp->sendMessage($phone, "📝 Kirimkan *Detail Iklan* (Deskripsi) Anda.");
    }

    private function handleAdDetail(string $phone, string $text, array $state): void
    {
        if (strlen($text) < 10) {
            $this->whatsapp->sendMessage($phone, "⚠️ Deskripsi terlalu pendek. Minimal 10 karakter.");
            return;
        }

        $state['ad_data']['description'] = $text;
        $state['step'] = 'awaiting_price';
        $this->setState($phone, $state);

        $this->whatsapp->sendMessage($phone, "💰 Masukkan *Harga* (hanya angka). Ketik *0* jika tidak ingin menampilkan harga.");
    }

    private function handleAdPrice(string $phone, string $text, array $state): void
    {
        $price = preg_replace('/\D/', '', $text);
        if ($price === '' && $text !== '0') {
            $this->whatsapp->sendMessage($phone, "⚠️ Mohon masukkan angka saja atau ketik 0.");
            return;
        }

        $state['ad_data']['price'] = (int) $price;
        $state['step'] = 'awaiting_photo_ask';
        $this->setState($phone, $state);

        $this->whatsapp->sendMessage($phone, "📸 Apakah Anda ingin mengirim *Foto ke-1*? (Ya/Tidak)");
    }

    private function handleAdPhotoAsk(string $phone, string $lower, array $state): void
    {
        $photoCount = count($state['photos'] ?? []);
        $maxPhotos = get_setting('max_foto_iklan', 4);

        if (in_array($lower, ['ya', 'y', 'yes', 'oke', 'ok'], true)) {
            $state['step'] = 'awaiting_photo_upload';
            $this->setState($phone, $state);
            $this->whatsapp->sendMessage($phone, "🖼️ Silakan kirim fotonya sekarang.");
            return;
        }

        // Jika tidak/selesai, lanjut ke kategori
        $state['step'] = 'awaiting_category';
        $this->setState($phone, $state);
        $this->whatsapp->sendMessage($phone, "📂 Ketik *Kategori* iklan Anda (maksimal 30 huruf).");
    }

    private function handleAdPhotoUpload(string $phone, array $payload, array $state): void
    {
        // GOWA payload can be in 'data' or 'payload'
        $data = $payload['data'] ?? ($payload['payload'] ?? []);
        
        // Find the image path/URL
        $imagePath = $data['image'] ?? ($data['url'] ?? ($data['file_url'] ?? null));
        
        if (is_array($imagePath)) {
            $imagePath = $imagePath['url'] ?? null;
        }

        if (!$imagePath) {
            $this->whatsapp->sendMessage($phone, "⚠️ Media tidak ditemukan. Silakan kirim fotonya atau ketik *batal*.");
            return;
        }

        // Build full URL if relative
        $fullUrl = $imagePath;
        if (!str_starts_with($imagePath, 'http')) {
            $baseUrl = rtrim((string) config('services.whatsapp.api_url'), '/');
            $fullUrl = $baseUrl . '/' . ltrim((string) $imagePath, '/');
        }

        try {
            // Log the attempt for debugging
            Log::info("WA Bot: Attempting to download photo from: " . $fullUrl);

            $response = Http::timeout(30)->get($fullUrl);
            if (!$response->successful()) {
                throw new \Exception("Gagal mengunduh media. Status: " . $response->status());
            }

            $state['photos'][] = base64_encode($response->body());
            
            $photoCount = count($state['photos']);
            $maxPhotos = get_setting('max_foto_iklan', 4);

            if ($photoCount >= $maxPhotos) {
                $state['step'] = 'awaiting_category';
                $this->setState($phone, $state);
                $this->whatsapp->sendMessage($phone, "✅ Foto ke-{$photoCount} diterima. Anda sudah mencapai batas maksimal foto.\n\n📂 Ketik *Kategori* iklan Anda (maksimal 30 huruf).");
            } else {
                $next = $photoCount + 1;
                $state['step'] = 'awaiting_photo_ask';
                $this->setState($phone, $state);
                $this->whatsapp->sendMessage($phone, "✅ Foto ke-{$photoCount} diterima. Apakah ingin mengirim *Foto ke-{$next}*? (Ya/Tidak)");
            }
        } catch (\Throwable $e) {
            Log::error("WA Bot: Photo upload error: " . $e->getMessage());
            $this->whatsapp->sendMessage($phone, "❌ Gagal memproses foto. Pastikan bot dapat mengakses file di: {$fullUrl}\n\nSilakan coba kirim ulang atau ketik *tidak* untuk lanjut.");
        }
    }

    private function handleAdCategory(string $phone, string $text, array $state): void
    {
        if (mb_strlen($text) > 30) {
            $this->whatsapp->sendMessage($phone, "⚠️ Kategori terlalu panjang (maksimal 30 huruf).");
            return;
        }

        $state['ad_data']['category_name'] = $text;
        
        // Show districts
        $districts = District::orderBy('name')->pluck('name', 'id')->toArray();
        $list = "📍 *Pilih Lokasi* (Ketik nomornya):\n\n";
        foreach ($districts as $id => $name) {
            $list .= "{$id}. {$name}\n";
        }

        $state['step'] = 'awaiting_location';
        $this->setState($phone, $state);
        $this->whatsapp->sendMessage($phone, $list);
    }

    private function handleAdLocation(string $phone, string $text, array $state): void
    {
        $districtId = (int) $text;
        if (!District::where('id', $districtId)->exists()) {
            $this->whatsapp->sendMessage($phone, "⚠️ Lokasi tidak valid. Mohon pilih nomor yang tertera.");
            return;
        }

        $state['ad_data']['district_id'] = $districtId;

        // Show Types
        $types = ListingType::orderBy('sort_order')->pluck('name', 'id')->toArray();
        $list = "🏷️ *Pilih Tipe Iklan* (Ketik nomornya):\n\n";
        foreach ($types as $id => $name) {
            $list .= "{$id}. {$name}\n";
        }

        $state['step'] = 'awaiting_type';
        $this->setState($phone, $state);
        $this->whatsapp->sendMessage($phone, $list);
    }

    private function handleAdType(string $phone, string $text, array $state): void
    {
        $typeId = (int) $text;
        if (!ListingType::where('id', $typeId)->exists()) {
            $this->whatsapp->sendMessage($phone, "⚠️ Tipe tidak valid. Mohon pilih nomor yang tertera.");
            return;
        }

        $state['ad_data']['listing_type_id'] = $typeId;
        $state['step'] = 'awaiting_wa_button';
        $this->setState($phone, $state);

        $this->whatsapp->sendMessage($phone, "📲 Tampilkan tombol WhatsApp di iklan? (Ya/Tidak)");
    }

    private function handleAdWaButton(string $phone, string $lower, array $state): void
    {
        $state['ad_data']['whatsapp_visibility'] = in_array($lower, ['ya', 'y', 'yes', 'oke', 'ok'], true);
        $state['step'] = 'awaiting_comment_section';
        $this->setState($phone, $state);

        $this->whatsapp->sendMessage($phone, "💬 Aktifkan kolom komentar? (Ya/Tidak)");
    }

    private function handleAdCommentSection(string $phone, string $lower, array $state): void
    {
        $state['ad_data']['comment_visibility'] = in_array($lower, ['ya', 'y', 'yes', 'oke', 'ok'], true);
        
        $title = $state['ad_data']['title'];
        $desc = $state['ad_data']['description'];
        $price = $state['ad_data']['price'] > 0 ? "Rp " . number_format($state['ad_data']['price']) : "Hubungi Kami";
        $loc = District::find($state['ad_data']['district_id'])->name;
        $type = ListingType::find($state['ad_data']['listing_type_id'])->name;
        $cat = $state['ad_data']['category_name'];
        $wa = $state['ad_data']['whatsapp_visibility'] ? "Aktif" : "Sembunyi";
        $comm = $state['ad_data']['comment_visibility'] ? "Aktif" : "Nonaktif";

        $summary = "🧐 *Konfirmasi Iklan*\n\n" .
                  "📌 Judul: {$title}\n" .
                  "📝 Detail: {$desc}\n" .
                  "💰 Harga: {$price}\n" .
                  "📂 Kategori: {$cat}\n" .
                  "📍 Lokasi: {$loc}\n" .
                  "🏷️ Tipe: {$type}\n" .
                  "📲 Tombol WA: {$wa}\n" .
                  "💬 Komentar: {$comm}\n" .
                  "🖼️ Foto: " . count($state['photos']) . " foto\n\n" .
                  "*Terbitkan iklan ini?* (Ya/Tidak)";

        $state['step'] = 'awaiting_confirmation';
        $this->setState($phone, $state);
        $this->whatsapp->sendMessage($phone, $summary);
    }

    private function handleAdConfirmation(string $phone, string $lower, array $state): void
    {
        if (in_array($lower, ['ya', 'y', 'yes', 'oke', 'ok'], true)) {
            try {
                $ad = $state['ad_data'];
                $listing = Listing::create([
                    'user_id' => $state['user_id'],
                    'listing_type_id' => $ad['listing_type_id'],
                    'district_id' => $ad['district_id'],
                    'title' => $ad['title'],
                    'slug' => Str::slug($ad['title']) . '-' . Str::random(5),
                    'description' => $ad['description'],
                    'price' => $ad['price'],
                    'whatsapp_visibility' => $ad['whatsapp_visibility'],
                    'comment_visibility' => $ad['comment_visibility'],
                    'is_active' => true,
                    'expires_at' => now()->addDays((int)get_setting('expire_iklan', 30)),
                ]);

                // Handle Category
                $category = Category::where('name', 'like', $ad['category_name'])->first();
                if (!$category) {
                    $category = Category::create([
                        'name' => $ad['category_name'],
                        'slug' => Str::slug($ad['category_name']),
                    ]);
                }
                $listing->categories()->attach($category->id);

                // Handle Photos
                foreach ($state['photos'] as $idx => $base64) {
                    $imageData = base64_decode($base64);
                    $tmpFile = tempnam(sys_get_temp_dir(), 'wa_photo_');
                    file_put_contents($tmpFile, $imageData);
                    
                    // Manually upload to ImageKit via ImageService if we can adapt it
                    // Or we can just use a modified version of uploadListingPhoto
                    // For now, I'll assume we can handle it or I'll provide a helper
                    $this->uploadPhotoFromBot($listing->id, $tmpFile, $idx === 0 ? 'foto_fitur' : 'gallery');
                    unlink($tmpFile);
                }

                // Handle Premium Request if applicable
                if (isset($state['premium_request_id'])) {
                    $premiumRequest = PremiumRequest::find($state['premium_request_id']);
                    if ($premiumRequest) {
                        $premiumRequest->update([
                            'listing_id' => $listing->id,
                        ]);
                        // If it was already 'active' (approved by admin while user was idling),
                        // the listing becomes premium immediately.
                        if ($premiumRequest->status === 'active') {
                            $listing->update(['is_premium' => true]);
                        }
                    }
                } elseif (isset($state['premium_package_id'])) {
                    // Fallback for older states if any
                    PremiumRequest::create([
                        'user_id' => $state['user_id'],
                        'listing_id' => $listing->id,
                        'package_id' => $state['premium_package_id'],
                        'unique_code' => $state['unique_code'] ?? 0,
                        'status' => 'pending',
                    ]);
                    $listing->update(['is_premium' => true]);
                }

                // Decrement quota if not using premium package (or maybe premium package adds a slot?)
                // The prompt says "lanjutkan ke proses pembuatan iklan dengan fitur paket premium"
                // which usually implies they paid for THIS ad to be premium.
                // If they have 0 quota, we allow them to proceed because they are paying for a premium slot.
                // Decrement quota if not using premium package
                if (!isset($state['premium_request_id']) && !isset($state['premium_package_id'])) {
                    $user = User::find($state['user_id']);
                    if ($user && $user->ads_quota > 0) {
                        $user->decrement('ads_quota');
                    }
                }

                $this->clearState($phone);
                $this->whatsapp->sendMessage(
                    $phone,
                    "🎉 *Iklan Berhasil Diterbitkan!*\n\n" .
                    "Terima kasih telah memasang iklan di Sebatam.\n" .
                    "Iklan Anda kini sudah online.\n\n" .
                    "🔗 *Link Iklan:* " . route('listings.show', $listing->slug) . "\n\n" .
                    "ℹ️ *Informasi:* Jika mau edit iklan, bisa dilakukan di website. Cara masuk website adalah dengan kirim pesan *login* untuk mendapatkan link masuk."
                );
            } catch (\Throwable $e) {
                Log::error("WA Bot: Final publish error: " . $e->getMessage());
                $this->whatsapp->sendMessage($phone, "❌ Terjadi kesalahan saat menerbitkan iklan. Silakan coba lagi nanti.");
            }
        } else {
            $this->clearState($phone);
            $this->whatsapp->sendMessage($phone, "🗑️ Iklan dibatalkan dan dihapus. Terima kasih.");
        }
    }

    private function uploadPhotoFromBot(int $listingId, string $filePath, string $collection): void
    {
        // We need to simulate an UploadedFile or just call ImageKit directly
        // I'll add a helper method to ImageService to accept a file path
        // For now, I'll call a method I'll add later
        try {
            $folder = "/listings/{$listingId}";
            $fileName = uniqid() . '.jpg';

            $upload = $this->imageService->uploadFromPath($filePath, $fileName, $folder);
            
            ListingPhoto::create([
                'listing_id' => $listingId,
                'photo_path' => $upload->filePath,
                'thumbnail_path' => $upload->filePath, 
                'collection' => $collection,
                'ik_file_id' => $upload->fileId,
            ]);
        } catch (\Throwable $e) {
            Log::error("WA Bot: Photo upload helper error: " . $e->getMessage());
        }
    }

    private function clearState(string $phone): void
    {
        Cache::forget(self::REG_CACHE_PREFIX . $phone);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────────────────

    public static function normalize(string $input): string
    {
        $digits = preg_replace('/\D/', '', $input) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    }

    private function sfx(string $phone): string
    {
        return strlen($phone) >= 4 ? substr($phone, -4) : $phone;
    }
}
