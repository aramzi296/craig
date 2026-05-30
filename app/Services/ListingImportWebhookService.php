<?php

namespace App\Services;

use App\Models\District;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ListingImportWebhookService
{
    /**
     * @param  array<string, mixed>|list<mixed>  $payload
     * @return array{user_id: int, user_created: bool, listing_id: int, listing_slug: string, photos_count: int}
     */
    public function process(array $payload): array
    {
        $data = $this->normalizeIncomingPayload($payload);

        $whatsapp = User::normalizeWhatsappNumber($data['nomor_wa']);
        if (!$whatsapp) {
            throw new InvalidArgumentException('nomor_wa tidak valid.');
        }

        $files = $data['uploaded_files'] ?? [];

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
     * Normalizes n8n / HTTP client payload shapes into one flat record.
     *
     * @param  array<string, mixed>|list<mixed>  $payload
     * @return array<string, mixed>
     */
    protected function normalizeIncomingPayload(array $payload): array
    {
        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload = $payload['data'];
        } elseif (isset($payload['body'])) {
            $body = $payload['body'];
            $payload = is_string($body) ? (json_decode($body, true) ?? []) : (is_array($body) ? $body : []);
        }

        // Satu objek datar: { nama, alamat, ..., uploaded_files: [...] }
        if ($this->isListingRecord($payload)) {
            return $this->finalizeRecord($payload);
        }

        // Satu elemen berisi objek record lengkap: [ {...} ]
        if (is_array($payload) && count($payload) === 1 && isset($payload[0]) && is_array($payload[0]) && $this->isListingRecord($payload[0])) {
            return $this->finalizeRecord($payload[0]);
        }

        if (!array_is_list($payload)) {
            throw new InvalidArgumentException('Format payload tidak dikenali. Kirim array JSON atau satu objek dengan field nama & uploaded_files.');
        }

        // Satu elemen berisi array penuh: [ [{...}, {...}] ]
        if (count($payload) === 1 && is_array($payload[0]) && array_is_list($payload[0])) {
            $payload = $payload[0];
        }

        return $this->finalizeRecord($this->mergePayloadParts($payload));
    }

    /**
     * @param  list<mixed>  $parts
     * @return array<string, mixed>
     */
    protected function mergePayloadParts(array $parts): array
    {
        $merged = [];
        $files = [];

        foreach ($parts as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (isset($item['uploaded_files'])) {
                $rawFiles = is_string($item['uploaded_files'])
                    ? array_values(array_filter(preg_split('/[\s,;\n\r]+/', $item['uploaded_files'])))
                    : (is_array($item['uploaded_files']) ? $item['uploaded_files'] : []);
                $files = array_merge($files, $this->filterFileUrls($rawFiles));
            }

            if ($this->isListingRecord($item) || $this->isPartialListingRecord($item)) {
                $merged = array_merge($merged, $item);
            }
        }

        if ($files !== []) {
            $merged['uploaded_files'] = $files;
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<string, mixed>
     */
    protected function finalizeRecord(array $record): array
    {
        if (isset($record['uploaded_files'])) {
            if (is_string($record['uploaded_files'])) {
                $urls = preg_split('/[\s,;\n\r]+/', $record['uploaded_files']);
                $record['uploaded_files'] = array_values(array_filter($urls));
            }
            if (is_array($record['uploaded_files'])) {
                $record['uploaded_files'] = $this->filterFileUrls($record['uploaded_files']);
            }
        }

        // Auto fallback for alamat
        if (empty($record['alamat'])) {
            $record['alamat'] = 'Batam';
        }

        // Auto fallback for judul
        if (empty($record['judul'])) {
            if (!empty($record['nama_usaha'])) {
                $record['judul'] = $record['nama_usaha'];
            } elseif (!empty($record['nama'])) {
                $record['judul'] = $record['nama'];
            }
        }

        foreach (['nama', 'judul', 'alamat', 'keterangan_usaha', 'nomor_wa'] as $field) {
            if (empty($record[$field]) || !is_string($record[$field])) {
                throw new InvalidArgumentException("Field {$field} wajib diisi.");
            }
        }

        return $record;
    }

    /**
     * @param  list<mixed>  $urls
     * @return list<string>
     */
    protected function filterFileUrls(array $urls): array
    {
        // Filter out empty URLs or URLs containing "undefined"
        $urls = array_filter($urls, function ($url) {
            if (!is_string($url)) return false;
            $trimmed = trim($url);
            return $trimmed !== '' && !str_contains($trimmed, 'undefined');
        });

        $candidates = array_values(array_filter(
            $urls,
            fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL)
        ));

        $valid = [];
        $rejected = [];

        foreach ($candidates as $url) {
            if ($reason = $this->rejectReasonForImageUrl($url)) {
                $rejected[] = "{$url} ({$reason})";
                continue;
            }
            $valid[] = $url;
        }

        return $valid;
    }

    /**
     * Returns rejection reason, or null if URL points to a real image.
     */
    protected function rejectReasonForImageUrl(string $url): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withOptions(['allow_redirects' => true])
                ->head($url);

            if (!$response->successful()) {
                $response = Http::timeout(20)
                    ->withOptions(['allow_redirects' => true])
                    ->get($url);
            }

            if (!$response->successful()) {
                return 'HTTP ' . $response->status();
            }

            $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));

            if ($contentType === 'text/html' || $contentType === 'text/plain') {
                return 'bukan gambar (' . ($contentType ?: 'unknown') . ') — kemungkinan halaman error 404';
            }

            if (!str_starts_with($contentType, 'image/')) {
                return 'Content-Type bukan image/* (' . ($contentType ?: 'kosong') . ')';
            }

            $length = (int) $response->header('Content-Length');
            if ($length === 0 && $response->body() !== '') {
                $length = strlen($response->body());
            }

            if ($length === 0) {
                return 'ukuran file 0 byte';
            }

            return null;
        } catch (\Throwable $e) {
            return 'gagal diakses: ' . $e->getMessage();
        }
    }

    /**
     * @param  array<mixed>  $data
     */
    protected function isListingRecord(array $data): bool
    {
        return isset($data['nama']);
    }

    /**
     * @param  array<mixed>  $data
     */
    protected function isPartialListingRecord(array $data): bool
    {
        return isset($data['nama']) || isset($data['nomor_wa']) || isset($data['uploaded_files']) || isset($data['judul']) || isset($data['nama_usaha']);
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
        $title = trim($data['judul'] ?? $data['nama']);

        return Listing::create([
            'user_id' => $user->id,
            'title' => $title,
            'slug' => Str::slug($title . '-' . uniqid()),
            'description' => trim($data['keterangan_usaha']),
            'address' => trim($data['alamat']),
            'district_id' => $districtId,
            'price' => null,
            'is_active' => DB::raw('true'),
            'is_premium' => DB::raw('false'),
            'whatsapp_visibility' => 2,
            'comment_visibility' => 0,
            'features' => [
                'nama_usaha' => trim($data['nama_usaha'] ?? $data['judul'] ?? ''),
                'judul' => trim($data['judul'] ?? ''),
                'referensi' => trim($data['referensi'] ?? ''),
            ],
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
