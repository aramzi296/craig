<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SyncSearchVector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize PostgreSQL full-text search vectors for all listings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!Schema::hasColumn('listings', 'search_vector')) {
            $this->error('The "search_vector" column does not exist. Please run "php artisan migrate" first.');
            return 1;
        }

        $this->info('Starting search vector synchronization...');

        $total = Listing::count();
        $bar = $this->output->createProgressBar($total);

        Listing::query()->chunk(100, function ($listings) use ($bar) {
            foreach ($listings as $listing) {
                // calls the updateSearchVector() method we added to the Listing model
                $listing->updateSearchVector();
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('Search vector synchronization completed successfuly.');

        return 0;
    }
}
