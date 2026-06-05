<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        \App\Models\Listing::observe(\App\Observers\ListingObserver::class);

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('globalTags', \App\Models\Tag::orderBy('sort_order')->get());
            
            if (request()->is('admin*')) {
                try {
                    $view->with('uncategorizedListingsCount', \App\Models\Listing::doesntHave('categories')->count());
                    $view->with('untaggedListingsCount', \App\Models\Listing::doesntHave('tags')->count());
                } catch (\Exception $e) {
                    // Fail silently during migrations/seeding
                }
            }
        });
    }
}
