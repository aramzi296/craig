<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MigrateWebsiteToMetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listings = \Illuminate\Support\Facades\DB::table('listings')->whereNotNull('website')->get();

        foreach ($listings as $listing) {
            $meta = $listing->meta ? json_decode($listing->meta, true) : [];
            $meta['facebook'] = $listing->website;

            \Illuminate\Support\Facades\DB::table('listings')->where('id', $listing->id)->update([
                'meta' => json_encode($meta)
            ]);
        }
    }
}
