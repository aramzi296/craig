<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;

use App\Models\Listing;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GenerateTagsWebhookController extends Controller
{
    /**
     * Webhook untuk menyimpan/memperbarui tagar dari n8n ke listing yang diberikan.
     */
    public function handle(Request $request): JsonResponse
    {
        // Validasi input: mendukung listing_id atau id
        $request->validate([
            'id' => 'required_without:listing_id|exists:listings,id',
            'listing_id' => 'required_without:id|exists:listings,id',
            'tags' => 'required|string',
        ]);

        $listingId = $request->input('listing_id') ?? $request->input('id');
        $rawTags = $request->input('tags');

        try {
            $listing = Listing::findOrFail($listingId);

            // Pisahkan tag berdasarkan tanda koma dan bersihkan whitespace
            $tagNames = array_filter(array_map('trim', explode(',', $rawTags)));
            $tagIds = [];

            foreach ($tagNames as $name) {
                if (empty($name)) {
                    continue;
                }

                $slug = Str::slug($name);

                $tag = Tag::findOrCreateByName($name, true);

                $tagIds[] = $tag->id;
            }

            // Hubungkan tag ke listing.
            // sync() secara otomatis akan menghapus tag lama yang sudah ada di listing tersebut
            // dan menggantinya dengan tagIds yang baru!
            $listing->tags()->sync($tagIds);

            // Update searchable field listings agar hasil pencarian terbaru sinkron
            $listing->updateSearchableField();

            return response()->json([
                'success' => true,
                'message' => 'Tagar berhasil diperbarui untuk listing ini.',
                'listing_id' => $listing->id,
                'tags' => $tagNames,
            ]);

        } catch (Throwable $e) {
            Log::error('Webhook Generate Tags Error: ' . $e->getMessage(), [
                'listing_id' => $listingId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses tagar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
