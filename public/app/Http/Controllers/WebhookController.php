<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WaMessage;
use App\Services\WhatsappService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    private const MAX_WA_VERIFY_ATTEMPTS = 5;

    private const WA_REG_TTL_SECONDS = 86400;

    /** @var array<string, mixed>|null */
    private ?array $waRegistrationStateCache = null;

    public function __construct(
        protected \App\Services\WhatsappService $whatsappService,
        protected \App\Services\WhatsappBotService $whatsappBotService
    ) {}

    /**
     * Webhook handler for GOWA (go-whatsapp-web-multidevice).
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'] ?? 'unknown';

        Log::info('GOWA Webhook: event received', $this->summarizePayloadForLog($payload));

        if (! $this->isGowaIncomingChatMessageEvent((string) $event)) {
            return response()->json(['status' => 'ignored']);
        }

        $data = $payload['data'] ?? ($payload['payload'] ?? null);

        if (! $data) {
            Log::warning('GOWA Webhook: No data found in message event');

            return response()->json(['status' => 'no_data']);
        }

        $from = $data['from'] ?? '';
        $from = str_replace(['@c.us', '@s.whatsapp.net'], '', $from);
        $from = preg_replace('/\D/', '', $from) ?? '';

        $isFromMeRaw = $data['is_from_me'] ?? ($data['from_me'] ?? ($data['fromMe'] ?? false));
        $isFromMe = $this->normalizeBoolish($isFromMeRaw);
        if ($isFromMe) {
            Log::info('GOWA Webhook: Message from self ignored');

            return response()->json(['status' => 'ignored']);
        }

        $to = str_replace(['@c.us', '@s.whatsapp.net'], '', ($data['to'] ?? ''));
        $to = preg_replace('/\D/', '', $to) ?? '';
        $body = $data['message'] ?? ($data['body'] ?? ($data['content'] ?? ''));

        $imagePath = $data['image'] ?? ($data['media_url'] ?? null);

        if ($from === '' || ($body === '' && !$imagePath)) {
            Log::warning('GOWA Webhook: Incomplete message data', [
                'from_suffix' => $from !== '' ? substr($from, -4) : null,
                'has_image' => !!$imagePath,
            ]);

            return response()->json(['status' => 'incomplete']);
        }

        try {
            WaMessage::create([
                'from_number' => $from,
                'to_number' => $to,
                'message' => mb_substr((string) $body, 0, 2000),
                'raw_json' => [
                    'event' => $event,
                    'from_suffix' => substr($from, -4),
                    'to_suffix' => $to !== '' ? substr($to, -4) : null,
                ],
            ]);
            Log::info('GOWA Webhook: Message stored', ['from_suffix' => substr($from, -4)]);
        } catch (\Throwable $e) {
            Log::error('GOWA Webhook: Failed to save message. '.$e->getMessage());
        }

        $trimmedBody = trim((string) $body);
        $upperBody = strtoupper($trimmedBody);

        // Perilaku sama seperti sebelum fitur "buat akun": verifikasi/OTP tidak kalah prioritas dari wizard pendaftaran WA.
        if ($upperBody === 'VERIFIKASI') {
            $this->clearWaRegistrationState($from);
            $this->whatsappService->sendMessage($from, 'Halo! Untuk mengaktifkan akun Anda, silakan ketik dan kirimkan *4-digit Kode Verifikasi* yang tertera di halaman verifikasi website Sebatam.com.');
            Log::info('Verification instruction sent', ['from_suffix' => substr($from, -4)]);

            return response()->json(['status' => 'success']);
        }

        if ($upperBody === 'OTP') {
            $this->processOtpRequest($from);

            return response()->json(['status' => 'success']);
        }

        if (str_starts_with($upperBody, 'OTP LAPAK')) {
            $parts = explode(' ', $trimmedBody);
            if (count($parts) > 2) {
                // User direct: "otp lapak 1234"
                $this->processLapakActivation($from, $trimmedBody);
            } else {
                // User start: "otp lapak"
                $normalizedFrom = $this->normalizePhoneNumber($from);
                
                // Pre-check if any non-active lapak (and not draft) exists for this number
                $exists = \App\Models\Listing::where('type', 'lapak')
                    ->where('is_active', false)
                    ->where('is_draft', false)
                    ->get()
                    ->filter(function($l) use ($normalizedFrom) {
                        return $this->normalizePhoneNumber($l->whatsapp) === $normalizedFrom;
                    })->count() > 0;

                if (!$exists) {
                    $this->whatsappService->sendMessage($from, "Maaf, tidak ada lapak dalam antrean aktivasi yang terdaftar dengan nomor WhatsApp ini.\n\nPastikan Anda sudah membuat iklan lapak di website Sebatam.com dan memilih opsi 'Simpan dan Publikasikan'.");
                } else {
                    $this->putWaRegistrationState($from, ['step' => 'awaiting_lapak_otp']);
                    $this->whatsappService->sendMessage($from, "Silakan masukkan *Nomor OTP/ID Lapak* yang ingin Anda aktifkan.\n\n_Contoh: 000123_");
                }
            }

            return response()->json(['status' => 'success']);
        }



        if (preg_match('/^\d{4}$/', $trimmedBody) === 1) {
            $this->processVerificationCode($from, $trimmedBody);

            return response()->json(['status' => 'success']);
        }

        // Handle standalone registration flow ('buat akun')
        Log::info('Checking processWaRegistrationFlow for: ' . $trimmedBody);
        if ($this->processWaRegistrationFlow($from, $trimmedBody)) {
            Log::info('processWaRegistrationFlow HANDLED the message');
            return response()->json(['status' => 'success']);
        }
        Log::info('processWaRegistrationFlow DID NOT handle the message');

        // Use the new persistent Bot Service for registration and business listing
        $dataMedia = null;
        
        // Handle GOWA image payload structure
        if ($imagePath) {
            // Check if it's already a full URL or a relative path
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                $fullImageUrl = $imagePath;
            } else {
                $baseUrl = rtrim(config('services.whatsapp.api_url'), '/');
                $fullImageUrl = $baseUrl . '/' . ltrim($imagePath, '/');
            }

            $dataMedia = [
                'url' => $fullImageUrl,
                'mimetype' => $data['mimetype'] ?? 'image/jpeg', // Default to image/jpeg if missing
            ];
        }

        $this->whatsappBotService->handle($from, $trimmedBody, $dataMedia);

        return response()->json(['status' => 'success']);
    }

    private function processVerificationCode(string $normalizedFrom, string $code): void
    {
        $user = User::query()
            ->where('whatsapp', $normalizedFrom)
            ->whereNotNull('verification_code_hash')
            ->first();

        if (! $user) {
            $this->whatsappService->sendMessage($normalizedFrom, 'Maaf, kode verifikasi yang Anda masukkan salah atau tidak sesuai dengan nomor WhatsApp ini.');
            Log::warning('Verification failed: no matching user', ['from_suffix' => substr($normalizedFrom, -4)]);

            return;
        }

        if ($user->whatsapp_verified_at || $user->is_active) {
            return;
        }

        if ($user->wa_verify_failed_attempts >= self::MAX_WA_VERIFY_ATTEMPTS) {
            $this->whatsappService->sendMessage($normalizedFrom, 'Terlalu banyak percobaan gagal. Tunggu beberapa saat atau minta kode baru dari halaman verifikasi di website.');

            return;
        }

        if ($user->verification_code_expires_at && $user->verification_code_expires_at->isPast()) {
            $this->whatsappService->sendMessage($normalizedFrom, 'Kode verifikasi sudah kedaluwarsa. Silakan buat kode baru dari halaman verifikasi di website.');
            Log::notice('Verification failed: code expired', ['user_id' => $user->id]);

            return;
        }

        if (! Hash::check($code, $user->verification_code_hash)) {
            $user->increment('wa_verify_failed_attempts');
            $this->whatsappService->sendMessage($normalizedFrom, 'Maaf, kode verifikasi yang Anda masukkan salah atau tidak sesuai dengan nomor WhatsApp ini.');
            Log::warning('Verification failed: bad code', [
                'user_id' => $user->id,
                'from_suffix' => substr($normalizedFrom, -4),
            ]);

            return;
        }

        $user->update([
            'whatsapp_verified_at' => now(),
            'is_active' => true,
            'verification_code_hash' => null,
            'verification_code_expires_at' => null,
            'wa_verify_failed_attempts' => 0,
        ]);

        $this->whatsappService->sendMessage($normalizedFrom, '✅ Selamat! Akun Anda telah aktif. Sekarang Anda dapat mulai menggunakan layanan di Sebatam.com.');
        Log::info('User verified via WhatsApp', ['user_id' => $user->id]);
    }

    private function processLapakActivation(string $normalizedFrom, string $body): void
    {
        // Body format: "otp lapak 123456"
        $parts = explode(' ', $body);
        $otpCode = end($parts);

        if (!is_numeric($otpCode) || strlen($otpCode) !== 6) {
            $this->whatsappService->sendMessage($normalizedFrom, "Format nomor aktivasi tidak valid. Mohon masukkan 6 digit angka yang muncul di dashboard Sebatam.com.\n\nKetik *otp lapak* kembali untuk mengulangi proses aktivasi.");
            return;
        }

        // Find listing with this OTP
        $listing = \App\Models\Listing::where('type', 'lapak')
            ->where('is_active', false)
            ->where('is_draft', false)
            ->get()
            ->filter(function($l) use ($otpCode) {
                return ($l->meta['lapak_otp'] ?? '') === (string)$otpCode;
            })->first();

        if (!$listing) {
            $this->whatsappService->sendMessage($normalizedFrom, "Maaf, kode aktivasi *{$otpCode}* tidak ditemukan atau sudah tidak berlaku.\n\nKetik *otp lapak* kembali untuk mengulangi proses aktivasi.");
            return;
        }

        // Check expiry
        $expiresAt = $listing->meta['lapak_otp_expires_at'] ?? null;
        if ($expiresAt && \Illuminate\Support\Carbon::parse($expiresAt)->isPast()) {
            $this->whatsappService->sendMessage($normalizedFrom, "Maaf, kode aktivasi sudah kadaluwarsa. Silakan buat kode baru di dashboard.");
            return;
        }

        // Security check: MUST match the number provided in lapak creation
        $normalizedListingWa = $this->normalizePhoneNumber($listing->whatsapp);
        $normalizedIncomingWa = $this->normalizePhoneNumber($normalizedFrom);
        $isOwner = ($normalizedListingWa === $normalizedIncomingWa);

        if (!$isOwner) {
            $this->whatsappService->sendMessage($normalizedFrom, "Maaf, nomor WhatsApp ini ({$normalizedFrom}) tidak berwenang mengaktifkan lapak *{$listing->title}*. Gunakan nomor WhatsApp yang terdaftar pada lapak tersebut ({$listing->whatsapp}).");
            return;
        }

        // Activate and clear OTP
        $meta = $listing->meta;
        unset($meta['lapak_otp']);
        unset($meta['lapak_otp_expires_at']);

        $listing->update([
            'is_active' => true,
            'is_draft' => false,
            'lapak_expires_at' => now()->addDays(30), // Default 30 days
            'meta' => $meta
        ]);

        $this->whatsappService->sendMessage($normalizedFrom, "✅ *BERHASIL!* Lapak *{$listing->title}* telah diaktifkan dan sekarang tayang di Sebatam.com.\n\nTerima kasih atas kepercayaan Anda.");
        Log::info('Lapak activated via WA Random OTP', ['listing_id' => $listing->id, 'from' => $normalizedFrom]);
    }

    private function processOtpRequest(string $normalizedFrom): void
    {
        $user = User::query()->where('whatsapp', $normalizedFrom)->first();

        if (! $user) {
            $this->putWaRegistrationState($normalizedFrom, ['step' => 'awaiting_otp_reg_decision']);
            $this->whatsappService->sendMessage($normalizedFrom, "Maaf, nomor WhatsApp Anda tidak terdaftar di sistem kami.\n\nApakah Anda mau mendaftarkan WA Anda? (Balas *YA* atau *TIDAK*)");
            Log::warning('OTP request from unregistered number, offering reg', ['from_suffix' => substr($normalizedFrom, -4)]);

            return;
        }

        if ($user->otp && $user->otp_expires_at && $user->otp_expires_at->isFuture()) {
            $this->whatsappService->sendMessage($normalizedFrom, 'Kode OTP sebelumnya masih berlaku. Tunggu hingga kedaluwarsa untuk meminta yang baru.');

            return;
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->whatsappService->sendMessage($normalizedFrom, "🔐 KODE OTP ANDA: *{$otp}*\n\nKode ini berlaku selama 10 menit. Jangan berikan kode ini kepada siapapun termasuk petugas Sebatam.");
        Log::info('OTP issued', ['user_id' => $user->id]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    /**
     * GOWA sends separate webhook calls for delivery acks (e.g. message.ack). Those payloads are
     * not chat messages; they must not pass the old "str_contains('message')" check or we mis-handle them.
     */
    private function isGowaIncomingChatMessageEvent(string $event): bool
    {
        $event = strtolower(trim($event));
        if ($event === '' || $event === 'unknown') {
            return false;
        }

        if (! str_contains($event, 'message')) {
            return false;
        }

        if (str_ends_with($event, '.ack') || str_contains($event, '.ack.')) {
            return false;
        }

        if (str_contains($event, 'receipt')) {
            return false;
        }

        return true;
    }

    private function summarizePayloadForLog(array $payload): array
    {
        $data = $payload['data'] ?? ($payload['payload'] ?? []);
        $from = is_array($data) ? ($data['from'] ?? '') : '';
        $from = str_replace(['@c.us', '@s.whatsapp.net'], '', (string) $from);
        $from = preg_replace('/\D/', '', $from) ?? '';

        return [
            'event' => $payload['event'] ?? 'unknown',
            'from_suffix' => $from !== '' ? substr($from, -4) : null,
        ];
    }

    /**
     * Multi-step "buat akun" registration via WhatsApp (state in cache).
     *
     * @return bool true if this message was consumed by the registration flow
     */
    private function processWaRegistrationFlow(string $normalizedFrom, string $trimmedBody): bool
    {
        $normalizedCmd = $this->normalizeWaUserInput($trimmedBody);
        $state = $this->getWaRegistrationState($normalizedFrom);

        Log::info("processWaRegistrationFlow: cmd='$normalizedCmd', state=" . json_encode($state));

        if ($normalizedCmd === 'aktivasi') {
            $this->handleAktivasiCommand($normalizedFrom);
            return true;
        }

        if ($normalizedCmd === 'buat akun') {
            if (User::query()->where('whatsapp', $normalizedFrom)->exists()) {
                $this->whatsappService->sendMessage(
                    $normalizedFrom,
                    'Nomor WhatsApp ini sudah terdaftar di Sebatam.com.'
                );

                return true;
            }

            $this->putWaRegistrationState($normalizedFrom, ['step' => 'awaiting_confirm']);
            $this->whatsappService->sendMessage(
                $normalizedFrom,
                "🤖 *Pendaftaran Akun Sebatam.com*\n\nAnda ingin membuat akun baru menggunakan nomor WhatsApp ini?\n\nBalas *YA* untuk lanjut mendaftar atau *TIDAK* untuk membatalkan."
            );

            return true;
        }

        if ($state === null) {
            return false;
        }

        return match ($state['step']) {
            'awaiting_confirm' => $this->waRegistrationHandleConfirm($normalizedFrom, $normalizedCmd, $state),
            'awaiting_otp_reg_decision' => $this->waOtpRegHandleDecision($normalizedFrom, $normalizedCmd),
            'awaiting_name' => $this->waRegistrationHandleName($normalizedFrom, $trimmedBody, $normalizedCmd, $state),
            'awaiting_email' => $this->waRegistrationHandleEmail($normalizedFrom, $trimmedBody),
            'awaiting_lapak_otp' => $this->handleLapakOtpInput($normalizedFrom, $trimmedBody),
            'awaiting_activation_decision' => $this->waAktivasiHandleDecision($normalizedFrom, $normalizedCmd),
            'awaiting_activation_name' => $this->waAktivasiHandleName($normalizedFrom, $trimmedBody),
            'awaiting_activation_code' => $this->waAktivasiHandleCode($normalizedFrom, $trimmedBody),
            default => $this->waRegistrationAbortUnknownStep($normalizedFrom),
        };
    }

    private function normalizeWaUserInput(string $body): string
    {
        $collapsed = trim(preg_replace('/\s+/u', ' ', $body) ?? '');

        return mb_strtolower($collapsed, 'UTF-8');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getWaRegistrationState(string $normalizedFrom): ?array
    {
        if ($this->waRegistrationStateCache !== null) {
            return $this->waRegistrationStateCache;
        }

        /** @var array<string, mixed>|null $got */
        $got = Cache::get($this->waRegistrationCacheKey($normalizedFrom));

        $this->waRegistrationStateCache = is_array($got) ? $got : null;

        return $this->waRegistrationStateCache;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function putWaRegistrationState(string $normalizedFrom, array $state): void
    {
        $this->waRegistrationStateCache = $state;
        Cache::put(
            $this->waRegistrationCacheKey($normalizedFrom),
            $state,
            self::WA_REG_TTL_SECONDS
        );
    }

    private function clearWaRegistrationState(string $normalizedFrom): void
    {
        $this->waRegistrationStateCache = null;
        Cache::forget($this->waRegistrationCacheKey($normalizedFrom));
    }

    private function waRegistrationCacheKey(string $normalizedDigits): string
    {
        return 'wa_reg:'.$normalizedDigits;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function waRegistrationHandleConfirm(string $normalizedFrom, string $normalizedCmd, array $state): bool
    {
        if (in_array($normalizedCmd, ['ya', 'y', 'yes', 'iya', 'ok', 'oke'], true)) {
            $this->putWaRegistrationState($normalizedFrom, ['step' => 'awaiting_name']);
            $this->whatsappService->sendMessage(
                $normalizedFrom,
                "📝 *Langkah 1: Nama Lengkap*\n\nSilakan kirim *Nama Anda* (nama lengkap atau nama panggilan).\n\n_Ketik *batal* jika ingin berhenti._"
            );

            return true;
        }

        if (in_array($normalizedCmd, ['tidak', 'n', 'no', 'gak', 'ngga', 'nggak', 'batal'], true)) {
            $this->clearWaRegistrationState($normalizedFrom);
            $this->whatsappService->sendMessage($normalizedFrom, 'Baik, pembuatan akun dibatalkan. Kirim *buat akun* kapan saja jika Anda ingin mendaftar lagi.');

            return true;
        }

        $this->whatsappService->sendMessage(
            $normalizedFrom,
            'Mohon balas *ya* jika ingin membuat akun di Sebatam.com, atau *tidak* untuk membatalkan.'
        );

        return true;
    }

    private function waOtpRegHandleDecision(string $normalizedFrom, string $normalizedCmd): bool
    {
        if (in_array($normalizedCmd, ['ya', 'y', 'yes', 'iya', 'ok', 'oke'], true)) {
            $this->putWaRegistrationState($normalizedFrom, ['step' => 'awaiting_name', 'for_otp' => true]);
            $this->whatsappService->sendMessage(
                $normalizedFrom,
                "📝 *Langkah 1: Nama Lengkap*\n\nSilakan kirim *Nama Anda* (nama lengkap atau nama panggilan).\n\n_Ketik *batal* jika ingin berhenti._"
            );
            return true;
        }

        if (in_array($normalizedCmd, ['tidak', 'n', 'no', 'gak', 'ngga', 'nggak', 'batal'], true)) {
            $this->clearWaRegistrationState($normalizedFrom);
            $this->whatsappService->sendMessage($normalizedFrom, 'Baik. Terima kasih sudah menghubungi sebatam.com.');
            return true;
        }

        $this->whatsappService->sendMessage(
            $normalizedFrom,
            'Mohon balas *ya* jika ingin mendaftar, atau *tidak* untuk membatalkan.'
        );

        return true;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function waRegistrationHandleName(string $normalizedFrom, string $trimmedBody, string $normalizedCmd, array $state): bool
    {
        if ($normalizedCmd === 'batal' || $normalizedCmd === 'batalkan') {
            $this->clearWaRegistrationState($normalizedFrom);
            $this->whatsappService->sendMessage($normalizedFrom, 'Pembuatan akun dibatalkan. Kirim *buat akun* jika ingin mencoba lagi.');

            return true;
        }

        $name = trim($trimmedBody);
        if ($name === '' || mb_strlen($name) < 2) {
            $this->whatsappService->sendMessage($normalizedFrom, 'Nama terlalu pendek. Mohon kirim nama Anda (minimal 2 karakter) atau *batal*.');

            return true;
        }

        if (mb_strlen($name) > 255) {
            $this->whatsappService->sendMessage($normalizedFrom, 'Nama terlalu panjang. Mohon kirim nama maksimal 255 karakter atau *batal*.');

            return true;
        }

        if (str_contains($name, '@')) {
            $this->whatsappService->sendMessage(
                $normalizedFrom,
                'Sepertinya itu alamat email. Untuk langkah ini mohon kirim *nama Anda* saja. Email akan diminta pada langkah berikutnya.'
            );

            return true;
        }

        $this->putWaRegistrationState($normalizedFrom, [
            'step' => 'awaiting_email',
            'name' => $name,
            'for_otp' => $state['for_otp'] ?? false,
        ]);
        $this->whatsappService->sendMessage($normalizedFrom, "📩 *Langkah 2: Alamat Email*\n\nTerima kasih, *" . $name . "*! Sekarang mohon kirim *alamat email Anda* yang aktif (untuk login di Sebatam.com).\n\n_Contoh: nama@email.com_");

        return true;
    }

    private function waRegistrationHandleEmail(string $normalizedFrom, string $trimmedBody): bool
    {
        $validator = Validator::make(
            ['email' => mb_strtolower(trim($trimmedBody), 'UTF-8')],
            ['email' => ['required', 'email', 'max:255']]
        );

        if ($validator->fails()) {
            $this->whatsappService->sendMessage(
                $normalizedFrom,
                'Format email tidak valid. Mohon kirim alamat email yang benar (contoh: nama@email.com).'
            );

            return true;
        }

        /** @var string $email */
        $email = $validator->validated()['email'];

        if (User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($email, 'UTF-8')])->exists()) {
            $this->whatsappService->sendMessage(
                $normalizedFrom,
                'Email ini sudah digunakan di Sebatam.com. Mohon kirim *email lain* atau hubungi bantuan jika ini akun Anda.'
            );

            return true;
        }

        $state = $this->getWaRegistrationState($normalizedFrom);
        $name = is_array($state) && isset($state['name']) && is_string($state['name']) ? $state['name'] : null;
        $forOtp = is_array($state) && isset($state['for_otp']) ? $state['for_otp'] : false;
        if ($name === null || $name === '') {
            $this->clearWaRegistrationState($normalizedFrom);
            $this->whatsappService->sendMessage($normalizedFrom, 'Sesi pendaftaran tidak lengkap. Silakan kirim *buat akun* untuk memulai lagi.');

            return true;
        }

        $firstDigit = (string) random_int(1, 9);
        $passwordPlain = str_repeat($firstDigit, 4) . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        try {
            $user = DB::transaction(function () use ($name, $email, $normalizedFrom, $passwordPlain) {
                $created = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($passwordPlain),
                    'whatsapp' => $normalizedFrom,
                    'whatsapp_verified_at' => now(),
                    'is_active' => true,
                    'verification_code_hash' => null,
                    'verification_code_expires_at' => null,
                    'wa_verify_failed_attempts' => 0,
                    'role' => User::ROLE_MEMBER,
                ]);

                return $created;
            });
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('WA registration: failed to create user', [
                'from_suffix' => substr($normalizedFrom, -4),
                'message' => $e->getMessage(),
            ]);
            $this->whatsappService->sendMessage(
                $normalizedFrom,
                '❌ Maaf, terjadi kesalahan saat menyimpan akun. Silakan coba lagi nanti atau daftar melalui website Sebatam.com.'
            );

            return true;
        }

        $this->clearWaRegistrationState($normalizedFrom);
        $loginUrl = rtrim(config('app.url', 'http://localhost:8000'), '/') . '/login';

        if ($forOtp) {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->update([
                'otp' => Hash::make($otp),
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            $this->whatsappService->sendMessage(
                $normalizedFrom,
                "🎉 *SELAMAT! Akun Anda Berhasil Dibuat!*\n\nDetail akun Anda:\n📧 Email: *{$email}*\n🔑 Password: *{$passwordPlain}*\n\n🔐 KODE OTP ANDA: *{$otp}*\n\nSilahkan login menggunakan nomor wa Anda ini dan nomor otp yang diberikan. Atau login menggunakan email dan password sementara di atas.\n\nLink Login: {$loginUrl}"
            );
        } else {
            $this->whatsappService->sendMessage(
                $normalizedFrom,
                "🎉 *SELAMAT! Akun Anda Berhasil Dibuat!*\n\nDetail login Anda:\n📧 Email: *{$email}*\n🔑 Password: *{$passwordPlain}*\n\n_Simpan detail login ini. Anda dapat masuk/login di website Sebatam.com dan melengkapi profil Anda._\n\nLink Login: {$loginUrl}\n\nKetik *menu* untuk melihat fitur chatbot lainnya. Selamat bergabung! 🚀"
            );
        }
        Log::info('User registered via WhatsApp flow', ['user_id' => $user->id]);

        return true;
    }

    private function handleLapakOtpInput(string $normalizedFrom, string $input): bool
    {
        $this->processLapakActivation($normalizedFrom, "otp lapak " . $input);
        $this->clearWaRegistrationState($normalizedFrom);
        return true;
    }

    private function handleAktivasiCommand(string $normalizedFrom): void
    {
        Log::info('handleAktivasiCommand started for: ' . $normalizedFrom);
        $user = User::where('whatsapp', $normalizedFrom)->first();

        if (!$user) {
            Log::info('User not found for activation, offering registration');
            $this->putWaRegistrationState($normalizedFrom, ['step' => 'awaiting_activation_decision']);
            $this->whatsappService->sendMessage($normalizedFrom, "Halo! Nomor WhatsApp Anda belum terdaftar di sistem Sebatam.com. Apakah Anda ingin mendaftar akun baru? (Balas *YA* atau *TIDAK*)");
            return;
        }

        Log::info('User found, asking for activation code');
        $this->putWaRegistrationState($normalizedFrom, ['step' => 'awaiting_activation_code', 'user_id' => $user->id]);
        $this->whatsappService->sendMessage($normalizedFrom, "Silakan masukkan *Nomor Kode Listing* (6 digit) yang ingin Anda aktifkan.\n\n_Ketik *batal* jika ingin membatalkan._");
    }

    private function waAktivasiHandleDecision(string $from, string $cmd): bool
    {
        if (in_array($cmd, ['ya', 'y', 'yes', 'iya', 'ok', 'oke'], true)) {
            $this->putWaRegistrationState($from, ['step' => 'awaiting_activation_name']);
            $this->whatsappService->sendMessage($from, "Siapa nama Anda?");
            return true;
        }

        if (in_array($cmd, ['tidak', 'n', 'no', 'gak', 'ngga', 'nggak', 'batal'], true)) {
            $this->putWaRegistrationState($from, ['step' => 'awaiting_activation_code', 'user_id' => null]);
            $this->whatsappService->sendMessage($from, "Baik. Untuk mengaktifkan postingan, silakan masukkan *Nomor Kode Listing* (6 digit) Anda:\n\n_Ketik *batal* jika ingin membatalkan._");
            return true;
        }

        $this->whatsappService->sendMessage($from, "Mohon balas *YA* atau *TIDAK*.");
        return true;
    }

    private function waAktivasiHandleName(string $from, string $name): bool
    {
        $normalizedFrom = $this->normalizePhoneNumber($from);
        $randomSuffix = Str::lower(Str::random(6));
        $email = $normalizedFrom . "." . $randomSuffix . "@sebatam.com";
        $firstDigit = (string) random_int(1, 9);
        $password = str_repeat($firstDigit, 4) . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'whatsapp' => $normalizedFrom,
            'password' => Hash::make($password),
            'is_active' => true,
            'whatsapp_verified_at' => now(),
            'role' => User::ROLE_MEMBER,
        ]);

        $this->putWaRegistrationState($from, ['step' => 'awaiting_activation_code', 'user_id' => $user->id]);
        $this->whatsappService->sendMessage($from, "Akun Anda berhasil dibuat!\n📧 Email: *{$email}*\n🔑 Password: *{$password}*\n\n_Mohon simpan data ini._\n\nSekarang, silakan masukkan *Nomor Kode Listing* (6 digit) Anda:\n\n_Ketik *batal* jika ingin membatalkan._");
        
        return true;
    }

    private function waAktivasiHandleCode(string $from, string $code): bool
    {
        $code = trim($code);

        // Cek apakah user ingin membatalkan
        if (in_array(mb_strtolower($code, 'UTF-8'), ['batal', 'batalkan', 'cancel'], true)) {
            $this->clearWaRegistrationState($from);
            $this->whatsappService->sendMessage($from, "Baik, proses aktivasi dibatalkan. Ketik *aktivasi* kapan saja jika ingin mencoba lagi.");
            return true;
        }

        $listing = \App\Models\Listing::where('code', $code)
            ->where('is_active', false)
            ->first();

        if (!$listing) {
            $this->whatsappService->sendMessage($from, "❌ Nomor kode listing salah atau sudah tidak berlaku. Pastikan Anda memasukkan 6 digit angka yang benar.");
            return true;
        }

        $state = $this->getWaRegistrationState($from);
        $userId = $state['user_id'] ?? null;

        $listing->update([
            'is_active' => true,
            'user_id' => $userId ?: $listing->user_id, // Link to user if just created/logged in
            'code' => null, // Clear code after activation
        ]);

        $this->clearWaRegistrationState($from);
        
        $baseUrl = config('app.url', 'http://localhost:8000');
        $path = ($listing->type === 'usaha') ? '/direktori/' : '/iklan/';
        $link = rtrim($baseUrl, '/') . $path . $listing->slug;

        $this->whatsappService->sendMessage($from, "✅ *BERHASIL!* Postingan *{$listing->title}* telah diaktifkan dan sekarang tayang di Sebatam.com.\n\nLihat postingan Anda di sini:\n{$link}\n\nTerima kasih telah bergabung! Ketik *menu* untuk fitur lainnya.");

        return true;
    }

    private function waRegistrationAbortUnknownStep(string $normalizedFrom): bool
    {
        $this->clearWaRegistrationState($normalizedFrom);
        $this->whatsappService->sendMessage($normalizedFrom, 'Sesi pendaftaran tidak dikenali. Kirim *buat akun* untuk memulai lagi.');

        return true;
    }

    /**
     * Some gateways send boolean-like values as strings (e.g. "false").
     * In PHP, non-empty strings are truthy, so we must normalize safely.
     */
    private function normalizeBoolish(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return $parsed ?? false;
        }

        return false;
    }

    /**
     * Aggressive normalization to match international style (e.g. 0812 -> 62812)
     */
    private function normalizePhoneNumber(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number) ?? '';
        
        // If starts with 0, replace with 62
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        
        return $digits;
    }
}
