<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

class MigrateMediaFromR2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-media-from-r2 {--disk=public : Target disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate media files from R2 to local storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDisk = $this->option('disk');
        $medias = Media::where('disk', 'r2')->get();

        $this->info("Found " . $medias->count() . " media items on R2 disk.");

        $bar = $this->output->createProgressBar($medias->count());
        $bar->start();

        foreach ($medias as $media) {
            try {
                // Determine folder path (Spatie default is media->id)
                // However, more robust is to use getPath() or similar if possible, 
                // but we need the relative path for Storage::disk()
                
                $id = $media->id;
                $fileName = $media->file_name;
                $folderPath = $id; 
                $originalFilePath = "{$folderPath}/{$fileName}";

                if (Storage::disk('r2')->exists($originalFilePath)) {
                    $content = Storage::disk('r2')->get($originalFilePath);
                    Storage::disk($targetDisk)->put($originalFilePath, $content);
                    
                    // Migrate conversions
                    $conversions = $media->generated_conversions ?? [];
                    foreach ($conversions as $conversionName => $status) {
                        if ($status) {
                            // Spatie conversion path is usually $id/conversions/$conversion-name.$ext
                            // But it might vary. We can check the directory.
                            $conversionFolder = "{$id}/conversions";
                            $files = Storage::disk('r2')->files($conversionFolder);
                            
                            foreach ($files as $file) {
                                $conversionContent = Storage::disk('r2')->get($file);
                                Storage::disk($targetDisk)->put($file, $conversionContent);
                            }
                        }
                    }

                    // For responsive images
                    if ($media->responsive_images) {
                        $responsiveFolder = "{$id}/responsive-images";
                        $files = Storage::disk('r2')->files($responsiveFolder);
                        foreach ($files as $file) {
                            $responsiveContent = Storage::disk('r2')->get($file);
                            Storage::disk($targetDisk)->put($file, $responsiveContent);
                        }
                    }

                    // Update DB
                    $media->update([
                        'disk' => $targetDisk,
                        'conversions_disk' => $targetDisk
                    ]);
                } else {
                    $this->error("\nFile not found on R2: {$originalFilePath}");
                }
            } catch (\Exception $e) {
                $this->error("\nError migrating media ID {$media->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nMigration completed.");
    }
}
