<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    /**
     * Clear specific cache keys when a category is modified.
     */
    protected function clearCache()
    {
        Cache::store('redis')->forget('categories:directory_with_counts');
        Cache::store('redis')->forget('categories:form_dropdown');
    }

    public function created(Category $category): void
    {
        $this->clearCache();
    }

    public function updated(Category $category): void
    {
        $this->clearCache();
    }

    public function deleted(Category $category): void
    {
        $this->clearCache();
    }

    public function restored(Category $category): void
    {
        $this->clearCache();
    }

    public function forceDeleted(Category $category): void
    {
        $this->clearCache();
    }
}
