<?php

namespace App\Jobs;

use App\Services\ImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcessListingImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tempPath;
    protected $listingId;
    protected $collection;
    protected $fileName;

    /**
     * Create a new job instance.
     */
    public function __construct(string $tempPath, int $listingId, string $collection, string $fileName)
    {
        $this->tempPath = $tempPath;
        $this->listingId = $listingId;
        $this->collection = $collection;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     */
    public function handle(ImageService $imageService): void
    {
        $finalPath = $this->tempPath;

        // Safety Net: If not found, try adding /private/ to the path
        if (!file_exists($finalPath)) {
            $altPath = str_replace('/storage/app/temp_uploads/', '/storage/app/private/temp_uploads/', $finalPath);
            if (file_exists($altPath)) {
                $finalPath = $altPath;
            }
        }
        
        if (!file_exists($finalPath)) {
            Log::warning("ProcessListingImageUpload: Temp file not found at {$finalPath}");
            return;
        }

        try {
            $imageService->uploadListingPhotoFromPath(
                $finalPath, 
                $this->fileName, 
                $this->listingId, 
                $this->collection
            );
        } catch (\Exception $e) {
            Log::error("ProcessListingImageUpload Error: " . $e->getMessage());
            throw $e;
        } finally {
            // Clean up temp file
            if (file_exists($this->tempPath)) {
                unlink($this->tempPath);
            }
        }
    }
}
