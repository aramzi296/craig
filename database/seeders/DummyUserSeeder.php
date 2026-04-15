<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 12 Dummy Users
        $users = \App\Models\User::factory(12)->create();
        
        // Get all listings
        $listings = \App\Models\Listing::all();
        
        foreach ($listings as $listing) {
            // Assign a random user from the newly created batch
            $randomUser = $users->random();
            $listing->user_id = $randomUser->id;
            $listing->save();
        }
    }
}
