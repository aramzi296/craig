<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ListingImportWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(function () {
            return Http::response('fake-image-bytes', 200, [
                'Content-Type' => 'image/jpeg',
                'Content-Length' => '17',
            ]);
        });
    }

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

    public function test_creates_listing_from_form_post_with_judul_and_referensi(): void
    {
        config(['services.webhook_import.secret' => null]);

        $payload = [
            'nama' => 'John Doe',
            'judul' => 'Toko Elektronik Makmur',
            'alamat' => 'Batam',
            'keterangan_usaha' => 'Jual laptop dan HP murah.',
            'nomor_wa' => '081234567890',
            'referensi' => 'Rekomendasi Teman',
            'uploaded_files' => "https://direktori-sebatam.sebatam.com/upload/2026/05/26/photo1.jpg, https://direktori-sebatam.sebatam.com/upload/2026/05/26/photo2.jpg",
        ];

        // Send as a standard form POST (urlencoded)
        $response = $this->post(route('webhook.listing-import'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_created', true)
            ->assertJsonPath('data.photos_count', 2);

        $user = User::where('whatsapp', '6281234567890')->first();
        $this->assertNotNull($user);
        $this->assertSame('John Doe', $user->name);

        $listing = Listing::where('user_id', $user->id)->first();
        $this->assertNotNull($listing);
        $this->assertSame('Toko Elektronik Makmur', $listing->title);
        $this->assertSame('Jual laptop dan HP murah.', $listing->description);
        
        // Assert the features contains referensi and judul
        $this->assertIsArray($listing->features);
        $this->assertSame('Toko Elektronik Makmur', $listing->features['judul']);
        $this->assertSame('Toko Elektronik Makmur', $listing->features['nama_usaha']);
        $this->assertSame('Rekomendasi Teman', $listing->features['referensi']);

        $this->assertSame(2, ListingPhoto::where('listing_id', $listing->id)->count());
    }

    public function test_creates_listing_from_user_json_list_payload(): void
    {
        config(['services.webhook_import.secret' => null]);

        $payload = [
            [
                'nama' => 'Prandiyanto Siregar',
                'judul' => 'Jasa Tukang Bangunan',
                'alamat' => 'Batam',
                'nomor_wa' => '6285374277648',
                'keterangan_usaha' => 'Menawarkan jasa dengan keahlian sebagai tukang bangunan. Berdasarkan foto-foto hasil kerja yang diunggah, pengerjaannya meliputi konstruksi dan renovasi rumah seperti pemasangan bata/dinding, plesteran, hingga pengerjaan plafon. Saat ini sedang aktif mencari informasi tawaran pekerjaan atau proyek pertukangan.',
                'uploaded_files' => [
                    'https://direktori-sebatam.sebatam.com/upload/2026/05/28/0c50c5e1-1047-4cc2-889f-9a3e81826016.jpg',
                    'https://direktori-sebatam.sebatam.com/upload/2026/05/28/60eb32b7-4ef7-4e20-9665-77bade225185.jpg',
                    'https://direktori-sebatam.sebatam.com/upload/2026/05/28/c32291ed-b0a0-4a29-a7c7-fe014a711a66.jpg',
                ],
            ]
        ];

        $response = $this->postJson(route('webhook.listing-import'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_created', true)
            ->assertJsonPath('data.photos_count', 3);

        $user = User::where('whatsapp', '6285374277648')->first();
        $this->assertNotNull($user);
        $this->assertSame('Prandiyanto Siregar', $user->name);

        $listing = Listing::where('user_id', $user->id)->first();
        $this->assertNotNull($listing);
        $this->assertSame('Jasa Tukang Bangunan', $listing->title);
        $this->assertTrue(str_contains($listing->description, 'Menawarkan jasa dengan keahlian'));

        $this->assertSame(3, ListingPhoto::where('listing_id', $listing->id)->count());
    }

    public function test_imports_listing_successfully_with_undefined_files_and_missing_alamat(): void
    {
        config(['services.webhook_import.secret' => null]);

        $payload = [
            [
                "uploaded_files" => "https://direktori-sebatam.sebatam.com/undefined",
                "keterangan_usaha" => "Pijat pria batam\nSedia tempat dan menerima panggilan",
                "group_url" => "https://www.facebook.com/groups/397212271668337",
                "nomor_wa" => "6285972998865",
                "nama" => "Rendy Piat Pria Batam"
            ]
        ];

        $response = $this->postJson(route('webhook.listing-import'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.photos_count', 0);

        $user = User::where('whatsapp', '6285972998865')->first();
        $this->assertNotNull($user);
        $this->assertSame('Rendy Piat Pria Batam', $user->name);

        $listing = Listing::where('user_id', $user->id)->first();
        $this->assertNotNull($listing);
        $this->assertSame('Rendy Piat Pria Batam', $listing->title);
        $this->assertSame('Batam', $listing->address);
        
        $this->assertSame(0, ListingPhoto::where('listing_id', $listing->id)->count());
    }
}
