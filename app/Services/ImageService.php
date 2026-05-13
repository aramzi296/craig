<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use App\Models\ListingPhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Upload listing photo to local public storage.
     */
    public function uploadListingPhoto(UploadedFile $file, int $listingId, string $collection = 'foto_fitur', array $meta = [])
    {
        $folder = "listings/{$listingId}";
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs($folder, $fileName, 'public');

        return ListingPhoto::create([
            'listing_id' => $listingId,
            'photo_path' => $path,
            'thumbnail_path' => $path, 
            'file_type' => isset($file) && is_object($file) && method_exists($file, 'getMimeType') ? $file->getMimeType() : (isset($filePath) ? File::mimeType($filePath) : null),
            'file_size' => isset($file) && is_object($file) && method_exists($file, 'getSize') ? $file->getSize() : (isset($filePath) ? File::size($filePath) : 0),
            'collection' => $collection,
            'meta' => array_merge($meta, [
                'original_name' => isset($file) && is_object($file) && method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : ($fileName ?? 'unknown'),
            ]),
        ]);
    }

    /**
     * Upload profile photo to local public storage.
     */
    public function uploadProfilePhoto(UploadedFile $file, int $userId)
    {
        $folder = "foto_profil";
        $fileName = "user_{$userId}_" . bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs($folder, $fileName, 'public');

        return [
            'path' => $path,
            'fileId' => null
        ];
    }

    /**
     * Upload file from path (for bot usage).
     */
    public function uploadFromPath(string $filePath, string $fileName, string $folder)
    {
        $content = File::get($filePath);
        $path = "{$folder}/{$fileName}";
        
        Storage::disk('public')->put($path, $content);

        return (object)[
            'filePath' => $path,
            'fileId' => null
        ];
    }

    /**
     * Upload listing photo from a local path (for queued uploads).
     */
    public function uploadListingPhotoFromPath(string $filePath, string $fileName, int $listingId, string $collection = 'foto_fitur', array $meta = [])
    {
        $folder = "listings/{$listingId}";
        $content = File::get($filePath);
        $path = "{$folder}/{$fileName}";

        Storage::disk('public')->put($path, $content);

        return ListingPhoto::create([
            'listing_id' => $listingId,
            'photo_path' => $path,
            'thumbnail_path' => $path, 
            'file_type' => isset($file) && is_object($file) && method_exists($file, 'getMimeType') ? $file->getMimeType() : (isset($filePath) ? File::mimeType($filePath) : null),
            'file_size' => isset($file) && is_object($file) && method_exists($file, 'getSize') ? $file->getSize() : (isset($filePath) ? File::size($filePath) : 0),
            'collection' => $collection,
            'meta' => array_merge($meta, [
                'original_name' => isset($file) && is_object($file) && method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : ($fileName ?? 'unknown'),
            ]),
        ]);
    }

    /**
     * Upload profile photo from a local path (for queued uploads).
     */
    public function uploadProfilePhotoFromPath(string $filePath, string $fileName, int $userId)
    {
        $folder = "foto_profil";
        $content = File::get($filePath);
        $path = "{$folder}/{$fileName}";

        Storage::disk('public')->put($path, $content);

        // Update User
        $user = \App\Models\User::find($userId);
        if ($user) {
            $user->update([
                'profile_photo' => $path,
                'ik_file_id' => null
            ]);
        }

        return [
            'path' => $path,
            'fileId' => null
        ];
    }

    /**
     * Delete file from local storage.
     */
    public function deleteFileById(string $fileId)
    {
        // For local storage, we don't use fileId but we might want to delete by path
        // But since the controller calls this with ik_file_id, we might need a different approach
        // or just rely on the fact that for now we are not passing path here.
        // Actually, let's update ListingController to pass photo_path instead?
        // Or just do nothing here and handle deletion in model/controller directly.
    }

    /**
     * Delete file by path.
     */
    public function deleteByPath(string $path)
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Compress large images (> 200KB)
     */
    public function compressLargeImages(int $limitKB = 100, int $maxItems = 100)
    {
        $limitBytes = $limitKB * 1024;
        $photos = ListingPhoto::where('file_size', '>', $limitBytes)
            ->take($maxItems)
            ->get();
        
        $count = 0;
        foreach ($photos as $photo) {
            @set_time_limit(30); // Reset timer for each image
            $fullPath = storage_path('app/public/' . ltrim($photo->photo_path, '/'));
            
            if (File::exists($fullPath)) {
                $originalPath = $photo->photo_path;
                $extension = File::extension($fullPath);
                $directory = File::dirname($fullPath);
                $filename = File::name($fullPath);
                
                // Check if it's already a compressed version to avoid double suffix
                $targetFilename = str_replace('_compressed', '', $filename);
                $newName = $targetFilename . '_compressed.webp';
                $newFullPath = $directory . DIRECTORY_SEPARATOR . $newName;
                
                // Ensure the path is relative to storage/app/public
                $publicPathBase = storage_path('app/public/');
                $newRelativePath = str_replace($publicPathBase, '', $newFullPath);
                $newRelativePath = str_replace('\\', '/', $newRelativePath);

                try {
                    // Start compression
                    $image = Image::read($fullPath);
                    
                    // Progressive reduction of quality until < limitBytes
                    $quality = 80;
                    $image->save($newFullPath, quality: $quality);
                    
                    while (File::size($newFullPath) > $limitBytes && $quality > 10) {
                        $quality -= 10;
                        $image->save($newFullPath, quality: $quality);
                    }

                    // If still too big, resize further
                    if (File::size($newFullPath) > $limitBytes) {
                        $image->scale(width: 1000); 
                        $image->save($newFullPath, quality: 50);
                    }

                    // Generate Thumbnail (200px width)
                    $thumbName = $targetFilename . '_thumb.webp';
                    $thumbFullPath = $directory . DIRECTORY_SEPARATOR . $thumbName;
                    $thumbRelativePath = str_replace($publicPathBase, '', $thumbFullPath);
                    $thumbRelativePath = str_replace('\\', '/', $thumbRelativePath);

                    $thumb = Image::read($fullPath);
                    $thumb->scale(width: 200);
                    $thumb->save($thumbFullPath, quality: 70);

                    // Update meta and paths
                    $meta = $photo->meta ?? [];
                    $meta['original_path_before_compression'] = $originalPath;
                    $meta['original_format'] = $extension;
                    $meta['compression_info'] = [
                        'date' => now()->toDateTimeString(),
                        'original_size' => $photo->file_size,
                        'new_size' => File::size($newFullPath),
                        'quality' => $quality,
                        'format' => 'webp'
                    ];

                    $photo->update([
                        'photo_path' => $newRelativePath,
                        'thumbnail_path' => $thumbRelativePath, 
                        'file_size' => File::size($newFullPath),
                        'file_type' => 'image/webp',
                        'meta' => $meta
                    ]);

                    $count++;
                } catch (\Exception $e) {
                    \Log::error("Failed to compress photo ID {$photo->id}: " . $e->getMessage());
                }
            }
        }
        
        return $count;
    }
}
