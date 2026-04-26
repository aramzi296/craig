<?php

namespace App\Services;

use ImageKit\ImageKit;
use Illuminate\Http\UploadedFile;
use App\Models\ListingPhoto;

class ImageService
{
    protected $imageKit;

    public function __construct()
    {
        $this->imageKit = new ImageKit(
            config('services.imagekit.public_key'),
            config('services.imagekit.private_key'),
            config('services.imagekit.url_endpoint')
        );
    }

    /**
     * Upload listing photo to ImageKit.
     */
    public function uploadListingPhoto(UploadedFile $file, int $listingId, string $collection = 'foto_fitur')
    {
        $folder = "/listings/{$listingId}";
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        $upload = $this->imageKit->uploadFile([
            'file' => base64_encode(file_get_contents($file->getRealPath())),
            'fileName' => $fileName,
            'folder' => $folder,
            'useUniqueFileName' => true,
        ]);

        if ($upload->error) {
            throw new \Exception("ImageKit Upload Error: " . $upload->error->message);
        }

        $result = $upload->result;

        // In ImageKit, we store the filePath. 
        // Thumbnails are generated on-the-fly via URL transformations.
        return ListingPhoto::create([
            'listing_id' => $listingId,
            'photo_path' => $result->filePath,
            'thumbnail_path' => $result->filePath, 
            'collection' => $collection,
            'ik_file_id' => $result->fileId,
        ]);
    }

    /**
     * Upload profile photo to ImageKit.
     */
    public function uploadProfilePhoto(UploadedFile $file, int $userId)
    {
        $folder = "foto_profil";
        $fileName = "user_{$userId}_" . bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();

        $upload = $this->imageKit->uploadFile([
            'file' => base64_encode(file_get_contents($file->getRealPath())),
            'fileName' => $fileName,
            'folder' => $folder,
            'useUniqueFileName' => true,
        ]);

        if ($upload->error) {
            throw new \Exception("ImageKit Upload Error: " . $upload->error->message);
        }

        return [
            'path' => $upload->result->filePath,
            'fileId' => $upload->result->fileId
        ];
    }

    /**
     * Upload file from path (for bot usage).
     */
    public function uploadFromPath(string $filePath, string $fileName, string $folder)
    {
        $upload = $this->imageKit->uploadFile([
            'file' => base64_encode(file_get_contents($filePath)),
            'fileName' => $fileName,
            'folder' => $folder,
            'useUniqueFileName' => true,
        ]);

        if ($upload->error) {
            throw new \Exception("ImageKit Upload Error: " . $upload->error->message);
        }

        return $upload->result;
    }

    /**
     * Upload listing photo from a local path (for queued uploads).
     */
    public function uploadListingPhotoFromPath(string $filePath, string $fileName, int $listingId, string $collection = 'foto_fitur')
    {
        $folder = "/listings/{$listingId}";

        $upload = $this->imageKit->uploadFile([
            'file' => base64_encode(file_get_contents($filePath)),
            'fileName' => $fileName,
            'folder' => $folder,
            'useUniqueFileName' => true,
        ]);

        if ($upload->error) {
            throw new \Exception("ImageKit Upload Error: " . $upload->error->message);
        }

        $result = $upload->result;

        return ListingPhoto::create([
            'listing_id' => $listingId,
            'photo_path' => $result->filePath,
            'thumbnail_path' => $result->filePath, 
            'collection' => $collection,
            'ik_file_id' => $result->fileId,
        ]);
    }

    /**
     * Upload profile photo from a local path (for queued uploads).
     */
    public function uploadProfilePhotoFromPath(string $filePath, string $fileName, int $userId)
    {
        $folder = "foto_profil";

        $upload = $this->imageKit->uploadFile([
            'file' => base64_encode(file_get_contents($filePath)),
            'fileName' => $fileName,
            'folder' => $folder,
            'useUniqueFileName' => true,
        ]);

        if ($upload->error) {
            throw new \Exception("ImageKit Upload Error: " . $upload->error->message);
        }

        $result = $upload->result;

        // Update User
        $user = \App\Models\User::find($userId);
        if ($user) {
            $user->update([
                'profile_photo' => $result->filePath,
                'ik_file_id' => $result->fileId
            ]);
        }

        return [
            'path' => $result->filePath,
            'fileId' => $result->fileId
        ];
    }

    /**
     * Delete file from ImageKit by fileId.
     */
    public function deleteFileById(string $fileId)
    {
        try {
            $this->imageKit->deleteFile($fileId);
        } catch (\Exception $e) {
            // Silently fail or log if already deleted
        }
    }
}
