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
}
