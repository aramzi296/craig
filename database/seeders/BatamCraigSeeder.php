<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BatamCraigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Users
        $user = \App\Models\User::factory()->create([
            'name' => 'Batam Seller',
            'email' => 'seller@batamcraig.com',
            'password' => bcrypt('password'),
        ]);

        // Categories
        $categories = [
            ['name' => 'Jual Beli', 'slug' => 'jual-beli', 'icon' => 'shopping-bag'],
            ['name' => 'Lowongan Kerja', 'slug' => 'lowongan-kerja', 'icon' => 'briefcase'],
            ['name' => 'Properti', 'slug' => 'properti', 'icon' => 'home'],
            ['name' => 'Jasa', 'slug' => 'jasa', 'icon' => 'tool'],
            ['name' => 'Komunitas', 'slug' => 'komunitas', 'icon' => 'users'],
            ['name' => 'Kendaraan', 'slug' => 'kendaraan', 'icon' => 'truck'],
        ];

        foreach ($categories as $cat) {
            $category = \App\Models\Category::create($cat);

            // Mock Listings for each category
            for ($i = 1; $i <= 3; $i++) {
                \App\Models\Listing::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'title' => $category->name . ' Item ' . $i . ' di Batam',
                    'slug' => \Illuminate\Support\Str::slug($category->name . ' Item ' . $i . ' di Batam ' . uniqid()),
                    'description' => 'Ini adalah deskripsi untuk listing ' . $category->name . '. Barang/Jasa ini tersedia di area Batam dengan kondisi terbaik.',
                    'price' => rand(100000, 5000000),
                    'location' => ['Batam Centre', 'Nagoya', 'Sekupang', 'Bengkong'][rand(0, 3)],
                    'is_featured' => \DB::raw($i == 1 ? 'TRUE' : 'FALSE'),
                ]);
            }
        }
    }
}
