<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;

class CategoryDirectoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function page_loads_successfully_and_displays_flat_categories()
    {
        // Create approved parent category
        $parent = Category::create([
            'name' => 'Kategori Induk',
            'slug' => 'kategori-induk',
            'is_approved' => true,
        ]);

        // Create approved child category
        $child = Category::create([
            'name' => 'Kategori Anak',
            'slug' => 'kategori-anak',
            'parent_id' => $parent->id,
            'is_approved' => true,
        ]);

        // Create unapproved category (should not be displayed)
        $unapproved = Category::create([
            'name' => 'Kategori Belum Disetujui',
            'slug' => 'kategori-belum-disetujui',
            'is_approved' => false,
        ]);

        // Create user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'whatsapp' => '6281234567890',
            'password' => bcrypt('password'),
        ]);

        // Create listing for the parent category
        $listing = Listing::create([
            'user_id' => $user->id,
            'title' => 'Listing Uji Coba',
            'slug' => 'listing-uji-coba',
            'description' => 'Deskripsi listing uji coba.',
            'is_active' => true,
        ]);
        $listing->categories()->attach($parent->id);

        $response = $this->get(route('categories.directory'));

        $response->assertOk();
        $response->assertSee('Kategori Induk');
        $response->assertSee('Kategori Anak');
        $response->assertDontSee('Kategori Belum Disetujui');
        $response->assertSee('(1)');
        $response->assertSee('(0)');
    }
}
