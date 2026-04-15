<?php

namespace App\Http\Controllers;

use App\Models\WaMessage;
use App\Services\WhatsappBotService;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected WhatsappService    $whatsappService,
        protected WhatsappBotService $whatsappBotService
    ) {}

    /**
     * Webhook handler for GOWA (go-whatsapp-web-multidevice).
     * Register this route without CSRF protection (see bootstrap/app.php or middleware).
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $event   = $payload['event'] ?? 'unknown';

        Log::info('WA Webhook: event received', [
            'event'    => $event,
            'from_sfx' => $this->fromSuffix($payload),
        ]);

        if (!$this->isIncomingChatEvent((string) $event)) {
            return response()->json(['status' => 'ignored']);
        }

        $data = $payload['data'] ?? ($payload['payload'] ?? null);
        if (!$data) {
            return response()->json(['status' => 'no_data']);
        }

        // Normalize sender number
        $from = $data['from'] ?? '';
        $from = str_replace(['@c.us', '@s.whatsapp.net'], '', $from);
        $from = preg_replace('/\D/', '', $from) ?? '';

        // Ignore messages sent by the bot itself
        $isFromMe = $this->normalizeBool($data['is_from_me'] ?? ($data['from_me'] ?? ($data['fromMe'] ?? false)));
        if ($isFromMe) {
            return response()->json(['status' => 'ignored_self']);
        }

        $to   = preg_replace('/\D/', '', str_replace(['@c.us', '@s.whatsapp.net'], '', ($data['to'] ?? ''))) ?? '';
        $body = $data['message'] ?? ($data['body'] ?? ($data['content'] ?? ''));

        if ($from === '' || $body === '') {
            return response()->json(['status' => 'incomplete']);
        }

        // Persist raw message for audit
        try {
            WaMessage::create([
                'from_number' => $from,
                'to_number'   => $to,
                'message'     => mb_substr((string) $body, 0, 2000),
                'raw_json'    => [
                    'event'    => $event,
                    'from_sfx' => substr($from, -4),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('WA Webhook: failed to save message – ' . $e->getMessage());
        }

        // Delegate to bot service
        $trimmed = trim((string) $body);
        $this->whatsappBotService->handle($from, $trimmed);

        return response()->json(['status' => 'success']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function isIncomingChatEvent(string $event): bool
    {
        $event = strtolower(trim($event));

        if ($event === '' || $event === 'unknown') {
            return false;
        }

        if (!str_contains($event, 'message')) {
            return false;
        }

        // Exclude delivery/read acks
        if (str_ends_with($event, '.ack') || str_contains($event, '.ack.') || str_contains($event, 'receipt')) {
            return false;
        }

        return true;
    }

    private function normalizeBool(mixed $value): bool
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

    private function fromSuffix(array $payload): ?string
    {
        $data = $payload['data'] ?? ($payload['payload'] ?? []);
        $from = is_array($data) ? ($data['from'] ?? '') : '';
        $from = preg_replace('/\D/', '', str_replace(['@c.us', '@s.whatsapp.net'], '', (string) $from)) ?? '';
        return $from !== '' ? substr($from, -4) : null;
    }
}
