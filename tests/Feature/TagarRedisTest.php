<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class TagarRedisTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear Redis cache before each test
        try {
            Cache::store('redis')->flush();
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis is not available: ' . $e->getMessage());
        }

        // Create a test user for associations
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'whatsapp' => '628123456789',
        ]);
    }

    public function test_categories_index_loads_successfully_and_caches_in_redis(): void
    {
        // 1. Create a Tag and an active, non-expired Listing associated with it
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

        // Verify Redis key does not exist yet
        $this->assertFalse(Cache::store('redis')->has('tags:approved_with_listings'));

        // 2. Access the /tagar endpoint
        $response = $this->get('/tagar');
        $response->assertStatus(200);
        $response->assertSee('Baja Ringan');

        // 3. Verify Redis key is now cached
        $this->assertTrue(Cache::store('redis')->has('tags:approved_with_listings'));

        $cachedTags = Cache::store('redis')->get('tags:approved_with_listings');
        $this->assertCount(1, $cachedTags);
        $this->assertEquals('Baja Ringan', $cachedTags->first()->name);
    }

    public function test_categories_search_returns_json_and_caches_in_redis_hash(): void
    {
        $tag1 = Tag::create(['name' => 'Baja Ringan', 'slug' => 'baja-ringan', 'is_approved' => true]);
        $tag2 = Tag::create(['name' => 'Teralis', 'slug' => 'teralis', 'is_approved' => true]);

        $listing = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Jasa Bengkel Las',
            'slug' => 'jasa-bengkel-las',
            'description' => 'Mengerjakan baja ringan dan teralis besi.',
            'is_active' => true,
            'expires_at' => now()->addDays(10),
        ]);

        $listing->tags()->attach([$tag1->id, $tag2->id]);

        // Populate main cache
        $this->get('/tagar');

        // Verify Redis Hash is empty
        $redis = Redis::connection('cache');
        $this->assertFalse($redis->hexists('laravel-cache-tags:searches', 'baja'));

        // Perform AJAX search
        $response = $this->getJson('/tagar?q=baja');
        $response->assertStatus(200);

        // Verify search result contains only Baja Ringan
        $data = $response->json();
        $this->assertCount(1, $data['categories']);
        $this->assertEquals('Baja Ringan', $data['categories'][0]['name']);

        // Verify result is cached in Redis Hash
        $this->assertTrue($redis->hexists('laravel-cache-tags:searches', 'baja'));
        $cachedSearch = json_decode($redis->hget('laravel-cache-tags:searches', 'baja'), true);
        $this->assertCount(1, $cachedSearch);
        $this->assertEquals('Baja Ringan', $cachedSearch[0]['name']);
    }

    public function test_cache_is_invalidated_when_tag_is_updated_or_deleted(): void
    {
        $tag = Tag::create(['name' => 'Baja Ringan', 'slug' => 'baja-ringan', 'is_approved' => true]);
        $listing = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Test',
            'slug' => 'test',
            'description' => 'Some test listing description',
            'is_active' => true
        ]);
        $listing->tags()->attach($tag->id);

        // Populate cache
        $this->get('/tagar');
        $this->getJson('/tagar?q=baja');

        $redis = Redis::connection('cache');
        $this->assertTrue(Cache::store('redis')->has('tags:approved_with_listings'));
        $this->assertTrue($redis->hexists('laravel-cache-tags:searches', 'baja'));

        // Update tag
        $tag->update(['name' => 'Baja Ringan Premium']);

        // Verify cache is cleared
        $this->assertFalse(Cache::store('redis')->has('tags:approved_with_listings'));
        $this->assertEquals(0, $redis->exists('laravel-cache-tags:searches'));
    }

    public function test_cache_is_invalidated_when_listing_is_updated_or_deleted(): void
    {
        $tag = Tag::create(['name' => 'Baja Ringan', 'slug' => 'baja-ringan', 'is_approved' => true]);
        $listing = Listing::create([
            'user_id' => $this->user->id,
            'title' => 'Test',
            'slug' => 'test',
            'description' => 'Some test listing description',
            'is_active' => true
        ]);
        $listing->tags()->attach($tag->id);

        // Populate cache
        $this->get('/tagar');
        $this->getJson('/tagar?q=baja');

        $redis = Redis::connection('cache');
        $this->assertTrue(Cache::store('redis')->has('tags:approved_with_listings'));
        $this->assertTrue($redis->hexists('laravel-cache-tags:searches', 'baja'));

        // Update listing
        $listing->update(['title' => 'Test Updated']);

        // Verify cache is cleared
        $this->assertFalse(Cache::store('redis')->has('tags:approved_with_listings'));
        $this->assertEquals(0, $redis->exists('laravel-cache-tags:searches'));
    }

    public function test_home_search_returns_matching_tags_in_view(): void
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
}
