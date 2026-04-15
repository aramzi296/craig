<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Listing;
use App\Models\Category;

class ListingCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listings = Listing::all();
        $categories = Category::all();

        foreach ($listings as $listing) {
            // Assign 1 to 3 random categories
            $randomCategories = $categories->random(rand(1, 3))->pluck('id');
            $listing->categories()->sync($randomCategories);
        }
    }
}
