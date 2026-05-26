<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingImportWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_listing_and_photos_from_webhook_payload(): void
    {
        config(['services.webhook_import.secret' => null]);

        $payload = [
            [
                'nama' => 'Samuel Sitanggang',
                'alamat' => 'Batam',
                'keterangan_usaha' => 'Menyediakan pasir plester.',
                'nomor_wa' => '6281395707840',
            ],
            [
                'uploaded_files' => [
                    'https://direktori-sebatam.sebatam.com/upload/2026/05/26/photo1.jpg',
                    'https://direktori-sebatam.sebatam.com/upload/2026/05/26/photo2.jpg',
                ],
                'total' => 2,
            ],
        ];

        $response = $this->postJson(route('webhook.listing-import'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_created', true)
            ->assertJsonPath('data.photos_count', 2);

        $user = User::where('whatsapp', '6281395707840')->first();
        $this->assertNotNull($user);
        $this->assertSame('Samuel Sitanggang', $user->name);

        $listing = Listing::where('user_id', $user->id)->first();
        $this->assertNotNull($listing);
        $this->assertSame('Samuel Sitanggang', $listing->title);
        $this->assertSame('Menyediakan pasir plester.', $listing->description);

        $this->assertSame(2, ListingPhoto::where('listing_id', $listing->id)->count());
        $this->assertTrue(
            ListingPhoto::where('listing_id', $listing->id)->where('collection', 'foto_fitur')->exists()
        );
    }

    public function test_reuses_existing_user_by_whatsapp(): void
    {
        config(['services.webhook_import.secret' => null]);

        $existing = User::create([
            'name' => 'Existing User',
            'email' => 'existing@sebatam.com',
            'password' => bcrypt('password'),
            'whatsapp' => '6281111111111',
            'ads_quota' => 5,
        ]);

        $payload = [
            [
                'nama' => 'Toko Baru',
                'alamat' => 'Batam',
                'keterangan_usaha' => 'Deskripsi.',
                'nomor_wa' => '081111111111',
            ],
            [
                'uploaded_files' => [
                    'https://direktori-sebatam.sebatam.com/upload/2026/05/26/only.jpg',
                ],
            ],
        ];

        $response = $this->postJson(route('webhook.listing-import'), $payload);

        $response->assertCreated()
            ->assertJsonPath('data.user_id', $existing->id)
            ->assertJsonPath('data.user_created', false);
    }

    public function test_accepts_flat_json_object_from_n8n(): void
    {
        config(['services.webhook_import.secret' => null]);

        $payload = [
            'nama' => 'Samuel Sitanggang',
            'alamat' => 'Batam',
            'keterangan_usaha' => 'Menyediakan pasir plester.',
            'nomor_wa' => '6282222222222',
            'uploaded_files' => [
                'https://direktori-sebatam.sebatam.com/upload/2026/05/26/photo1.jpg',
            ],
            'total' => 1,
        ];

        $this->postJson(route('webhook.listing-import'), $payload)
            ->assertCreated()
            ->assertJsonPath('data.photos_count', 1);
    }

    public function test_rejects_request_without_secret_when_configured(): void
    {
        config(['services.webhook_import.secret' => 'test-secret']);

        $response = $this->postJson(route('webhook.listing-import'), []);

        $response->assertUnauthorized();
    }
}
