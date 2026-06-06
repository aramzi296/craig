<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingShowTest extends TestCase
{
    use RefreshDatabase;

    private $owner;
    private $otherUser;
    private $admin;
    private $listing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Owner User',
            'whatsapp' => '6281234567890',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->otherUser = User::create([
            'name' => 'Other User',
            'whatsapp' => '6289876543210',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'whatsapp' => '6289999999999',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $this->listing = Listing::create([
            'user_id' => $this->owner->id,
            'title' => 'Nagoya Food Court Batam',
            'description' => 'Pusat kuliner terlengkap di Nagoya Batam.',
            'address' => 'Nagoya, Batam',
            'slug' => 'profil-bisnis-nagoya-food-court-batam-6a2410c1c33b8',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function guest_cannot_see_edit_listing_link()
    {
        $response = $this->get(route('listings.show', $this->listing->slug));
        $response->assertOk();
        $response->assertDontSee('Edit Profil Usaha');
        $response->assertDontSee('Edit Profil Usaha (Admin)');
    }

    /** @test */
    public function non_owner_user_cannot_see_edit_listing_link()
    {
        $response = $this->actingAs($this->otherUser)->get(route('listings.show', $this->listing->slug));
        $response->assertOk();
        $response->assertDontSee('Edit Profil Usaha');
        $response->assertDontSee('Edit Profil Usaha (Admin)');
    }

    /** @test */
    public function owner_can_see_regular_edit_listing_link()
    {
        $response = $this->actingAs($this->owner)->get(route('listings.show', $this->listing->slug));
        $response->assertOk();
        $response->assertSee(route('listings.edit', $this->listing->id));
        $response->assertSee('Edit Profil Usaha');
        $response->assertDontSee('Edit Profil Usaha (Admin)');
    }

    /** @test */
    public function admin_can_see_admin_edit_listing_link()
    {
        $response = $this->actingAs($this->admin)->get(route('listings.show', $this->listing->slug));
        $response->assertOk();
        $response->assertSee(route('admin.listings.edit', $this->listing->id));
        $response->assertSee('Edit Profil Usaha (Admin)');
    }
}
