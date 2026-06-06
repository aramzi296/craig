<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\User;

class AdminCategoriesFilterTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'whatsapp' => '6281111111111',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function admin_can_view_categories_list_without_filters()
    {
        $category = Category::create([
            'name' => 'Kuliner Nusantara',
            'slug' => 'kuliner-nusantara',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories'));

        $response->assertOk();
        $response->assertSee('Kuliner Nusantara');
    }

    /** @test */
    public function admin_can_filter_categories_by_search_keyword()
    {
        $match = Category::create([
            'name' => 'Bengkel Las',
            'slug' => 'bengkel-las',
            'is_approved' => true,
        ]);

        $noMatch = Category::create([
            'name' => 'Pendidikan Anak',
            'slug' => 'pendidikan-anak',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories', ['search' => 'bengkel']));

        $response->assertOk();
        $response->assertSee('Bengkel Las');
        $response->assertDontSee('Pendidikan Anak');
    }

    /** @test */
    public function admin_can_filter_categories_by_type()
    {
        $parent = Category::create([
            'name' => 'Kategori Induk',
            'slug' => 'kategori-induk',
            'is_approved' => true,
            'parent_id' => null,
        ]);

        $child = Category::create([
            'name' => 'Kategori Anak',
            'slug' => 'kategori-anak',
            'is_approved' => true,
            'parent_id' => $parent->id,
        ]);

        $anotherParent = Category::create([
            'name' => 'Kategori Lain',
            'slug' => 'kategori-lain',
            'is_approved' => true,
            'parent_id' => null,
        ]);

        // Filter only parent categories
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories', ['type' => 'parent']));

        $response->assertOk();
        $response->assertSee('Kategori Induk');
        $response->assertSee('Kategori Lain');
        $response->assertDontSee('Kategori Anak');

        // Filter only subcategories
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories', ['type' => 'sub']));

        $response->assertOk();
        $response->assertSee('Kategori Anak');
        $response->assertDontSee('Kategori Lain');
    }

    /** @test */
    public function admin_can_filter_categories_by_approval_status()
    {
        $approved = Category::create([
            'name' => 'Kategori Disetujui',
            'slug' => 'kategori-disetujui',
            'is_approved' => true,
        ]);

        $unapproved = Category::create([
            'name' => 'Kategori Pending',
            'slug' => 'kategori-pending',
            'is_approved' => false,
        ]);

        // Filter only approved
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories', ['status' => 'approved']));

        $response->assertOk();
        $response->assertSee('Kategori Disetujui');
        $response->assertDontSee('Kategori Pending');

        // Filter only unapproved
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories', ['status' => 'unapproved']));

        $response->assertOk();
        $response->assertSee('Kategori Pending');
        $response->assertDontSee('Kategori Disetujui');
    }
}
