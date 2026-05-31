<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order', 'is_approved'];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    protected static function booted()
    {
        $clearCache = function () {
            try {
                $redisStore = \Illuminate\Support\Facades\Cache::store('redis');
                $redisStore->forget('tags:approved_with_listings');
                $redisStore->forget('tags:searches');
            } catch (\Exception $e) {
                // Prevent app from crashing if Redis connection fails
            }
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function listings()
    {
        return $this->belongsToMany(Listing::class);
    }

    /**
     * Find an existing tag by name (case-insensitive and space-insensitive)
     * or create a new tag if not found.
     *
     * @param string $name
     * @param bool $isApproved
     * @return Tag
     */
    public static function findOrCreateByName(string $name, bool $isApproved = false): Tag
    {
        $tagName = trim($name);
        $slug = \Illuminate\Support\Str::slug($tagName);
        $normalizedInput = str_replace(' ', '', strtolower($tagName));

        // Find existing tag using normalized comparison (ignoring case and spaces)
        $tag = self::whereRaw("REPLACE(LOWER(name), ' ', '') = ?", [$normalizedInput])
            ->orWhere('slug', $slug)
            ->first();

        if (!$tag) {
            $tag = self::create([
                'name' => $tagName,
                'slug' => $slug,
                'icon' => 'fa-solid fa-tag',
                'sort_order' => (int)self::max('sort_order') + 1,
                'is_approved' => $isApproved,
            ]);
        }

        return $tag;
    }

    /**
     * Deduplicate tags by normalizing their names (lowercasing and removing spaces).
     * Merges duplicate tags, re-associates listings, preserves icons and approvals,
     * updates affected listing searchable fields, and deletes duplicate tags.
     *
     * @return array Summary of merged tags
     */
    public static function deduplicate(): array
    {
        return \DB::transaction(function () {
            $allTags = self::all();
            
            // Group tags by normalized name (lowercase, no spaces)
            $grouped = $allTags->groupBy(function ($tag) {
                return str_replace(' ', '', strtolower($tag->name));
            });
            
            $summary = [];
            
            foreach ($grouped as $normalizedName => $group) {
                if ($group->count() <= 1) {
                    continue;
                }
                
                // Sort to determine the primary tag to keep:
                // 1. is_approved (true first)
                // 2. listings count (highest first)
                // 3. name has space (space first, e.g. "rental mobil" > "rentalmobil")
                // 4. oldest tag (lowest ID) first
                $sorted = $group->sort(function ($a, $b) {
                    if ($a->is_approved !== $b->is_approved) {
                        return $b->is_approved <=> $a->is_approved;
                    }
                    
                    $aCount = $a->listings()->count();
                    $bCount = $b->listings()->count();
                    if ($aCount !== $bCount) {
                        return $bCount <=> $aCount;
                    }
                    
                    $aHasSpace = strpos($a->name, ' ') !== false;
                    $bHasSpace = strpos($b->name, ' ') !== false;
                    if ($aHasSpace !== $bHasSpace) {
                        return $bHasSpace <=> $aHasSpace;
                    }
                    
                    return $a->id <=> $b->id;
                });
                
                $primaryTag = $sorted->first();
                $duplicateTags = $sorted->slice(1);
                
                $mergedNames = $duplicateTags->pluck('name')->toArray();
                
                // 1. Merge approval status
                if (!$primaryTag->is_approved) {
                    foreach ($duplicateTags as $dup) {
                        if ($dup->is_approved) {
                            $primaryTag->is_approved = true;
                            break;
                        }
                    }
                }
                
                // 2. Merge icon
                if (is_null($primaryTag->icon)) {
                    foreach ($duplicateTags as $dup) {
                        if (!is_null($dup->icon)) {
                            $primaryTag->icon = $dup->icon;
                            break;
                        }
                    }
                }
                
                $primaryTag->save();
                
                // 3. Re-associate listings
                $duplicateTagIds = $duplicateTags->pluck('id')->toArray();
                $listingsToMigrate = \DB::table('listing_tag')
                    ->whereIn('tag_id', $duplicateTagIds)
                    ->pluck('listing_id')
                    ->unique();
                    
                $existingListingIds = \DB::table('listing_tag')
                    ->where('tag_id', $primaryTag->id)
                    ->pluck('listing_id')
                    ->toArray();
                    
                $listingIdsToAttach = $listingsToMigrate->diff($existingListingIds);
                
                if ($listingIdsToAttach->isNotEmpty()) {
                    $insertData = $listingIdsToAttach->map(function ($listingId) use ($primaryTag) {
                        return [
                            'tag_id' => $primaryTag->id,
                            'listing_id' => $listingId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    })->toArray();
                    
                    \DB::table('listing_tag')->insert($insertData);
                }
                
                // 4. Delete old relations for duplicate tags
                \DB::table('listing_tag')->whereIn('tag_id', $duplicateTagIds)->delete();
                
                // 5. Delete the duplicate tags
                self::whereIn('id', $duplicateTagIds)->delete();
                
                // 6. Update searchable field on affected listings
                $affectedListingIds = \DB::table('listing_tag')
                    ->where('tag_id', $primaryTag->id)
                    ->pluck('listing_id')
                    ->unique();
                    
                foreach ($affectedListingIds as $listingId) {
                    $listing = Listing::find($listingId);
                    if ($listing) {
                        $listing->updateSearchableField();
                    }
                }
                
                $summary[] = [
                    'primary_tag' => [
                        'id' => $primaryTag->id,
                        'name' => $primaryTag->name,
                    ],
                    'merged_tags' => $mergedNames,
                    'listings_affected' => $affectedListingIds->count(),
                ];
            }
            
            // Clear Redis cache
            try {
                $redisStore = \Illuminate\Support\Facades\Cache::store('redis');
                $redisStore->forget('tags:approved_with_listings');
                $redisStore->forget('tags:searches');
            } catch (\Exception $e) {
                // Prevent failure if redis isn't configured/running
            }
            
            return $summary;
        });
    }
}
