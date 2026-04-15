<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies incoming webhooks from go-whatsapp-web-multidevice (GOWA).
 *
 * @see https://raw.githubusercontent.com/aldinokemal/go-whatsapp-web-multidevice/main/src/infrastructure/whatsapp/webhook.go
 * @see https://raw.githubusercontent.com/aldinokemal/go-whatsapp-web-multidevice/main/docs/webhook-payload.md
 */
class VerifyWhatsappWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $mustVerify = (bool) config('services.whatsapp.webhook_verify_signature', true);

        if (! $mustVerify) {
            Log::critical('WhatsApp webhook: WHATSAPP_WEBHOOK_VERIFY_SIGNATURE=false — all secret/signature checks skipped (disable after debugging)');

            return $next($request);
        }

        $secret = $this->normalizeSecret(config('services.whatsapp.webhook_secret'));

        if ($secret === '') {
            if (app()->environment('production')) {
                Log::critical('WhatsApp webhook rejected: WHATSAPP_WEBHOOK_SECRET missing in production');

                return response()->json(['message' => 'Webhook not configured'], 503);
            }

            Log::warning('WhatsApp webhook: WHATSAPP_WEBHOOK_SECRET empty; allowing request (non-production only)');

            return $next($request);
        }

        // Plain shared secret (used by some proxies / custom setups; reference: public/app WhatsAppWebhookController).
        $authHeader = $request->header('Authorization');
        $bearer = is_string($authHeader) && str_starts_with($authHeader, 'Bearer ')
            ? trim(substr($authHeader, 7))
            : null;

        $providedPlain = $request->header('X-WhatsApp-Webhook-Secret')
            ?? $request->header('X-Webhook-Secret')
            ?? $request->header('Webhook-Secret')
            ?? $request->header('X-Gowa-Webhook-Secret')
            ?? $bearer
            ?? $request->input('webhook_secret')
            ?? '';

        if (is_string($providedPlain) && $providedPlain !== '' && hash_equals($secret, $providedPlain)) {
            return $next($request);
        }

        // GOWA: HMAC-SHA256 over the exact raw POST body bytes (see webhook.go + docs/webhook-payload.md).
        if ($this->hubSignature256Valid($request, $secret)) {
            return $next($request);
        }

        $rawLen = strlen($request->getContent());
        $sigHeader = $this->getHeaderSignature256($request);
        Log::warning('WhatsApp webhook: unauthorized', [
            'raw_body_length' => $rawLen,
            'has_x_hub_signature_256' => $sigHeader !== null && $sigHeader !== '',
            'sig_prefix' => $sigHeader !== null && $sigHeader !== '' ? substr($sigHeader, 0, 12) : null,
        ]);

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    private function normalizeSecret(mixed $secret): string
    {
        if (! is_string($secret) || $secret === '') {
            return '';
        }

        $secret = trim($secret);
        if (str_starts_with($secret, "\xEF\xBB\xBF")) {
            $secret = substr($secret, 3);
        }

        return $secret;
    }

    private function getHeaderSignature256(Request $request): ?string
    {
        foreach (['X-Hub-Signature-256', 'X-Hub-Signature', 'X-Signature'] as $name) {
            $v = $request->header($name);
            if (is_string($v) && $v !== '') {
                return $v;
            }
        }

        return null;
    }

    /**
     * Matches Go: utils.GetMessageDigestOrSignature(postBody, secretKey) + header `sha256=<hex>`.
     */
    private function hubSignature256Valid(Request $request, string $secret): bool
    {
        $header = $this->getHeaderSignature256($request);
        if ($header === null || $header === '') {
            return false;
        }

        $rawBody = $request->getContent();
        $expectedHex = strtolower(hash_hmac('sha256', $rawBody, $secret));

        $candidate = trim($header);
        if (str_starts_with(strtolower($candidate), 'sha256=')) {
            $candidate = trim(substr($candidate, 7));
        }
        $candidate = strtolower($candidate);

        if ($candidate !== '' && hash_equals($expectedHex, $candidate)) {
            return true;
        }

        // If raw body was empty (some proxies), try re-encoding parsed JSON (best-effort; may still match GOWA).
        if ($rawBody === '' && $request->isJson()) {
            $encoded = json_encode($request->json()->all(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (is_string($encoded) && $encoded !== '') {
                $expectedHexRetry = strtolower(hash_hmac('sha256', $encoded, $secret));
                if ($candidate !== '' && hash_equals($expectedHexRetry, $candidate)) {
                    Log::notice('WhatsApp webhook: HMAC matched using re-encoded JSON body (raw body was empty)');

                    return true;
                }
            }
        }

        return false;
    }
}
