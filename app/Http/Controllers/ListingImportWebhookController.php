<?php

namespace App\Http\Controllers;

use App\Services\ListingImportWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class ListingImportWebhookController extends Controller
{
    public function __construct(
        protected ListingImportWebhookService $importService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        if ($secret = config('services.webhook_import.secret')) {
            $token = $request->bearerToken() ?? $request->header('X-Webhook-Token');
            if ($token !== $secret) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }
        }

        $payload = $request->json()->all();

        if (!is_array($payload) || $payload === []) {
            return response()->json([
                'success' => false,
                'message' => 'Body harus berupa JSON array.',
            ], 422);
        }

        try {
            $result = $this->importService->process($payload);

            Log::info('Listing import webhook: success', $result);

            return response()->json([
                'success' => true,
                'message' => 'Listing berhasil dibuat.',
                'data' => $result,
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Listing import webhook: failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses webhook.',
            ], 500);
        }
    }
}
