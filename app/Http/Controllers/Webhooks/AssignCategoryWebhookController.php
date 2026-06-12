<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Listing;
use App\Models\Category;

/**
 * Controller ini digunakan untuk menangani webhook (umumnya dari n8n atau bot)
 * yang bertugas menugaskan/mengaitkan kategori secara otomatis pada sebuah listing.
 * Menerima request berisi ID listing dan nama subkategori, kemudian mencari kategori
 * yang relevan di database untuk disematkan pada listing tersebut.
 */
class AssignCategoryWebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $listingId = $request->input('listing_id') ?? $request->input('id');
            $kategoriName = $request->input('kategori') ?? $request->input('nama_kategori');
            $subkategoriName = $request->input('subkategori') ?? $request->input('nama_subkategori');

            if (!$listingId || !$subkategoriName || !$kategoriName) {
                return response()->json(['success' => false, 'message' => 'Parameter listing_id (atau id), kategori, dan subkategori wajib diisi'], 400);
            }

            $listing = Listing::find($listingId);
            if (!$listing) {
                return response()->json(['success' => false, 'message' => 'Listing tidak ditemukan'], 404);
            }

            $kategoriName = trim($kategoriName);
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
                // Cari parent kategori terlebih dahulu
                $parentCategory = Category::whereRaw('LOWER(name) = ?', [strtolower($kategoriName)])->first();
                
                if (!$parentCategory) {
                    $parentCategory = Category::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($kategoriName) . '%'])->first();
                }

                // Jika parent juga belum ada, buat parent kategori baru
                if (!$parentCategory) {
                    // Generate base slug
                    $baseSlug = \Illuminate\Support\Str::slug($kategoriName);
                    $slug = $baseSlug;
                    $counter = 1;
                    // Pastikan slug unik
                    while (Category::where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }

                    $parentCategory = Category::create([
                        'name' => $kategoriName,
                        'slug' => $slug,
                        'is_approved' => true,
                        'parent_id' => null,
                    ]);
                }

                // Generate base slug untuk subkategori
                $baseSubSlug = \Illuminate\Support\Str::slug($subkategoriName);
                $subSlug = $baseSubSlug;
                $counter = 1;
                // Pastikan slug unik
                while (Category::where('slug', $subSlug)->exists()) {
                    $subSlug = $baseSubSlug . '-' . $counter;
                    $counter++;
                }

                // Buat subkategori dengan parent_id yang sesuai
                $category = Category::create([
                    'name' => $subkategoriName,
                    'slug' => $subSlug,
                    'parent_id' => $parentCategory->id,
                    'is_approved' => true,
                ]);
            }

            // Memasukkan relasi ke tabel pivot category_listing
            $listing->categories()->sync([$category->id]);

            // Memastikan data diperbarui untuk pencarian (Scout/Meilisearch)
            if (method_exists($listing, 'updateSearchableField')) {
                $listing->updateSearchableField();
            }
            
            // Simpan model jika diperlukan untuk memicu event
            $listing->touch();

            // Menghapus cache direktori kategori agar count terupdate di halaman depan
            \Illuminate\Support\Facades\Cache::store('redis')->forget('categories:directory_with_counts');

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
