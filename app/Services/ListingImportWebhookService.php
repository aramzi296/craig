<?php

namespace App\Services;

use App\Models\District;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ListingImportWebhookService
{
    /**
     * @param  array<int, array<string, mixed>>  $payload
     * @return array{user_id: int, user_created: bool, listing_id: int, listing_slug: string, photos_count: int}
     */
    public function process(array $payload): array
    {
        $data = $this->parsePayload($payload);

        $whatsapp = User::normalizeWhatsappNumber($data['nomor_wa']);
        if (!$whatsapp) {
            throw new InvalidArgumentException('nomor_wa tidak valid.');
        }

        $files = $data['uploaded_files'] ?? [];
        if ($files === []) {
            throw new InvalidArgumentException('uploaded_files wajib berisi minimal satu URL gambar.');
        }

        return DB::transaction(function () use ($data, $whatsapp, $files) {
            [$user, $userCreated] = $this->resolveUser($whatsapp, $data['nama']);

            if ($user->ads_quota > 0) {
                $user->decrement('ads_quota');
            }

            $listing = $this->createListing($user, $data);
            $photosCount = $this->attachPhotos($listing, $files);

            $listing->updateSearchableField();

            return [
                'user_id' => $user->id,
                'user_created' => $userCreated,
                'listing_id' => $listing->id,
                'listing_slug' => $listing->slug,
                'photos_count' => $photosCount,
            ];
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $payload
     * @return array<string, mixed>
     */
    protected function parsePayload(array $payload): array
    {
        $merged = [];
        $files = [];

        foreach ($payload as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (isset($item['uploaded_files']) && is_array($item['uploaded_files'])) {
                $files = array_values(array_filter(
                    $item['uploaded_files'],
                    fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
                ));
                continue;
            }

            $merged = array_merge($merged, $item);
        }

        $merged['uploaded_files'] = $files;

        foreach (['nama', 'alamat', 'keterangan_usaha', 'nomor_wa'] as $field) {
            if (empty($merged[$field]) || !is_string($merged[$field])) {
                throw new InvalidArgumentException("Field {$field} wajib diisi.");
            }
        }

        return $merged;
    }

    /**
     * @return array{0: User, 1: bool}
     */
    protected function resolveUser(string $whatsapp, string $name): array
    {
        $user = User::where('whatsapp', $whatsapp)->first();

        if ($user) {
            return [$user, false];
        }

        $randomSuffix = rand(100, 999);

        $user = User::create([
            'name' => trim($name),
            'whatsapp' => $whatsapp,
            'email' => $whatsapp . '+' . $randomSuffix . '@sebatam.com',
            'password' => Hash::make(Str::random(16)),
            'ads_quota' => (int) get_setting('jumlah_iklan_user_default', 1),
            'is_verified' => DB::raw('true'),
        ]);

        return [$user, true];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function createListing(User $user, array $data): Listing
    {
        $districtId = $this->resolveDistrictId($data['alamat']);

        return Listing::create([
            'user_id' => $user->id,
            'title' => trim($data['nama']),
            'slug' => Str::slug($data['nama'] . '-' . uniqid()),
            'description' => trim($data['keterangan_usaha']),
            'address' => trim($data['alamat']),
            'district_id' => $districtId,
            'price' => null,
            'is_active' => DB::raw('true'),
            'is_premium' => DB::raw('false'),
            'whatsapp_visibility' => 2,
            'comment_visibility' => 0,
        ]);
    }

    protected function resolveDistrictId(string $alamat): ?int
    {
        $alamat = trim($alamat);

        $district = District::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($alamat)])
            ->first();

        if (!$district) {
            $district = District::query()
                ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($alamat) . '%'])
                ->first();
        }

        return $district?->id;
    }

    /**
     * @param  list<string>  $urls
     */
    protected function attachPhotos(Listing $listing, array $urls): int
    {
        $count = 0;

        foreach ($urls as $index => $url) {
            $collection = $index === 0 ? 'foto_fitur' : 'galeri';

            ListingPhoto::create([
                'listing_id' => $listing->id,
                'photo_path' => $url,
                'thumbnail_path' => $url,
                'file_type' => $this->guessMimeFromUrl($url),
                'file_size' => 0,
                'collection' => $collection,
                'meta' => [
                    'source' => 'listing_import_webhook',
                    'original_url' => $url,
                ],
            ]);

            $count++;
        }

        return $count;
    }

    protected function guessMimeFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}
