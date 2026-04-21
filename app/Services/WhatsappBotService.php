<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
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
        protected WhatsappService $whatsapp
    ) {}

    /**
     * Main entry point called from WebhookController.
     */
    public function handle(string $from, string $message): void
    {
        $phone = self::normalize($from);
        if ($phone === '') {
            return;
        }

        $text      = trim($message);
        $lowerText = strtolower($text);

        // ── Keyword: otp / login ───────────────────────────────────────────
        if ($lowerText === 'otp' || $lowerText === 'login') {
            $this->handleOtpRequest($phone);
            return;
        }

        // ── State machine for registration sub-flow ─────────────────────────
        $state = $this->getState($phone);
        if ($state !== null) {
            $this->processState($phone, $text, $lowerText, $state);
            return;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  LOGIN keyword handler
    // ─────────────────────────────────────────────────────────────────────────

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
                    'name'     => 'user-' . rand(100000, 999999),
                    'whatsapp' => $phone,
                    'email'    => $email,
                    'password' => Hash::make($password),
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
            "🔐 *Kode OTP Sebatam*\n\n" .
            "Halo, *{$user->name}*! Berikut adalah kode OTP Anda:\n\n" .
            "🔑 *KODE OTP : {$otp}*\n\n" .
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
            "Halo, *{$user->name}*! Berikut dua kode OTP untuk login:\n\n" .
            "🔑 *OTP Pertama : {$otp1}*\n" .
            "🔑 *OTP Kedua   : {$otp2}*\n\n" .
            "Kedua kode ini berlaku selama *10 menit*.\n\n" .
            "Buka halaman login di:\n{$loginUrl}\n" .
            "Lalu masukkan kedua kode di atas.\n\n" .
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
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($password),
                'whatsapp' => $phone,
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
