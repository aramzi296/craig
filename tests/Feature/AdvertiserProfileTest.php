<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Listing;

class AdvertiserProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function advertiser_profile_page_loads_correctly_with_listings_and_card()
    {
        // 1. Create the advertiser user
        $advertiser = User::create([
            'name' => 'Budi Setiawan',
            'whatsapp' => '6281234567890',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Create another user to make sure their listings do NOT show up
        $otherUser = User::create([
            'name' => 'Other User',
            'whatsapp' => '6289876543210',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        // 3. Create listings for both users
        $listing1 = Listing::create([
            'user_id' => $advertiser->id,
            'title' => 'Jasa Cuci AC Budi',
            'slug' => 'jasa-cuci-ac-budi',
            'description' => 'Servis AC cepat dan murah di Batam.',
            'price' => 75000,
            'is_active' => true,
        ]);

        $listing2 = Listing::create([
            'user_id' => $advertiser->id,
            'title' => 'Sewa Mobil Batam Murah',
            'slug' => 'sewa-mobil-batam-murah',
            'description' => 'Rental mobil harian terpercaya.',
            'price' => 250000,
            'is_active' => true,
        ]);

        $otherListing = Listing::create([
            'user_id' => $otherUser->id,
            'title' => 'Kopi Shop Enak Sekali',
            'slug' => 'kopi-shop-enak-sekali',
            'description' => 'Tempat nongkrong asik.',
            'price' => 15000,
            'is_active' => true,
        ]);

        // 4. Access the advertiser profile route /pengiklan/{id}
        $response = $this->get(route('user.listings', $advertiser->id));

        // 5. Assertions
        $response->assertStatus(200);
        $response->assertViewIs('home');

        // Check that the Advertiser's Card is rendered with correct details
        $response->assertSee('Budi Setiawan');
        $response->assertSee('Verified Partner');
        $response->assertSee('+6281234567890');
        $response->assertSee('2 Iklan Aktif');

        // Check that the advertiser's listings are visible
        $response->assertSee('Jasa Cuci AC Budi');
        $response->assertSee('Sewa Mobil Batam Murah');

        // Check that other user's listings are NOT visible
        $response->assertDontSee('Kopi Shop Enak Sekali');
    }
}
