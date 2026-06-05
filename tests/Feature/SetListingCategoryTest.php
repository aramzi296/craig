<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Listing;
use App\Models\Category;
use Illuminate\Support\Facades\Http;

class SetListingCategoryTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $admin;
    private $webhookUrl;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->webhookUrl = 'https://n8n-pfokjx3fv0cf.axwy.sumopod.my.id/webhook-test/b2ffd1ad-a816-4a3e-80a2-523101342679';
        config(['services.n8n.listing_category_webhook_url' => $this->webhookUrl]);

        // Create standard user
        $this->user = User::create([
            'name' => 'John Doe',
            'whatsapp' => '6281234567890',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'whatsapp' => '6289999999999',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function guests_cannot_access_set_category()
    {
        $response = $this->get(route('admin.set-category'));
        $response->assertRedirect('/login');
    }

    /** @test */
    public function non_admins_cannot_access_set_category()
    {
        $response = $this->actingAs($this->user)->get(route('admin.set-category'));
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman ini.');
    }

    /** @test */
    public function admin_can_trigger_set_category_with_no_eligible_listings()
    {
        // If there are no listings at all
        $response = $this->actingAs($this->admin)->get(route('admin.set-category'));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Semua listing sudah memiliki kategori atau sedang diproses oleh n8n.');

        // If there's a listing, but it already has a category
        $listing = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Dengan Kategori',
            'slug' => 'listing-dengan-kategori',
            'description' => 'Deskripsi listing dengan kategori.',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
        $listing->categories()->attach($category->id);

        $response = $this->actingAs($this->admin)->get(route('admin.set-category'));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Semua listing sudah memiliki kategori atau sedang diproses oleh n8n.');
    }

    /** @test */
    public function admin_can_trigger_set_category_sends_eligible_listings_to_webhook()
    {
        // Listing 1 without category
        $listing1 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Kategori 1',
            'slug' => 'listing-tanpa-kategori-1',
            'description' => 'Deskripsi listing tanpa kategori pertama.',
            'is_active' => true,
        ]);

        // Listing 2 without category
        $listing2 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Kategori 2',
            'slug' => 'listing-tanpa-kategori-2',
            'description' => 'Deskripsi listing tanpa kategori kedua.',
            'is_active' => true,
        ]);

        // Listing with category (should be skipped)
        $listingWithCategory = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Dengan Kategori',
            'slug' => 'listing-dengan-kategori',
            'description' => 'Deskripsi listing yang sudah memiliki kategori.',
            'is_active' => true,
        ]);
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
        $listingWithCategory->categories()->attach($category->id);

        Http::fake([
            $this->webhookUrl => Http::sequence()
                ->push(['status' => 'success'], 200) // success for listing 1
                ->push(['status' => 'error'], 500)   // failure for listing 2
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.set-category', ['limit' => 2]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Proses pengaturan kategori selesai. Total listing diproses: 2. Berhasil: 1, Gagal: 1.');

        Http::assertSent(function ($request) use ($listing1) {
            return $request->url() === $this->webhookUrl &&
                   $request->method() === 'POST' &&
                   $request['id'] === $listing1->id &&
                   $request['description'] === $listing1->description;
        });

        Http::assertSent(function ($request) use ($listing2) {
            return $request->url() === $this->webhookUrl &&
                   $request->method() === 'POST' &&
                   $request['id'] === $listing2->id &&
                   $request['description'] === $listing2->description;
        });

        // Listing with category should NOT be sent
        Http::assertNotSent(function ($request) use ($listingWithCategory) {
            return $request['id'] === $listingWithCategory->id;
        });
    }

    /** @test */
    public function admin_trigger_set_category_respects_default_limit_of_1()
    {
        $listing1 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Kategori 1',
            'slug' => 'listing-tanpa-kategori-1',
            'description' => 'Deskripsi listing tanpa kategori pertama.',
            'is_active' => true,
        ]);

        $listing2 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Kategori 2',
            'slug' => 'listing-tanpa-kategori-2',
            'description' => 'Deskripsi listing tanpa kategori kedua.',
            'is_active' => true,
        ]);

        Http::fake([
            $this->webhookUrl => Http::response(['status' => 'success'], 200)
        ]);

        // Trigger without limit query parameter (defaults to 1)
        $response = $this->actingAs($this->admin)->get(route('admin.set-category'));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Proses pengaturan kategori selesai. Total listing diproses: 1. Berhasil: 1, Gagal: 0.');

        Http::assertSent(function ($request) use ($listing1) {
            return $request['id'] === $listing1->id;
        });

        // The second listing should NOT be sent because of limit of 1
        Http::assertNotSent(function ($request) use ($listing2) {
            return $request['id'] === $listing2->id;
        });
    }

    /** @test */
    public function admin_trigger_set_category_excludes_listings_already_processing()
    {
        $listing1 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Kategori 1',
            'slug' => 'listing-tanpa-kategori-1',
            'description' => 'Deskripsi listing tanpa kategori pertama.',
            'is_active' => true,
        ]);

        $listing2 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Kategori 2',
            'slug' => 'listing-tanpa-kategori-2',
            'description' => 'Deskripsi listing tanpa kategori kedua.',
            'is_active' => true,
        ]);

        Http::fake([
            $this->webhookUrl => Http::response(['status' => 'success'], 200)
        ]);

        // First call - should send listing 1 (since limit is 1)
        $response = $this->actingAs($this->admin)->get(route('admin.set-category', ['limit' => 1]));
        $response->assertSessionHas('success', 'Proses pengaturan kategori selesai. Total listing diproses: 1. Berhasil: 1, Gagal: 0.');

        Http::assertSent(function ($request) use ($listing1) {
            return $request['id'] === $listing1->id;
        });

        // Clear recorded requests for clean assertions
        Http::fake([
            $this->webhookUrl => Http::response(['status' => 'success'], 200)
        ]);

        // Second call immediately after - should send listing 2 because listing 1 is marked as processing in cache
        $response = $this->actingAs($this->admin)->get(route('admin.set-category', ['limit' => 1]));
        $response->assertSessionHas('success', 'Proses pengaturan kategori selesai. Total listing diproses: 1. Berhasil: 1, Gagal: 0.');

        Http::assertSent(function ($request) use ($listing2) {
            return $request['id'] === $listing2->id;
        });
        
        Http::assertNotSent(function ($request) use ($listing1) {
            return $request['id'] === $listing1->id;
        });
    }
}
