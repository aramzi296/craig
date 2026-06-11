<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Listing;
use App\Models\Category;

class AssignCategoryWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $listingId = $request->input('listing_id') ?? $request->input('id');
            $subkategoriName = $request->input('subkategori') ?? $request->input('nama_subkategori');

            if (!$listingId || !$subkategoriName) {
                return response()->json(['success' => false, 'message' => 'Parameter listing_id (atau id) dan subkategori wajib diisi'], 400);
            }

            $listing = Listing::find($listingId);
            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Listing tidak ditemukan'], 404);
            }

            $subkategoriName = trim($subkategoriName);

            // Cari kategori berdasarkan nama persis case insensitive
            $category = Category::whereRaw('LOWER(name) = ?', [strtolower($subkategoriName)])
                                ->first();

            // Fallback: pencarian LIKE jika tidak ketemu yang persis
            if (!$category) {
                $category = Category::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($subkategoriName) . '%'])
                                    ->first();
            }

            if (!$category) {
                return response()->json(['success' => false, 'message' => "Kategori atau subkategori '{$subkategoriName}' tidak ditemukan"], 404);
            }

            // Memasukkan relasi ke tabel pivot category_listing
            $listing->categories()->sync([$category->id]);

            // Memastikan data diperbarui untuk pencarian (Scout/Meilisearch)
            if (method_exists($listing, 'updateSearchableField')) {
                $listing->updateSearchableField();
            }
            
            // Simpan model jika diperlukan untuk memicu event
            $listing->touch();

            // Menghapus ID dari antrean proses n8n di cache
            $processingIds = \Illuminate\Support\Facades\Cache::get('listings_category_processing', []);
            if (!empty($processingIds)) {
                $processingIds = array_diff($processingIds, [$listing->id]);
                \Illuminate\Support\Facades\Cache::put('listings_category_processing', array_values($processingIds), now()->addMinutes(10));
            }

            return response()->json([
                'success' => true,
                'message' => 'Kategori berhasil diset untuk listing ini',
                'listing_id' => $listing->id,
                'category_id' => $category->id,
                'category_name' => $category->name
            ]);

        } catch (\Exception $e) {
            Log::error('AssignCategoryWebhook Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
