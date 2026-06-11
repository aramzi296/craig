<?php

namespace App\Observers;

use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class TagObserver
{
    /**
     * Clear specific cache keys when a tag is modified.
     */
    protected function clearCache()
    {
        Cache::store('redis')->forget('tags:global_list');
        Cache::store('redis')->forget('tags:approved_with_listings');
    }

    public function created(Tag $tag): void
    {
        $this->clearCache();
    }

    public function updated(Tag $tag): void
    {
        $this->clearCache();
    }

    public function deleted(Tag $tag): void
    {
        $this->clearCache();
    }

    public function restored(Tag $tag): void
    {
        $this->clearCache();
    }

    public function forceDeleted(Tag $tag): void
    {
        $this->clearCache();
    }
}
