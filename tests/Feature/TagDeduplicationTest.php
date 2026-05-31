<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'whatsapp' => '628123456789',
        ]);
    }

    public function test_tag_deduplication_merges_duplicate_tags(): void
    {
        // 1. Create duplicate tags
        // Tag 1 (Approved but no spaces)
        $tag1 = Tag::create([
            'name' => 'rentalmobil',
            'slug' => 'rentalmobil',
            'is_approved' => true,
            'icon' => 'fa-solid fa-tag',
        ]);

        // Tag 2 (Approved and has prettier name with spaces)
        $tag2 = Tag::create([
            'name' => 'rental mobil',
            'slug' => 'rental-mobil',
            'is_approved' => true,
            'icon' => null,
        ]);

        // 2. Create listings and associate with tags
        $listing1 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing 1',
            'slug' => 'listing-1',
            'description' => 'Listing 1 description',
            'price' => 1000,
            'is_active' => true,
            'address' => 'Address 1',
        ]);
        $listing1->tags()->attach($tag1->id);
        $listing1->updateSearchableField();

        $listing2 = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Listing 2',
            'slug' => 'listing-2',
            'description' => 'Listing 2 description',
            'price' => 2000,
            'is_active' => true,
            'address' => 'Address 2',
        ]);
        $listing2->tags()->attach($tag2->id);
        $listing2->updateSearchableField();

        // Initially, listings have different tags
        $this->assertTrue($listing1->tags->contains('id', $tag1->id));
        $this->assertTrue($listing2->tags->contains('id', $tag2->id));

        // 3. Run deduplication method
        $summary = Tag::deduplicate();

        // 4. Assert summary structure and contents
        $this->assertCount(1, $summary);
        $this->assertEquals('rental mobil', $summary[0]['primary_tag']['name']);
        $this->assertEquals(['rentalmobil'], $summary[0]['merged_tags']);
        $this->assertEquals(2, $summary[0]['listings_affected']);

        // 5. Assert duplicate tag (rentalmobil) is deleted
        $this->assertDatabaseMissing('tags', ['id' => $tag1->id]);
        $this->assertDatabaseHas('tags', ['id' => $tag2->id]);

        // 6. Assert master tag (rental mobil) is now approved because rentalmobil was approved
        $primaryTag = Tag::find($tag2->id);
        $this->assertTrue($primaryTag->is_approved);

        // 7. Assert master tag has the icon copied from rentalmobil
        $this->assertEquals('fa-solid fa-tag', $primaryTag->icon);

        // 8. Assert both listings are now attached to the master tag
        $this->assertTrue($listing1->fresh()->tags->contains('id', $primaryTag->id));
        $this->assertTrue($listing2->fresh()->tags->contains('id', $primaryTag->id));

        // 9. Assert listings searchable fields are updated to contain the master tag name
        $this->assertStringContainsString('rental mobil', $listing1->fresh()->searchable);
        $this->assertStringContainsString('rental mobil', $listing2->fresh()->searchable);
    }

    public function test_find_or_create_by_name_does_not_create_duplicate_tags(): void
    {
        // 1. Create a tag
        $tag = Tag::create([
            'name' => 'Baja Ringan',
            'slug' => 'baja-ringan',
            'is_approved' => true,
        ]);

        // 2. Lookup with case-insensitive / space-insensitive variation
        $found1 = Tag::findOrCreateByName('bajaringan');
        $found2 = Tag::findOrCreateByName('BAJA RINGAN');
        $found3 = Tag::findOrCreateByName('baja ringan');

        // 3. Assert they all return the exact same tag instance
        $this->assertEquals($tag->id, $found1->id);
        $this->assertEquals($tag->id, $found2->id);
        $this->assertEquals($tag->id, $found3->id);

        // 4. Lookup non-existent tag
        $newTag = Tag::findOrCreateByName('jasa las');
        $this->assertDatabaseHas('tags', ['name' => 'jasa las', 'slug' => 'jasa-las']);

        // 5. Lookup the newly created tag space-insensitive
        $foundNew = Tag::findOrCreateByName('jasalas');
        $this->assertEquals($newTag->id, $foundNew->id);
    }

    public function test_admin_can_access_tag_deduplication_pages_and_trigger_merge(): void
    {
        // 1. Guest cannot access
        $this->get('/admin/tags/deduplicate')->assertRedirect('/login');

        // 2. Non-admin user cannot access and is redirected to home
        $this->actingAs($this->user);
        $this->get('/admin/tags/deduplicate')->assertRedirect('/');

        // Let's make the user an admin
        $this->user->update(['is_admin' => true]);

        // 3. Admin can access the view
        $response = $this->get('/admin/tags/deduplicate');
        $response->assertStatus(200);
        $response->assertSee('Bersihkan Tagar Duplikat');

        // 4. Admin can trigger deduplication via POST
        $postResponse = $this->post('/admin/tags/deduplicate');
        $postResponse->assertRedirect('/admin/tags/deduplicate');
        $postResponse->assertSessionHas('success');
    }
}
