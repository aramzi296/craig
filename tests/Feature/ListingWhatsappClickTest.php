<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingWhatsappClick;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingWhatsappClickTest extends TestCase
{
    use RefreshDatabase;

    private $owner;
    private $listing;

    protected function setUp(): void
    {
        parent::setUp();

        // Create owner user
        $this->owner = User::create([
            'name' => 'Owner Jasa',
            'whatsapp' => '6281234567890',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create listing
        $this->listing = Listing::create([
            'user_id' => $this->owner->id,
            'title' => 'Jasa Tukang Bangunan Renovasi Rumah Batam',
            'slug' => 'jasa-tukang-bangunan-renovasi-rumah-batam',
            'description' => 'Jasa renovasi berkualitas.',
            'price' => 1500000,
            'is_active' => true,
        ]);
        $this->listing->updateSearchableField();
    }

    /** @test */
    public function guest_clicking_whatsapp_button_is_logged_and_redirected()
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->get(route('listings.whatsapp', $this->listing->id));

        // Assert redirect to WhatsApp
        $expectedText = "Halo Owner Jasa, saya tertarik dengan usaha Anda di " . config('app.name') . ": Jasa Tukang Bangunan Renovasi Rumah Batam.";
        $expectedUrl = "https://wa.me/6281234567890?text=" . urlencode($expectedText);
        $response->assertRedirect($expectedUrl);

        // Assert click is logged in DB
        $this->assertDatabaseHas('listing_whatsapp_clicks', [
            'listing_id' => $this->listing->id,
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        $this->assertCount(1, ListingWhatsappClick::all());
    }

    /** @test */
    public function authenticated_user_clicking_whatsapp_button_is_logged_with_user_id()
    {
        $visitor = User::create([
            'name' => 'Visitor User',
            'whatsapp' => '6289999999999',
            'email' => 'visitor@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($visitor)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)',
            ])
            ->get(route('listings.whatsapp', $this->listing->id));

        // Assert redirect to WhatsApp
        $expectedText = "Halo Owner Jasa, saya tertarik dengan usaha Anda di " . config('app.name') . ": Jasa Tukang Bangunan Renovasi Rumah Batam.";
        $expectedUrl = "https://wa.me/6281234567890?text=" . urlencode($expectedText);
        $response->assertRedirect($expectedUrl);

        // Assert click is logged in DB with user_id
        $this->assertDatabaseHas('listing_whatsapp_clicks', [
            'listing_id' => $this->listing->id,
            'user_id' => $visitor->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)',
        ]);

        $this->assertCount(1, ListingWhatsappClick::all());
    }
}
