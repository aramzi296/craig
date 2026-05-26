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
     * Upload listing photo to Cloudflare R2 storage.
     */
    public function uploadListingPhoto(UploadedFile $file, int $listingId, string $collection = 'foto_fitur', array $meta = [])
    {
        $folder = 'upload/' . date('Y') . '/' . date('m') . '/' . date('d');
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        
        $r2Path = Storage::disk('r2')->putFileAs($folder, $file, $fileName);
        
        $r2Url = rtrim(config('filesystems.disks.r2.url'), '/');
        $fullUrl = $r2Url . '/' . ltrim($r2Path, '/');

        return ListingPhoto::create([
            'listing_id' => $listingId,
            'photo_path' => $fullUrl,
            'thumbnail_path' => $fullUrl, 
            'file_type' => isset($file) && is_object($file) && method_exists($file, 'getMimeType') ? $file->getMimeType() : (isset($filePath) ? File::mimeType($filePath) : null),
            'file_size' => isset($file) && is_object($file) && method_exists($file, 'getSize') ? $file->getSize() : (isset($filePath) ? File::size($filePath) : 0),
            'collection' => $collection,
            'meta' => array_merge($meta, [
                'original_name' => isset($file) && is_object($file) && method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : ($fileName ?? 'unknown'),
            ]),
        ]);
    }

    /**
     * Upload profile photo directly to Cloudflare R2 storage.
     */
    public function uploadProfilePhoto(UploadedFile $file, int $userId)
    {
        $folder = 'upload/' . date('Y') . '/' . date('m') . '/' . date('d');
        $fileName = "user_{$userId}_" . bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();
        
        $r2Path = Storage::disk('r2')->putFileAs($folder, $file, $fileName);
        
        $r2Url = rtrim(config('filesystems.disks.r2.url'), '/');
        $fullUrl = $r2Url . '/' . ltrim($r2Path, '/');

        // Update User
        $user = \App\Models\User::find($userId);
        if ($user) {
            // Delete old profile photo
            if ($user->profile_photo) {
                $this->deleteByPath($user->profile_photo);
            }

            $user->update([
                'profile_photo' => $fullUrl,
                'ik_file_id' => null
            ]);
        }

        return [
            'path' => $fullUrl,
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
        
        Storage::disk('r2')->put($path, $content);

        $r2Url = rtrim(config('filesystems.disks.r2.url'), '/');
        $fullUrl = $r2Url . '/' . ltrim($path, '/');

        return (object)[
            'filePath' => $fullUrl,
            'fileId' => null
        ];
    }

    /**
     * Upload listing photo from a local path (for queued uploads).
     */
    public function uploadListingPhotoFromPath(string $filePath, string $fileName, int $listingId, string $collection = 'foto_fitur', array $meta = [])
    {
        $folder = 'upload/' . date('Y') . '/' . date('m') . '/' . date('d');
        $content = File::get($filePath);
        $path = "{$folder}/{$fileName}";

        Storage::disk('r2')->put($path, $content);

        $r2Url = rtrim(config('filesystems.disks.r2.url'), '/');
        $fullUrl = $r2Url . '/' . ltrim($path, '/');

        return ListingPhoto::create([
            'listing_id' => $listingId,
            'photo_path' => $fullUrl,
            'thumbnail_path' => $fullUrl, 
            'file_type' => File::mimeType($filePath),
            'file_size' => File::size($filePath),
            'collection' => $collection,
            'meta' => array_merge($meta, [
                'original_name' => $fileName ?? 'unknown',
            ]),
        ]);
    }

    /**
     * Upload profile photo from a local path (for queued uploads).
     */
    public function uploadProfilePhotoFromPath(string $filePath, string $fileName, int $userId)
    {
        $folder = 'upload/' . date('Y') . '/' . date('m') . '/' . date('d');
        $content = File::get($filePath);
        $path = "{$folder}/{$fileName}";

        Storage::disk('r2')->put($path, $content);

        $r2Url = rtrim(config('filesystems.disks.r2.url'), '/');
        $fullUrl = $r2Url . '/' . ltrim($path, '/');

        // Update User
        $user = \App\Models\User::find($userId);
        if ($user) {
            if ($user->profile_photo) {
                $this->deleteByPath($user->profile_photo);
            }
            $user->update([
                'profile_photo' => $fullUrl,
                'ik_file_id' => null
            ]);
        }

        return [
            'path' => $fullUrl,
            'fileId' => null
        ];
    }

    /**
     * Delete file from local/R2 storage by ID (unused/fallback).
     */
    public function deleteFileById(string $fileId)
    {
        // Fallback or no-op
    }

    /**
     * Delete file by path (handles both local storage and R2 absolute URLs).
     */
    public function deleteByPath(string $path)
    {
        $r2Url = rtrim(config('filesystems.disks.r2.url'), '/');
        if (str_starts_with($path, $r2Url)) {
            $relativePath = ltrim(str_replace($r2Url, '', $path), '/');
            if (Storage::disk('r2')->exists($relativePath)) {
                Storage::disk('r2')->delete($relativePath);
            }
        } else {
            // Local Public storage cleanup
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $publicPathBase = 'storage/';
            if (str_starts_with($path, $publicPathBase)) {
                $relativePublicPath = str_replace($publicPathBase, '', $path);
                if (Storage::disk('public')->exists($relativePublicPath)) {
                    Storage::disk('public')->delete($relativePublicPath);
                }
            }
        }
    }

    /**
     * Compress large images (> 200KB) supporting both Local and R2 files.
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
            
            $isR2 = false;
            $r2Url = rtrim(config('filesystems.disks.r2.url'), '/');
            $relativePath = '';
            
            if (str_starts_with($photo->photo_path, $r2Url)) {
                $isR2 = true;
                $relativePath = ltrim(str_replace($r2Url, '', $photo->photo_path), '/');
                
                if (!Storage::disk('r2')->exists($relativePath)) {
                    continue;
                }
                
                // Download file locally to private storage temp
                $content = Storage::disk('r2')->get($relativePath);
                $tempDir = storage_path('app/private/temp_compress');
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }
                
                $extension = File::extension($relativePath) ?: 'jpg';
                $filename = File::name($relativePath);
                $fullPath = $tempDir . DIRECTORY_SEPARATOR . $filename . '.' . $extension;
                File::put($fullPath, $content);
            } else {
                $fullPath = storage_path('app/public/' . ltrim($photo->photo_path, '/'));
            }
            
            if (File::exists($fullPath)) {
                $originalPath = $photo->photo_path;
                $extension = File::extension($fullPath);
                $directory = File::dirname($fullPath);
                $filename = File::name($fullPath);
                
                // Check if it's already a compressed version to avoid double suffix
                $targetFilename = str_replace('_compressed', '', $filename);
                $newName = $targetFilename . '_compressed.webp';
                $newFullPath = $directory . DIRECTORY_SEPARATOR . $newName;
                
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

                    $thumb = Image::read($fullPath);
                    $thumb->scale(width: 200);
                    $thumb->save($thumbFullPath, quality: 70);

                    if ($isR2) {
                        $folder = 'upload/' . date('Y') . '/' . date('m') . '/' . date('d');
                        
                        $newR2Path = "{$folder}/{$newName}";
                        $thumbR2Path = "{$folder}/{$thumbName}";
                        
                        Storage::disk('r2')->put($newR2Path, File::get($newFullPath));
                        Storage::disk('r2')->put($thumbR2Path, File::get($thumbFullPath));
                        
                        $newPhotoPath = $r2Url . '/' . ltrim($newR2Path, '/');
                        $newThumbPath = $r2Url . '/' . ltrim($thumbR2Path, '/');
                        
                        // Clean up temporary local files
                        @unlink($fullPath);
                        @unlink($newFullPath);
                        @unlink($thumbFullPath);
                        
                        // Delete original large file from R2
                        Storage::disk('r2')->delete($relativePath);
                        
                        $newSize = Storage::disk('r2')->size($newR2Path);
                    } else {
                        // Ensure the path is relative to storage/app/public
                        $publicPathBase = storage_path('app/public/');
                        $newRelativePath = str_replace($publicPathBase, '', $newFullPath);
                        $newRelativePath = str_replace('\\', '/', $newRelativePath);

                        $thumbRelativePath = str_replace($publicPathBase, '', $thumbFullPath);
                        $thumbRelativePath = str_replace('\\', '/', $thumbRelativePath);
                        
                        $newPhotoPath = $newRelativePath;
                        $newThumbPath = $thumbRelativePath;
                        $newSize = File::size($newFullPath);
                    }

                    // Update meta and paths
                    $meta = $photo->meta ?? [];
                    $meta['original_path_before_compression'] = $originalPath;
                    $meta['original_format'] = $extension;
                    $meta['compression_info'] = [
                        'date' => now()->toDateTimeString(),
                        'original_size' => $photo->file_size,
                        'new_size' => $newSize,
                        'quality' => $quality,
                        'format' => 'webp'
                    ];

                    $photo->update([
                        'photo_path' => $newPhotoPath,
                        'thumbnail_path' => $newThumbPath, 
                        'file_size' => $newSize,
                        'file_type' => 'image/webp',
                        'meta' => $meta
                    ]);

                    $count++;
                } catch (\Exception $e) {
                    \Log::error("Failed to compress photo ID {$photo->id}: " . $e->getMessage());
                    // Cleanup in case of exception
                    if ($isR2) {
                        if (file_exists($fullPath)) @unlink($fullPath);
                        if (file_exists($newFullPath)) @unlink($newFullPath);
                        if (file_exists($thumbFullPath)) @unlink($thumbFullPath);
                    }
                }
            }
        }
        
        return $count;
    }
}
