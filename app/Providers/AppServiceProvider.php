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

        \Illuminate\Support\Facades\View::share('globalTags', \App\Models\Tag::orderBy('sort_order')->get());
        \Illuminate\Support\Facades\View::share('globalListingTypes', \App\Models\ListingType::orderBy('sort_order')->get());
    }
}
