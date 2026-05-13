<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CompressLargeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:compress-images {limit=200}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compress listing photos that are larger than the specified limit (KB)';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\ImageService $imageService)
    {
        $limit = (int) $this->argument('limit');
        $this->info("Starting compression for images larger than {$limit}KB...");
        
        // CLI can handle more, let's do 500 at a time
        $count = $imageService->compressLargeImages($limit, 500);
        
        $this->info("Finished! Total images compressed: {$count}");
    }
}
