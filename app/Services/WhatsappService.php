<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $apiUrl;
    protected string $deviceId;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        $this->apiUrl   = rtrim((string) config('services.whatsapp.api_url', ''), '/');
        $this->deviceId = (string) config('services.whatsapp.device_id', 'default');
        $this->username = (string) config('services.whatsapp.api_user', 'admin');
        $this->password = (string) config('services.whatsapp.api_pass', '');
    }

    /**
     * Send a plain-text WhatsApp message via GOWA (go-whatsapp-web-multidevice).
     */
    public function sendMessage(string $to, string $message): ?array
    {
        // 1. Clean number (only digits)
        $cleanPhone = preg_replace('/\D/', '', $to);

        // 2. Fix Indonesian format: 0811 -> 62811
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        // 3. If starts with 8 (no country code), prepend 62
        if (str_starts_with($cleanPhone, '8')) {
            $cleanPhone = '62' . $cleanPhone;
        }

        if ($this->apiUrl === '') {
            Log::warning('WhatsappService: WHATSAPP_API_URL not configured, message not sent.', [
                'to'      => $cleanPhone,
                'preview' => mb_substr($message, 0, 80),
            ]);
            return null;
        }

        try {
            Log::info("WhatsApp GOWA: attempting to send to $cleanPhone: " . mb_substr($message, 0, 100));
            $sendUrl  = $this->apiUrl . '/send/message';
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(10)
                ->withQueryParameters(['device_id' => $this->deviceId])
                ->post($sendUrl, [
                    'phone'   => $cleanPhone,
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp GOWA: success to $cleanPhone");
                return $response->json();
            }

            Log::warning('WhatsApp GOWA sendMessage failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'to'     => $cleanPhone,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp GOWA sendMessage exception', [
                'error' => $e->getMessage(),
                'to'    => $cleanPhone,
            ]);
        }

        return null;
    }

    /**
     * Send an image via GOWA.
     */
    public function sendImage(string $to, string $imageUrl, string $caption = ''): ?array
    {
        $cleanPhone = self::normalizeNumber($to);

        if ($this->apiUrl === '') {
            Log::warning('WhatsappService: WHATSAPP_API_URL not configured, image not sent.', [
                'to' => $cleanPhone,
            ]);
            return null;
        }

        try {
            Log::info("WhatsApp GOWA: attempting to send image to $cleanPhone: $imageUrl");
            $sendUrl = $this->apiUrl . '/send/image';
            
            $request = Http::withBasicAuth($this->username, $this->password)
                ->timeout(30)
                ->withQueryParameters(['device_id' => $this->deviceId]);

            // If the URL is localhost or 127.0.0.1, GOWA might not be able to reach it.
            // In such cases, we should try to upload the local file directly.
            $isLocal = str_contains($imageUrl, 'localhost') || str_contains($imageUrl, '127.0.0.1');
            $localPath = null;

            if ($isLocal) {
                $parsed = parse_url($imageUrl);
                $path = $parsed['path'] ?? '';
                // Try to find the file in the public directory
                $possiblePath = public_path(ltrim($path, '/'));
                if (file_exists($possiblePath)) {
                    $localPath = $possiblePath;
                }
            }

            if ($localPath) {
                // Send as multipart file upload
                $response = $request->asMultipart()
                    ->attach('image', file_get_contents($localPath), basename($localPath))
                    ->post($sendUrl, [
                        'phone'   => $cleanPhone,
                        'caption' => $caption,
                    ]);
            } else {
                // Send as URL
                $response = $request->post($sendUrl, [
                    'phone'     => $cleanPhone,
                    'image_url' => $imageUrl, // GOWA uses 'image_url' for remote URLs
                    'caption'   => $caption,
                ]);
            }

            if ($response->successful()) {
                Log::info("WhatsApp GOWA: success sending image to $cleanPhone");
                return $response->json();
            }

            Log::warning('WhatsApp GOWA sendImage failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'to'     => $cleanPhone,
                'url'    => $imageUrl
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp GOWA sendImage exception', [
                'error' => $e->getMessage(),
                'to'    => $cleanPhone,
            ]);
        }

        return null;
    }

    /**
     * Normalize a WhatsApp number to international digits only.
     */
    public static function normalizeNumber(string $input): string
    {
        $digits = preg_replace('/\D/', '', $input) ?? '';

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }
}
