<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListingTypesSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'jual', 'color' => '#f87171'],
            ['name' => 'beli', 'color' => '#fbbf24'],
            ['name' => 'jasa', 'color' => '#34d399'],
            ['name' => 'lowongan', 'color' => '#60a5fa'],
            ['name' => 'cari kerja', 'color' => '#a78bfa'],
            ['name' => 'agenda', 'color' => '#f472b6'],
            ['name' => 'pengumuman', 'color' => '#fb7185'],
            ['name' => 'promo', 'color' => '#22d3ee'],
            ['name' => 'lainnya', 'color' => '#9ca3af'],
        ];

        foreach ($types as $type) {
            DB::table('listing_types')->updateOrInsert(
                ['slug' => Str::slug($type['name'])],
                ['name' => $type['name'], 'color' => $type['color'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
