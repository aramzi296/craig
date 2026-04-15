<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Services\ImageService;
use Illuminate\Support\Facades\File;

class ListingImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imageService = new ImageService();
        $listings = Listing::all();
        $sourceDir = public_path('images-contoh');

        if (!File::exists($sourceDir)) {
            $this->command->error("Folder public/images-contoh tidak ditemukan.");
            return;
        }

        $files = File::files($sourceDir);
        if (empty($files)) {
            $this->command->error("Tidak ada file gambar di folder public/images-contoh.");
            return;
        }

        foreach ($listings as $listing) {
            $this->command->info("Memproses foto untuk listing: {$listing->title}");

            // Hapus foto lama di database dan storage (Opsional, tapi bagus untuk kebersihan)
            ListingPhoto::where('listing_id', $listing->id)->delete();
            // Folder fisik juga sebaiknya dibersihkan jika ingin benar-benar ganti baru
            // \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory("upload/{$listing->id}");

            // Pilih satu file acak
            $randomFile = $files[array_rand($files)];
            $path = $randomFile->getRealPath();
            $extension = $randomFile->getExtension();

            // Gunakan ImageService untuk simpan (otomatis resize ke 200kb dan buat thumbnail)
            $imageService->storeFromPath($path, $extension, $listing->id, 'foto_fitur');
        }

        $this->command->info("Selesai mengganti semua foto fitur listing.");
    }
}
