<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Listing;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AdminListingsByJsonTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $existingUser;

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

        // Create an existing user
        $this->existingUser = User::create([
            'name' => 'Existing User',
            'whatsapp' => '6285789173762',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        Storage::fake('r2');
        Http::fake();
    }

    /** @test */
    public function admin_can_view_listing_by_json_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.listings.json'));
        $response->assertOk();
        $response->assertSee('Listing By JSON');
        $response->assertSee('Payload JSON');
        $response->assertSee('name="website"', false);
    }

    /** @test */
    public function admin_cannot_use_already_registered_whatsapp_number()
    {
        $jsonPayload = json_encode([
            'judul' => 'Jasa Tukang Bangunan Batam',
            'nama' => 'Mitro Sanjay',
            'alamat' => 'Batam',
            'keterangan_usaha' => 'Melayani jasa pertukangan dan renovasi bangunan.',
            'nomor_wa' => '6285789173762' // existingUser's whatsapp
        ]);

        $foto = UploadedFile::fake()->image('tukang.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.listings.json.store'), [
            'json_data' => $jsonPayload,
            'foto' => $foto,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Nomor WhatsApp ini sudah terdaftar.');
        
        // Assert no new user was created
        $this->assertDatabaseCount('users', 2);
    }

    /** @test */
    public function admin_can_create_new_user_and_listing_by_json_successfully()
    {
        Http::fake([
            'https://n8n-pfokjx3fv0cf.axwy.sumopod.my.id/*' => Http::response(['status' => 'success'], 200),
        ]);

        $jsonPayload = json_encode([
            'judul' => 'Jasa Tukang Bangunan Batam',
            'nama' => 'Mitro Sanjay',
            'alamat' => 'Batam',
            'keterangan_usaha' => 'Melayani jasa pertukangan dan renovasi bangunan.',
            'nomor_wa' => '628123456789' // new whatsapp
        ]);

        $foto = UploadedFile::fake()->image('tukang.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.listings.json.store'), [
            'json_data' => $jsonPayload,
            'foto' => $foto,
            'website' => 'https://mitrosanjay.com',
        ]);

        $response->assertRedirect(route('admin.listings'));
        $response->assertSessionHas('success');

        // Check if user is created
        $user = User::where('whatsapp', '628123456789')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Mitro Sanjay', $user->name);
        $this->assertTrue($user->is_verified);

        // Check if listing is created
        $listing = Listing::where('user_id', $user->id)->first();
        $this->assertNotNull($listing);
        $this->assertEquals('Jasa Tukang Bangunan Batam', $listing->title);
        $this->assertEquals('Melayani jasa pertukangan dan renovasi bangunan.', $listing->description);
        $this->assertEquals('Batam', $listing->address);
        $this->assertEquals('https://mitrosanjay.com', $listing->website);
        $this->assertTrue($listing->is_active);

        // Assert photo is uploaded and attached
        $this->assertDatabaseHas('listing_photos', [
            'listing_id' => $listing->id,
            'collection' => 'foto_fitur',
        ]);

        // Assert Http webhook call was triggered
        Http::assertSent(function ($request) use ($listing) {
            return $request->url() === 'https://n8n-pfokjx3fv0cf.axwy.sumopod.my.id/webhook/e0d05b06-3bc5-4512-8dbb-ca7e28437e54'
                && $request['id'] == $listing->id
                && $request['description'] == $listing->description;
        });
    }

    /** @test */
    public function admin_can_create_listing_without_website_optionally()
    {
        Http::fake([
            'https://n8n-pfokjx3fv0cf.axwy.sumopod.my.id/*' => Http::response(['status' => 'success'], 200),
        ]);

        $jsonPayload = json_encode([
            'judul' => 'Jasa Tukang Listrik Batam',
            'nama' => 'Ahmad Listrik',
            'alamat' => 'Batam Center',
            'keterangan_usaha' => 'Melayani instalasi listrik rumah tangga.',
            'nomor_wa' => '628987654321' // new whatsapp
        ]);

        $foto = UploadedFile::fake()->image('listrik.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.listings.json.store'), [
            'json_data' => $jsonPayload,
            'foto' => $foto,
            // 'website' is omitted (optional)
        ]);

        $response->assertRedirect(route('admin.listings'));
        $response->assertSessionHas('success');

        $user = User::where('whatsapp', '628987654321')->first();
        $this->assertNotNull($user);

        $listing = Listing::where('user_id', $user->id)->first();
        $this->assertNotNull($listing);
        $this->assertNull($listing->website);
    }

    /** @test */
    public function fail_if_json_is_invalid()
    {
        $invalidJson = '{invalid-json}';
        $foto = UploadedFile::fake()->image('tukang.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.listings.json.store'), [
            'json_data' => $invalidJson,
            'foto' => $foto,
        ]);

        $response->assertSessionHasErrors(['json_data']);
        $this->assertDatabaseCount('users', 2);
    }

    /** @test */
    public function fail_if_required_keys_are_missing()
    {
        $payloadMissingKeys = json_encode([
            'nama' => 'Mitro Sanjay',
            'nomor_wa' => '628123456789'
            // Missing: judul, alamat, keterangan_usaha
        ]);

        $foto = UploadedFile::fake()->image('tukang.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.listings.json.store'), [
            'json_data' => $payloadMissingKeys,
            'foto' => $foto,
        ]);

        $response->assertSessionHasErrors(['json_data']);
        $this->assertDatabaseCount('users', 2);
    }

    /** @test */
    public function admin_can_create_listing_using_admin_account_when_nomor_wa_is_missing()
    {
        Http::fake([
            'https://n8n-pfokjx3fv0cf.axwy.sumopod.my.id/*' => Http::response(['status' => 'success'], 200),
        ]);

        $jsonPayload = json_encode([
            'judul' => 'Jasa Servis AC Batam',
            'alamat' => 'Nongsa',
            'keterangan_usaha' => 'Melayani servis dan cuci AC murah.',
            // nomor_wa and nama are missing
        ]);

        $foto = UploadedFile::fake()->image('ac.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.listings.json.store'), [
            'json_data' => $jsonPayload,
            'foto' => $foto,
        ]);

        $response->assertRedirect(route('admin.listings'));
        $response->assertSessionHas('success', "Listing 'Jasa Servis AC Batam' berhasil dibuat menggunakan akun admin Anda. Tagar otomatis sedang diproses.");

        // Assert no new user was created
        $this->assertDatabaseCount('users', 2);

        // Check if listing is created with user_id set to admin's ID
        $listing = Listing::where('title', 'Jasa Servis AC Batam')->first();
        $this->assertNotNull($listing);
        $this->assertEquals($this->admin->id, $listing->user_id);
    }
}
