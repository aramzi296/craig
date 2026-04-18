<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Subdistrict;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DistrictSubdistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Import Districts
        $districtsJson = File::get(database_path('districts.json'));
        $districtsData = json_decode($districtsJson, true);

        foreach ($districtsData['rows'] as $row) {
            District::updateOrCreate(
                ['id' => $row['id']],
                [
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );
        }

        // Import Subdistricts
        $subdistrictsJson = File::get(database_path('subdistricts.json'));
        $subdistrictsData = json_decode($subdistrictsJson, true);

        foreach ($subdistrictsData['rows'] as $row) {
            Subdistrict::updateOrCreate(
                ['id' => $row['id']],
                [
                    'district_id' => $row['district_id'],
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );
        }
    }
}
