<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $matchingTags = collect();

        if ($request->filled('q') && strlen(trim($request->q)) >= 2) {
            $q = $request->q;
            $scoutQuery = \App\Models\Listing::search($q, function ($meilisearch, $query, $options) {
                $options['matchingStrategy'] = 'all';
                return $meilisearch->search($query, $options);
            })->where('is_active', true);

            if ($request->filled('location')) {
                $scoutQuery->where('district_id', (int)$request->location);
            }

            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    if ($category->parent_id === null) {
                        $subCategoryIds = $category->children()->pluck('id')->push($category->id)->toArray();
                        $scoutQuery->whereIn('categories', $subCategoryIds);
                    } else {
                        $scoutQuery->whereIn('categories', [$category->id]);
                    }
                }
            }

            if ($request->has('tags') && is_array($request->tags)) {
                $tags = \App\Models\Tag::whereIn('slug', $request->tags)->get();
                if ($tags->count() > 0) {
                    foreach ($tags as $tag) {
                        $scoutQuery->where('tags', $tag->id);
                    }
                }
            } elseif ($request->filled('tag')) {
                $tag = \App\Models\Tag::where('slug', $request->tag)->first();
                if ($tag) {
                    $scoutQuery->whereIn('tags', [$tag->id]);
                }
            }

            $scoutQuery->query(function ($builder) {
                $builder->with('district')->notExpired();
            });

            $recentListings = $scoutQuery->paginate(24);

            $cleanQuery = strtolower(trim($q));
            try {
                $redisStore = \Illuminate\Support\Facades\Cache::store('redis');
                $redis = \Illuminate\Support\Facades\Redis::connection('cache');
                
                // Coba ambil dari Redis Hash
                $cachedSearch = $redis->hget('laravel-cache-tags:searches', $cleanQuery);

                if ($cachedSearch) {
                    $categoriesData = json_decode($cachedSearch, true);
                    $matchingTags = \App\Models\Tag::hydrate($categoriesData);
                } else {
                    // Ambil daftar utama dari Redis, jika tidak ada baru query DB
                    $allTags = $redisStore->remember('tags:approved_with_listings', 3600, function() {
                        return \App\Models\Tag::whereRaw('is_approved = true')
                            ->whereHas('listings', function($q) {
                                $q->whereRaw('is_active = true')->notExpired();
                            })
                            ->orderBy('name')
                            ->get();
                    });

                    $words = array_filter(explode(' ', $cleanQuery));

                    $filteredTags = $allTags->filter(function($tag) use ($words) {
                        $name = strtolower($tag->name);
                        $slug = strtolower($tag->slug);
                        foreach ($words as $word) {
                            if (!str_contains($name, $word) && !str_contains($slug, $word)) {
                                return false; // Ada satu kata yang tidak cocok
                            }
                        }
                        return true; // Semua kata cocok
                    })->values();

                    // Simpan hasil filter ke Redis Hash
                    $redis->hset('laravel-cache-tags:searches', $cleanQuery, json_encode($filteredTags->toArray()));
                    
                    // Set expiry pada hash jika baru dibuat (misal 10 menit)
                    if ($redis->ttl('laravel-cache-tags:searches') === -1) {
                        $redis->expire('laravel-cache-tags:searches', 600);
                    }

                    $matchingTags = $filteredTags;
                }
            } catch (\Exception $e) {
                // Fallback to direct DB query if Redis connection/driver fails
                $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
                $matchingTagsQuery = \App\Models\Tag::whereRaw('is_approved = true')
                    ->whereHas('listings', function($q) {
                        $q->whereRaw('is_active = true')->notExpired();
                    });

                $words = array_filter(explode(' ', $cleanQuery));

                if ($driver === 'pgsql') {
                    foreach ($words as $word) {
                        $matchingTagsQuery->where(function($queryBuilder) use ($word) {
                            $queryBuilder->where('name', 'ilike', "%{$word}%")
                                         ->orWhere('slug', 'ilike', "%{$word}%");
                        });
                    }
                    $matchingTags = $matchingTagsQuery->orderBy('name')->get();
                } else {
                    // SQLite/MySQL fallback: query with LIKE, then filter with str_contains in PHP
                    foreach ($words as $word) {
                        $matchingTagsQuery->where(function($queryBuilder) use ($word) {
                            $queryBuilder->where('name', 'like', "%{$word}%")
                                         ->orWhere('slug', 'like', "%{$word}%");
                        });
                    }
                    $dbTags = $matchingTagsQuery->orderBy('name')->get();

                    $matchingTags = $dbTags->filter(function($tag) use ($words) {
                        $name = strtolower($tag->name);
                        $slug = strtolower($tag->slug);
                        foreach ($words as $word) {
                            if (!str_contains($name, $word) && !str_contains($slug, $word)) {
                                return false;
                            }
                        }
                        return true;
                    })->values();
                }
            }
        } else {
            $query = \App\Models\Listing::query()->whereRaw('is_active = true')->notExpired();

            if ($request->filled('location')) {
                $query->where('district_id', (int)$request->location);
            }

            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    if ($category->parent_id === null) {
                        $subCategoryIds = $category->children()->pluck('id')->push($category->id);
                        $query->whereHas('categories', function($q) use ($subCategoryIds) {
                            $q->whereIn('categories.id', $subCategoryIds);
                        });
                    } else {
                        $query->whereHas('categories', function($q) use ($category) {
                            $q->where('categories.id', $category->id);
                        });
                    }
                }
            }

            if ($request->has('tags') && is_array($request->tags)) {
                $tagSlugs = $request->tags;
                $query->whereHas('tags', function($q) use ($tagSlugs) {
                    $q->whereIn('tags.slug', $tagSlugs);
                }, '=', count($tagSlugs));
            } elseif ($request->filled('tag')) {
                $tag = \App\Models\Tag::where('slug', $request->tag)->first();
                if ($tag) {
                    $query->whereHas('tags', function($q) use ($tag) {
                        $q->where('tags.id', $tag->id);
                    });
                }
            }
            
            $recentListings = $query->with('district')
                ->orderBy('created_at', 'desc')
                ->orderBy('is_premium', 'desc')
                ->orderBy('listing_rank', 'asc')
                ->paginate(24);
        }

        return view('home', compact('recentListings', 'matchingTags'));
    }


    public function show($slug)
    {
        // Find listing first to get ID
        $listingId = \App\Models\Listing::where('slug', $slug)->value('id');
        
        if ($listingId) {
            // Check if this IP has viewed this listing in the last 1 hour
            $recentView = \App\Models\ListingView::where('listing_id', $listingId)
                ->where('ip_address', request()->ip())
                ->where('created_at', '>', now()->subHour())
                ->exists();

            if (!$recentView) {
                // Record view in separate table for analytics
                \App\Models\ListingView::create([
                    'listing_id' => $listingId,
                    'ip_address' => request()->ip()
                ]);

                // Increment cache in listings table WITHOUT updating updated_at timestamp
                \Illuminate\Support\Facades\DB::table('listings')
                    ->where('id', $listingId)
                    ->increment('views_count');
            }
        }

        $code = request()->query('code');

        $query = \App\Models\Listing::with(['categories.parent', 'tags', 'photos', 'user', 'comments.user', 'district'])
            ->withCount('views')
            ->where('slug', $slug);

        if ($code) {
            $query->where('activation_code', $code);
        } else {
            $query->whereRaw('is_active = true')->notExpired();
        }

        $listing = $query->firstOrFail();

        $relatedListings = \App\Models\Listing::with(['categories', 'tags'])
            ->whereHas('categories', function($q) use ($listing) {
                $q->whereIn('categories.id', $listing->categories->pluck('id'));
            })
            ->where('id', '!=', $listing->id)
            ->whereRaw('is_active = true')
            ->notExpired()
            ->latest()
            ->take(6)
            ->get();

        $sidebarPremiumListings = \App\Models\Listing::with(['categories', 'tags'])
            ->whereRaw('is_premium = true')
            ->whereRaw('is_active = true')
            ->notExpired()
            ->where('id', '!=', $listing->id)
            ->inRandomOrder()
            ->take(5)
            ->get();

        return view('listings.show', compact('listing', 'relatedListings', 'sidebarPremiumListings'));
    }

    public function categoriesDirectory()
    {
        $categories = \Illuminate\Support\Facades\Cache::store('redis')->remember('categories:directory_with_counts', 3600, function() {
            $cats = \App\Models\Category::whereNull('parent_id')
                ->whereRaw('is_approved = true')
                ->with(['children' => function($query) {
                    $query->whereRaw('is_approved = true')
                        ->withCount(['listings' => function($q) {
                            $q->whereRaw('is_active = true')->notExpired();
                        }])
                        ->orderBy('sort_order')
                        ->orderBy('name');
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            // Count parent unique listings as the unique count of listings across parent and all its children
            foreach ($cats as $parent) {
                $categoryIds = $parent->children->pluck('id')->push($parent->id);
                $parent->listings_count = \App\Models\Listing::whereRaw('is_active = true')
                    ->notExpired()
                    ->whereHas('categories', function($q) use ($categoryIds) {
                        $q->whereIn('categories.id', $categoryIds);
                    })
                    ->count();
            }
            
            return $cats;
        });

        return view('categories.directory', compact('categories'));
    }

    public function categories(\Illuminate\Http\Request $request)
    {
        $redisStore = \Illuminate\Support\Facades\Cache::store('redis');
        $redis = \Illuminate\Support\Facades\Redis::connection('cache');
        $query = $request->query('q');

        if (!empty($query)) {
            $cleanQuery = strtolower(trim($query));
            
            try {
                // Coba ambil dari Redis Hash
                $cachedSearch = $redis->hget('laravel-cache-tags:searches', $cleanQuery);

                if ($cachedSearch) {
                    $categoriesData = json_decode($cachedSearch, true);
                    $categories = \App\Models\Tag::hydrate($categoriesData);
                } else {
                    // Ambil daftar utama dari Redis, jika tidak ada baru query DB
                    $allTags = $redisStore->remember('tags:approved_with_listings', 3600, function() {
                        return \App\Models\Tag::whereRaw('is_approved = true')
                            ->whereHas('listings', function($q) {
                                $q->whereRaw('is_active = true')->notExpired();
                            })
                            ->orderBy('name')
                            ->get();
                    });

                    $words = array_filter(explode(' ', $cleanQuery));

                    $filteredTags = $allTags->filter(function($tag) use ($words) {
                        $name = strtolower($tag->name);
                        $slug = strtolower($tag->slug);
                        foreach ($words as $word) {
                            if (!str_contains($name, $word) && !str_contains($slug, $word)) {
                                return false;
                            }
                        }
                        return true;
                    })->values();

                    // Simpan hasil filter ke Redis Hash
                    $redis->hset('laravel-cache-tags:searches', $cleanQuery, json_encode($filteredTags->toArray()));
                    
                    // Set expiry pada hash jika baru dibuat (misal 10 menit)
                    if ($redis->ttl('laravel-cache-tags:searches') === -1) {
                        $redis->expire('laravel-cache-tags:searches', 600);
                    }

                    $categories = $filteredTags;
                }
            } catch (\Exception $e) {
                // Fallback to direct DB query if Redis connection/driver fails
                $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
                $categoriesQuery = \App\Models\Tag::whereRaw('is_approved = true')
                    ->whereHas('listings', function($q) {
                        $q->whereRaw('is_active = true')->notExpired();
                    });

                $words = array_filter(explode(' ', $cleanQuery));

                if ($driver === 'pgsql') {
                    foreach ($words as $word) {
                        $categoriesQuery->where(function($queryBuilder) use ($word) {
                            $queryBuilder->where('name', 'ilike', "%{$word}%")
                                         ->orWhere('slug', 'ilike', "%{$word}%");
                        });
                    }
                    $categories = $categoriesQuery->orderBy('name')->get();
                } else {
                    foreach ($words as $word) {
                        $categoriesQuery->where(function($queryBuilder) use ($word) {
                            $queryBuilder->where('name', 'like', "%{$word}%")
                                         ->orWhere('slug', 'like', "%{$word}%");
                        });
                    }
                    $dbTags = $categoriesQuery->orderBy('name')->get();

                    $categories = $dbTags->filter(function($tag) use ($words) {
                        $name = strtolower($tag->name);
                        $slug = strtolower($tag->slug);
                        foreach ($words as $word) {
                            if (!str_contains($name, $word) && !str_contains($slug, $word)) {
                                return false;
                            }
                        }
                        return true;
                    })->values();
                }
            }
        } else {
            try {
                // Ambil daftar utama dari Redis
                $categories = $redisStore->remember('tags:approved_with_listings', 3600, function() {
                    return \App\Models\Tag::whereRaw('is_approved = true')
                        ->whereHas('listings', function($q) {
                            $q->whereRaw('is_active = true')->notExpired();
                        })
                        ->orderBy('name')
                        ->get();
                });
            } catch (\Exception $e) {
                // Fallback direct DB query if Redis fails
                $categories = \App\Models\Tag::whereRaw('is_approved = true')
                    ->whereHas('listings', function($q) {
                        $q->whereRaw('is_active = true')->notExpired();
                    })
                    ->orderBy('name')
                    ->get();
            }
            
            // Randomize and take 100 tags for initial view
            $categories = $categories->shuffle()->take(100)->values();
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'categories' => $categories
            ]);
        }

        return view('categories.index', compact('categories'));
    }

    public function userListings($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        $recentListings = \App\Models\Listing::query()
            ->where('user_id', $id)
            ->whereRaw('is_active = true')
            ->notExpired()
            ->orderBy('created_at', 'desc')
            ->orderBy('is_premium', 'desc')
            ->orderBy('listing_rank', 'asc')
            ->paginate(24);

        return view('home', [
            'recentListings' => $recentListings,
            'user' => $user,
            'isUserPage' => true
        ]);
    }

    public function tentang()
    {
        return view('tentang');
    }

    public function submitContact(Request $request, \App\Services\WhatsappService $whatsappService)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        $whatsapp = \App\Models\User::normalizeWhatsappNumber($data['whatsapp']) ?? $data['whatsapp'];

        // Save to database
        \App\Models\ContactMessage::create([
            'name' => $data['name'],
            'whatsapp' => $whatsapp,
            'message' => $data['message'],
            'status' => 'unread',
        ]);

        $adminNumber2 = config('services.whatsapp.admin_number_2');
        
        $text = "*PESAN KONTAK BARU*\n\n";
        $text .= "*Nama:* " . $data['name'] . "\n";
        $text .= "*WA:* " . $whatsapp . "\n";
        $text .= "*Pesan:* " . $data['message'];

        // Send WhatsApp notification as a best-effort background action
        $whatsappService->sendMessage($adminNumber2, $text);

        return back()->with('success', 'Pesan Anda berhasil dikirim dan disimpan. Terima kasih telah menghubungi kami!');
    }
}
