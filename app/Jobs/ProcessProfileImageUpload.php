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
        $finalPath = $this->tempPath;

        // Safety Net: If not found, try adding /private/ to the path
        if (!file_exists($finalPath)) {
            $altPath = str_replace('/storage/app/temp_uploads/', '/storage/app/private/temp_uploads/', $finalPath);
            if (file_exists($altPath)) {
                $finalPath = $altPath;
            }
        }

        if (!file_exists($finalPath)) {
            Log::warning("ProcessProfileImageUpload: Temp file not found at {$finalPath}");
            return;
        }

        try {
            $imageService->uploadProfilePhotoFromPath(
                $finalPath, 
                $this->fileName, 
                $this->userId
            );
        } catch (\Exception $e) {
            Log::error("ProcessProfileImageUpload Error: " . $e->getMessage());
            throw $e;
        } finally {
            // Clean up temp file
            if (file_exists($finalPath)) {
                unlink($finalPath);
            }
        }
    }
}
