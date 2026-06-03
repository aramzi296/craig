<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPhotosTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $listing1;
    private $listing2;
    private $photo1;
    private $photo2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'whatsapp' => '6289999999999',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        // Create normal user
        $this->user = User::create([
            'name' => 'Normal User',
            'whatsapp' => '6281111111111',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        // Create listings
        $this->listing1 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Jasa AC Batam Center',
            'slug' => 'jasa-ac-batam-center',
            'description' => 'Servis AC terpercaya.',
            'price' => 50000,
            'is_active' => true,
        ]);

        $this->listing2 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Laundry Kiloan Nagoya',
            'slug' => 'laundry-kiloan-nagoya',
            'description' => 'Cuci bersih wangi.',
            'price' => 7000,
            'is_active' => true,
        ]);

        // Create listing photos
        $this->photo1 = ListingPhoto::create([
            'listing_id' => $this->listing1->id,
            'photo_path' => 'photos/ac_featured.jpg',
            'thumbnail_path' => 'photos/ac_featured_thumb.jpg',
            'collection' => 'foto_fitur',
        ]);

        $this->photo2 = ListingPhoto::create([
            'listing_id' => $this->listing2->id,
            'photo_path' => 'photos/laundry_gallery.jpg',
            'thumbnail_path' => 'photos/laundry_gallery_thumb.jpg',
            'collection' => 'galeri',
        ]);
    }

    /** @test */
    public function guests_cannot_access_photos_management()
    {
        $response = $this->get(route('admin.photos'));
        $response->assertRedirect('/login');
    }

    /** @test */
    public function non_admins_cannot_access_photos_management()
    {
        $response = $this->actingAs($this->user)->get(route('admin.photos'));
        $response->assertRedirect('/');
    }

    /** @test */
    public function admins_can_view_photos_index_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.photos'));

        $response->assertOk();
        $response->assertSee('Gambar Sebatam');
        $response->assertSee('Jasa AC Batam Center');
        $response->assertSee('Laundry Kiloan Nagoya');
        $response->assertSee('Foto Fitur');
        $response->assertSee('Galeri');
    }

    /** @test */
    public function admins_can_search_photos_by_listing_title()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.photos', ['search' => 'AC']));

        $response->assertOk();
        $response->assertSee('Jasa AC Batam Center');
        $response->assertDontSee('Laundry Kiloan Nagoya');
    }

    /** @test */
    public function admins_can_delete_a_photo()
    {
        $this->assertDatabaseHas('listing_photos', ['id' => $this->photo1->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.listings.photos.destroy', $this->photo1->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Foto berhasil dihapus.');

        $this->assertDatabaseMissing('listing_photos', ['id' => $this->photo1->id]);
    }
}
