<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class N8nSendTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $admin;
    private $n8nUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->n8nUrl = config('services.n8n.listing_webhook_url');

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
    public function guests_and_non_admins_cannot_send_to_n8n()
    {
        // Guest is redirected
        $response = $this->post(route('admin.send-to-n8n'), [
            'text' => 'Halo n8n',
        ]);
        $response->assertRedirect('/login');

        // Non-admin is redirected with error
        $response = $this->actingAs($this->user)->post(route('admin.send-to-n8n'), [
            'text' => 'Halo n8n',
        ]);
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman ini.');
    }

    /** @test */
    public function admin_cannot_send_to_n8n_without_files_or_with_short_text()
    {
        // Try sending short text and no files
        $response = $this->actingAs($this->admin)->postJson(route('admin.send-to-n8n'), [
            'text' => 'Pesan pendek',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['text', 'files']);
    }

    /** @test */
    public function admin_can_send_valid_text_and_images_to_n8n()
    {
        Http::fake([
            $this->n8nUrl => Http::response(['status' => 'success'], 200)
        ]);

        $file1 = UploadedFile::fake()->image('logo.png');
        $file2 = UploadedFile::fake()->image('banner.jpg');
        $longText = 'Pesan teks uji coba yang panjangnya harus melebihi batas minimal 50 karakter agar dapat lolos validasi sistem.';

        $response = $this->actingAs($this->admin)->post(route('admin.send-to-n8n'), [
            'text' => $longText,
            'files' => [$file1, $file2]
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Data berhasil dikirim ke n8n.',
        ]);

        Http::assertSent(function ($request) use ($longText) {
            $textElement = collect($request->data())->firstWhere('name', 'text');
            return $request->url() === $this->n8nUrl &&
                   $request->isMultipart() &&
                   $textElement &&
                   $textElement['contents'] === $longText &&
                   $request->hasFile('files[]', null, 'logo.png') &&
                   $request->hasFile('files[]', null, 'banner.jpg');
        });
    }

    /** @test */
    public function admin_cannot_send_non_image_files_to_n8n()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $longText = 'Pesan teks uji coba yang panjangnya harus melebihi batas minimal 50 karakter agar dapat lolos validasi sistem.';

        $response = $this->actingAs($this->admin)->postJson(route('admin.send-to-n8n'), [
            'text' => $longText,
            'files' => [$file]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['files.0']);
    }

    /** @test */
    public function n8n_form_validation_requires_text()
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.send-to-n8n'), [
            'text' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['text']);
    }

    /** @test */
    public function guests_and_non_admins_cannot_access_n8n_listings_page()
    {
        // Guest is redirected to login
        $response = $this->get(route('admin.n8n.listings'));
        $response->assertRedirect('/login');

        // Non-admin is redirected with error
        $response = $this->actingAs($this->user)->get(route('admin.n8n.listings'));
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman ini.');
    }

    /** @test */
    public function admin_can_access_n8n_listings_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.n8n.listings'));
        $response->assertOk();
        $response->assertSee('n8n Listing');
        $response->assertSee('Kirim Data ke n8n');
    }

    /** @test */
    public function guests_and_non_admins_cannot_access_generate_tags()
    {
        // Guest is redirected
        $response = $this->get(route('admin.generate-tags'));
        $response->assertRedirect('/login');

        // Non-admin is redirected with error
        $response = $this->actingAs($this->user)->get(route('admin.generate-tags'));
        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Anda tidak memiliki akses ke halaman ini.');
    }

    /** @test */
    public function admin_can_trigger_generate_tags_and_sends_listings_without_tags_to_webhook()
    {
        // Create 2 listings without tags, and 1 listing with a tag
        $listing1 = \App\Models\Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Tag 1',
            'slug' => 'listing-tanpa-tag-1',
            'description' => 'Deskripsi listing tanpa tag pertama.',
            'is_active' => true,
        ]);

        $listing2 = \App\Models\Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Tag 2',
            'slug' => 'listing-tanpa-tag-2',
            'description' => 'Deskripsi listing tanpa tag kedua.',
            'is_active' => true,
        ]);

        $listingWithTag = \App\Models\Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Dengan Tag',
            'slug' => 'listing-dengan-tag',
            'description' => 'Deskripsi listing yang sudah memiliki tag.',
            'is_active' => true,
        ]);
        
        $tag = \App\Models\Tag::create([
            'name' => 'TestTag',
            'slug' => 'testtag',
            'is_approved' => true
        ]);
        $listingWithTag->tags()->attach($tag->id);

        $webhookUrl = 'https://n8n-pfokjx3fv0cf.axwy.sumopod.my.id/webhook/e0d05b06-3bc5-4512-8dbb-ca7e28437e54';

        Http::fake([
            $webhookUrl => Http::sequence()
                ->push(['status' => 'success'], 200) // success for listing 1
                ->push(['status' => 'error'], 500)   // failure/error for listing 2
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.generate-tags', ['limit' => 2]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Proses pembuatan tagar selesai. Total listing diproses: 2. Berhasil: 1, Gagal: 1.');

        Http::assertSent(function ($request) use ($listing1, $webhookUrl) {
            return $request->url() === $webhookUrl &&
                   $request->method() === 'POST' &&
                   $request['id'] === $listing1->id &&
                   $request['description'] === $listing1->description;
        });

        Http::assertSent(function ($request) use ($listing2, $webhookUrl) {
            return $request->url() === $webhookUrl &&
                   $request->method() === 'POST' &&
                   $request['id'] === $listing2->id &&
                   $request['description'] === $listing2->description;
        });

        // Listing with tag should NOT be sent
        Http::assertNotSent(function ($request) use ($listingWithTag, $webhookUrl) {
            return $request['id'] === $listingWithTag->id;
        });
    }

    /** @test */
    public function admin_trigger_generate_tags_defaults_to_limit_of_1()
    {
        // Create 2 listings without tags
        $listing1 = \App\Models\Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Tag 1',
            'slug' => 'listing-tanpa-tag-1',
            'description' => 'Deskripsi listing tanpa tag pertama.',
            'is_active' => true,
        ]);

        $listing2 = \App\Models\Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing Tanpa Tag 2',
            'slug' => 'listing-tanpa-tag-2',
            'description' => 'Deskripsi listing tanpa tag kedua.',
            'is_active' => true,
        ]);

        $webhookUrl = 'https://n8n-pfokjx3fv0cf.axwy.sumopod.my.id/webhook/e0d05b06-3bc5-4512-8dbb-ca7e28437e54';

        Http::fake([
            $webhookUrl => Http::response(['status' => 'success'], 200)
        ]);

        // Trigger without limit query parameter
        $response = $this->actingAs($this->admin)->get(route('admin.generate-tags'));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Proses pembuatan tagar selesai. Total listing diproses: 1. Berhasil: 1, Gagal: 0.');

        Http::assertSent(function ($request) use ($listing1, $webhookUrl) {
            return $request['id'] === $listing1->id;
        });

        // The second listing should NOT be sent because of default limit of 1
        Http::assertNotSent(function ($request) use ($listing2, $webhookUrl) {
            return $request['id'] === $listing2->id;
        });
    }
}
