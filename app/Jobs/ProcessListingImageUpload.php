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
        $disk = \Illuminate\Support\Facades\Storage::disk('local');
        
        if (!$disk->exists($this->tempPath)) {
            Log::warning("ProcessListingImageUpload: Temp file not found at {$this->tempPath} on local disk");
            return;
        }

        $fullPath = $disk->path($this->tempPath);

        try {
            $imageService->uploadListingPhotoFromPath(
                $fullPath, 
                $this->fileName, 
                $this->listingId, 
                $this->collection
            );
        } catch (\Exception $e) {
            Log::error("ProcessListingImageUpload Error: " . $e->getMessage());
            throw $e;
        } finally {
            // Clean up temp file
            if ($disk->exists($this->tempPath)) {
                $disk->delete($this->tempPath);
            }
        }
    }
}
