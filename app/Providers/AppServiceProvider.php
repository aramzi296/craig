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
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Pagination\Paginator::useBootstrapFive();

        \App\Models\Listing::observe(\App\Observers\ListingObserver::class);
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        \App\Models\Tag::observe(\App\Observers\TagObserver::class);

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $globalTags = \Illuminate\Support\Facades\Cache::store('redis')->remember('tags:global_list', 3600, function() {
                return \App\Models\Tag::orderBy('sort_order')->get();
            });
            $view->with('globalTags', $globalTags);
            
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
