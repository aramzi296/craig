<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Elektronik',
            'Fashion',
            'Kuliner',
            'Perawatan Kecantikan',
            'Kesehatan',
            'Peralatan Rumah Tangga',
            'Mobil',
            'Motor',
            'Konstruksi & Bangunan',
            'Jasa Kebersihan',
            'Teknologi & Gadget',
            'Olahraga & Fitness',
            'Mainan & Hobi',
            'Perlengkapan Bayi & Anak',
            'Pariwisata & Hotel',
            'Pertanian',
            'Perikanan',
            'Furniture',
            'Jasa Pendidikan',
            'Jasa Keuangan',
            'Transportasi & Logistik',
            'Percetakan',
            'Event & Hiburan',
            'Perawatan Hewan',
            'Jasa Fotografi',
            'Elektronik Mobil & Motor',
            'Laundry & Dry Cleaning',
            'Toko Buku & Alat Tulis',
            'Kamera & Aksesoris',
            'Jasa Konsultasi',
            'Properti',
            'Tukang & Reparasi'
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => Str::slug($category)],
                ['name' => $category, 'icon' => null, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
