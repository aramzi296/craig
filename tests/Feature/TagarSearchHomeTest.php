<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagarSearchHomeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user for associations
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'whatsapp' => '628123456789',
        ]);
    }

    public function test_home_search_returns_matching_tags_in_view_with_db_fallback(): void
    {
        $tag = Tag::create([
            'name' => 'Baja Ringan',
            'slug' => 'baja-ringan',
            'is_approved' => true,
        ]);

        $listing = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Pemasangan Baja Ringan Murah',
            'slug' => 'pemasangan-baja-ringan-murah',
            'description' => 'Jasa pemasangan rangka atap baja ringan berkualitas.',
            'price' => 150000,
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        $listing->tags()->attach($tag->id);

        // Perform search request
        $response = $this->get('/?q=baja');
        $response->assertStatus(200);

        // Assert the view has 'matchingTags' variable and it contains the tag
        $response->assertViewHas('matchingTags', function($tags) use ($tag) {
            return $tags->contains('id', $tag->id);
        });

        // Assert tag name and "Tagar Terkait" text are rendered on the page
        $response->assertSee('Baja Ringan');
        $response->assertSee('Tagar Terkait');
    }

    public function test_home_search_requires_all_words_in_tag(): void
    {
        // Create tags
        $tagsToCreate = [
            'service ac' => true,
            'ac service' => true,
            'service ac bekas' => true,
            'terima service ac' => true,
            'service' => false, // should not match
            'ac' => false,      // should not match
            'baja ringan' => false // should not match
        ];

        $matchedTags = [];
        $unmatchedTags = [];

        foreach ($tagsToCreate as $name => $shouldMatch) {
            $tag = Tag::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'is_approved' => true
            ]);

            // Create an active listing for the tag
            $listing = Listing::create([
                'user_id' => $this->user->id,
                'title' => 'Listing for ' . $name,
                'slug' => 'listing-for-' . \Illuminate\Support\Str::slug($name),
                'description' => 'Description of listing',
                'is_active' => true
            ]);
            $listing->tags()->attach($tag->id);

            if ($shouldMatch) {
                $matchedTags[] = $tag;
            } else {
                $unmatchedTags[] = $tag;
            }
        }

        // Perform search request
        $response = $this->get('/?q=service ac');
        $response->assertStatus(200);

        // Assert all matched tags are in matchingTags
        $response->assertViewHas('matchingTags', function($tags) use ($matchedTags, $unmatchedTags) {
            foreach ($matchedTags as $tag) {
                if (!$tags->contains('id', $tag->id)) {
                    return false;
                }
            }
            foreach ($unmatchedTags as $tag) {
                if ($tags->contains('id', $tag->id)) {
                    return false;
                }
            }
            return true;
        });
    }
}
