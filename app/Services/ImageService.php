<?php

namespace App\Services;

use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\ListingPhoto;

class ImageService
{
    public function uploadListingPhoto(UploadedFile $file, int $listingId, string $collection = 'foto_fitur')
    {
        return $this->storeFromPath($file->getRealPath(), $file->getClientOriginalExtension(), $listingId, $collection);
    }

    public function storeFromPath(string $path, string $extension, int $listingId, string $collection = 'foto_fitur')
    {
        $folder = "upload/{$listingId}";
        
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        // Force extension to webp
        $filename = uniqid() . '.webp';
        $thumbFilename = 'thumb_' . $filename;

        // 1. Process Main Image: Max 200KB
        $img = Image::read($path);
        
        if ($img->width() > 1200) {
            $img->scale(width: 1200);
        }

        $quality = 80;
        $encoded = $img->encodeByExtension('webp', quality: $quality);
        
        while (strlen((string)$encoded) > 200 * 1024 && $quality > 10) {
            $quality -= 10;
            $encoded = $img->encodeByExtension('webp', quality: $quality);
        }

        Storage::disk('public')->put("{$folder}/{$filename}", (string)$encoded);

        // 2. Process Thumbnail: 200x200px
        $thumb = Image::read($path);
        $thumb->cover(200, 200);
        $thumbEncoded = $thumb->encodeByExtension('webp', quality: 70);
        
        Storage::disk('public')->put("{$folder}/{$thumbFilename}", (string)$thumbEncoded);

        // 3. Save to database
        return ListingPhoto::create([
            'listing_id' => $listingId,
            'photo_path' => "{$folder}/{$filename}",
            'thumbnail_path' => "{$folder}/{$thumbFilename}",
            'collection' => $collection,
        ]);
    }
}
