<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $imageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->imageService = new ImageService();
    }

    public function test_upload_listing_photo_to_r2(): void
    {
        Storage::fake('r2');
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'whatsapp' => '628123456789',
            'is_admin' => \Illuminate\Support\Facades\DB::raw('false'),
            'is_verified' => \Illuminate\Support\Facades\DB::raw('false'),
            'ads_quota' => 10,
        ]);

        $listing = Listing::create([
            'user_id' => $user->id,
            'title' => 'Test Listing',
            'description' => 'Description of the test listing',
            'address' => 'Test Address',
            'slug' => 'test-listing-' . uniqid(),
            'is_active' => \Illuminate\Support\Facades\DB::raw('true'),
            'ad_package' => 'standard',
        ]);

        $file = UploadedFile::fake()->image('photo.jpg', 600, 600);

        $photo = $this->imageService->uploadListingPhoto($file, $listing->id, 'foto_fitur');

        $this->assertInstanceOf(ListingPhoto::class, $photo);
        
        $r2Url = rtrim(config('filesystems.disks.r2.url'), '/');
        $this->assertStringStartsWith($r2Url . '/upload/', $photo->photo_path);
        
        $relativePath = ltrim(str_replace($r2Url, '', $photo->photo_path), '/');
        Storage::disk('r2')->assertExists($relativePath);
    }

    public function test_delete_listing_photo_from_r2(): void
    {
        Storage::fake('r2');
        
        $r2Url = rtrim(config('filesystems.disks.r2.url'), '/');
        $fakePath = $r2Url . '/upload/' . date('Y') . '/' . date('m') . '/' . date('d') . '/fake_photo.jpg';
        $relativePath = ltrim(str_replace($r2Url, '', $fakePath), '/');

        Storage::disk('r2')->put($relativePath, 'fake_content');
        Storage::disk('r2')->assertExists($relativePath);

        $this->imageService->deleteByPath($fakePath);

        Storage::disk('r2')->assertMissing($relativePath);
    }
}
