<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListingsSearchTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $userA;
    private $userB;
    private $listing1;
    private $listing2;
    private $listing3;

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

        // Create test users
        $this->userA = User::create([
            'name' => 'Joko Widodo',
            'whatsapp' => '628123456789',
            'email' => 'joko@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $this->userB = User::create([
            'name' => 'Susi Pudjiastuti',
            'whatsapp' => '628987654321',
            'email' => 'susi@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        // Create listings
        $this->listing1 = Listing::create([
            'user_id' => $this->userA->id,
            'title' => 'Bengkel Las Mandiri',
            'slug' => 'bengkel-las-mandiri',
            'description' => 'Jasa las besi berkualitas.',
            'price' => 200000,
            'is_active' => true,
        ]);
        $this->listing1->updateSearchableField();

        $this->listing2 = Listing::create([
            'user_id' => $this->userB->id,
            'title' => 'Katering Sehat Susi',
            'slug' => 'katering-sehat-susi',
            'description' => 'Katering makanan sehat harian.',
            'price' => 35000,
            'is_active' => true,
        ]);
        $this->listing2->updateSearchableField();

        $this->listing3 = Listing::create([
            'user_id' => $this->userA->id,
            'title' => 'Toko Sembako Berkah',
            'slug' => 'toko-sembako-berkah',
            'description' => 'Menyediakan kebutuhan sehari-hari.',
            'price' => 5000,
            'is_active' => true,
        ]);
        $this->listing3->updateSearchableField();
    }

    /** @test */
    public function admin_can_search_listings_by_title_or_content()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.listings', ['search' => 'Katering']));

        $response->assertOk();
        $response->assertSee('Katering Sehat Susi');
        $response->assertDontSee('Bengkel Las Mandiri');
        $response->assertDontSee('Toko Sembako Berkah');
    }

    /** @test */
    public function admin_can_search_listings_by_owner_name()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.listings', ['search' => 'Joko']));

        $response->assertOk();
        $response->assertSee('Bengkel Las Mandiri');
        $response->assertSee('Toko Sembako Berkah');
        $response->assertDontSee('Katering Sehat Susi');
    }

    /** @test */
    public function admin_can_search_listings_by_owner_whatsapp_raw_number()
    {
        // 08123456789 should normalize to 628123456789 and match userA's listings
        $response = $this->actingAs($this->admin)->get(route('admin.listings', ['search' => '08123456789']));

        $response->assertOk();
        $response->assertSee('Bengkel Las Mandiri');
        $response->assertSee('Toko Sembako Berkah');
        $response->assertDontSee('Katering Sehat Susi');
    }

    /** @test */
    public function admin_can_search_listings_by_owner_whatsapp_normalized_number()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.listings', ['search' => '628987654321']));

        $response->assertOk();
        $response->assertSee('Katering Sehat Susi');
        $response->assertDontSee('Bengkel Las Mandiri');
        $response->assertDontSee('Toko Sembako Berkah');
    }
}
