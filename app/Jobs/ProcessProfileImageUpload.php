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

class ProcessProfileImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tempPath;
    protected $userId;
    protected $fileName;

    /**
     * Create a new job instance.
     */
    public function __construct(string $tempPath, int $userId, string $fileName)
    {
        $this->tempPath = $tempPath;
        $this->userId = $userId;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     */
    public function handle(ImageService $imageService): void
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        
        if (!$disk->exists($this->tempPath)) {
            Log::warning("ProcessProfileImageUpload: Temp file not found at {$this->tempPath} on public disk");
            return;
        }

        $fullPath = $disk->path($this->tempPath);

        try {
            $imageService->uploadProfilePhotoFromPath(
                $fullPath, 
                $this->fileName, 
                $this->userId
            );
        } catch (\Exception $e) {
            Log::error("ProcessProfileImageUpload Error: " . $e->getMessage());
            throw $e;
        } finally {
            // Clean up temp file
            if ($disk->exists($this->tempPath)) {
                $disk->delete($this->tempPath);
            }
        }
    }
}
