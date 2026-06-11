<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryApiController extends Controller
{
    /**
     * Mengambil daftar subkategori berdasarkan nama induk kategori
     */
    public function getSubcategories(Request $request)
    {
        $parentName = $request->query('parent');

        if (!$parentName) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter parent (nama induk kategori) wajib disertakan'
            ], 400);
        }

        $parentName = trim($parentName);

        // Cari kategori induk berdasarkan nama
        $parentCategory = Category::whereRaw('LOWER(name) = ?', [strtolower($parentName)])
                                  ->whereNull('parent_id')
                                  ->first();

        // Jika tidak ditemukan secara persis, coba pencarian LIKE
        if (!$parentCategory) {
            $parentCategory = Category::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($parentName) . '%'])
                                      ->whereNull('parent_id')
                                      ->first();
        }

        if (!$parentCategory) {
            return response()->json([
                'success' => false,
                'message' => "Induk kategori '{$parentName}' tidak ditemukan",
                'subcategories' => []
            ], 404);
        }

        // Ambil nama-nama subkategorinya (hanya yang sudah diapprove)
        $subcategories = $parentCategory->children()
            ->whereRaw('is_approved = true')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        return response()->json([
            'success' => true,
            'parent_category' => $parentCategory->name,
            'parent_category_id' => $parentCategory->id,
            'subcategories' => $subcategories
        ]);
    }
}
