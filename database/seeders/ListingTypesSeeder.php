<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ListingTypesSeeder extends Seeder
{
    public function run()
    {
        $json = File::get(database_path('tipe_listing.json'));
        $data = json_decode($json, true);

        foreach ($data['rows'] as $row) {
            DB::table('listing_types')->updateOrInsert(
                ['id' => $row['id']],
                [
                    'name'       => $row['name'],
                    'slug'       => $row['slug'],
                    'color'      => $row['color'],
                    'sort_order' => $row['sort_order'],
                    'keterangan' => $row['keterangan'] ?? null,
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );
        }
    }
}
