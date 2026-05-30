<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateTagsWebhookTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $listing;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a standard user
        $this->user = User::create([
            'name' => 'John Doe',
            'whatsapp' => '6281234567890',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a listing
        $this->listing = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Toko Besi Makmur',
            'slug' => 'toko-besi-makmur',
            'description' => 'Jual teralis besi dan pagar berkualitas.',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_validates_required_parameters()
    {
        $response = $this->postJson(route('webhook.generate-tags'), []);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['id', 'tags']);
    }

    /** @test */
    public function it_processes_and_saves_comma_separated_tags_successfully()
    {
        $response = $this->postJson(route('webhook.generate-tags'), [
            'id' => $this->listing->id,
            'tags' => 'teralis, besi , pagar, baja ringan',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'listing_id' => $this->listing->id,
            'tags' => ['teralis', 'besi', 'pagar', 'baja ringan'],
        ]);

        // Verify tags are created
        $this->assertSame(4, Tag::count());
        $this->assertTrue(Tag::where('slug', 'teralis')->exists());
        $this->assertTrue(Tag::where('slug', 'baja-ringan')->exists());

        // Verify listing is attached to these tags
        $this->assertSame(4, $this->listing->fresh()->tags->count());
        $this->assertSame(
            ['teralis', 'besi', 'pagar', 'baja-ringan'],
            $this->listing->fresh()->tags->pluck('slug')->toArray()
        );
    }

    /** @test */
    public function it_supports_listing_id_key_as_alternative_to_id()
    {
        $response = $this->postJson(route('webhook.generate-tags'), [
            'listing_id' => $this->listing->id,
            'tags' => 'las, stainless',
        ]);

        $response->assertOk();
        $this->assertSame(2, $this->listing->fresh()->tags->count());
    }

    /** @test */
    public function it_overwrites_existing_tags_completely()
    {
        // First attach some tags to the listing
        $tag1 = Tag::create(['name' => 'Lama1', 'slug' => 'lama1']);
        $tag2 = Tag::create(['name' => 'Lama2', 'slug' => 'lama2']);
        $this->listing->tags()->attach([$tag1->id, $tag2->id]);

        $this->assertSame(2, $this->listing->fresh()->tags->count());

        // Call the webhook with new tags
        $response = $this->postJson(route('webhook.generate-tags'), [
            'id' => $this->listing->id,
            'tags' => 'Baru1, Baru2',
        ]);

        $response->assertOk();

        // Old tags must be detached
        $freshListing = $this->listing->fresh();
        $this->assertSame(2, $freshListing->tags->count());
        $this->assertSame(
            ['baru1', 'baru2'],
            $freshListing->tags->pluck('slug')->toArray()
        );
    }

    /** @test */
    public function it_updates_searchable_field_on_listing()
    {
        // Update description to not contain "teralis" initially
        $this->listing->update(['description' => 'Jual barang berkualitas.']);
        $this->listing->updateSearchableField();
        
        $this->assertStringNotContainsString('teralis', $this->listing->fresh()->searchable);

        $this->postJson(route('webhook.generate-tags'), [
            'id' => $this->listing->id,
            'tags' => 'teralis',
        ]);

        // Searchable field must contain the new tag
        $this->assertStringContainsString('teralis', $this->listing->fresh()->searchable);
    }
}
