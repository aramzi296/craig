<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateListingsFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listings = \App\Models\Listing::all();
        
        $pool = [
            'Properti' => ['Lokasi Strategis', 'Bebas Banjir', 'Sertifikat Lengkap', 'Dekat Mall', 'Keamanan 24 Jam'],
            'Kendaraan' => ['Mesin Sehat', 'Pajak Aktif', 'Interior Rapi', 'Servis Teratur', 'Ban Baru'],
            'Job' => ['Gaji Kompetitif', 'Bonus Menarik', 'Lingkungan Nyaman', 'Jenjang Karir', 'Asuransi Kesehatan'],
            'Elektronik' => ['Garansi Aktif', 'Mulus 99%', 'Baterai Awet', 'Lengkap Dus', 'Free Ongkir'],
            'Jasa' => ['Harga Terjangkau', 'Pengerjaan Cepat', 'Berpengalaman', 'Hasil Memuaskan', 'Konsultasi Gratis'],
            'Default' => ['Kondisi Terawat', 'Harga Nego', 'Siap Pakai', 'Nego Sampai Jadi', 'Bisa COD']
        ];

        foreach ($listings as $listing) {
            $catName = $listing->category->name;
            $features = [];
            
            if (isset($pool[$catName])) {
                $features = fake()->randomElements($pool[$catName], 2);
            } else {
                $features = fake()->randomElements($pool['Default'], 2);
            }
            
            $listing->features = $features;
            $listing->save();
        }
    }
}
