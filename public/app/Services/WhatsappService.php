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

        try {
            Log::info("WhatsApp GOWA: attempting to send to $cleanPhone: " . mb_substr($message, 0, 100));
            // GOWA v8+ requires device_id as query parameter
            $sendUrl = rtrim($this->apiUrl, '/') . '/send/message';
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
                'status'  => $response->status(),
                'body'    => $response->body(),
                'to'      => $cleanPhone,
                'url'     => $this->apiUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp GOWA sendMessage exception', [
                'error' => $e->getMessage(),
                'to'    => $cleanPhone,
            ]);
        }

        return null;
    }

    public function adminNumber(): string
    {
        return (string) config('services.whatsapp.admin_number', '');
    }
}
