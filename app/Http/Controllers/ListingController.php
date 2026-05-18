<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessListingImageUpload;
use Illuminate\Support\Facades\Storage;


class ListingController extends Controller
{
    protected $imageService;

    public function __construct(\App\Services\ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function create()
    {
        $categories = \App\Models\Tag::whereRaw('is_approved = true')->orderBy('sort_order')->get();

        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        $districts = \App\Models\District::orderBy('name')->get();
        
        $layout = auth()->check() ? 'layouts.dashboard' : 'layouts.app';
        $section = auth()->check() ? 'dashboard_content' : 'content';

        $premiumRequest = null;
        if (request()->has('premium_request_id')) {
            $premiumRequest = \App\Models\PremiumRequest::where('user_id', auth()->id())
                ->where('id', request('premium_request_id'))
                ->whereNull('listing_id')
                ->first();
        }

        return view('listings.create', compact('categories', 'listingTypes', 'districts', 'layout', 'section', 'premiumRequest'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $isPremium = $request->ad_package === 'premium';
        $descLimit = $isPremium ? get_setting('huruf_deskripsi_iklan_premium', 2000) : get_setting('huruf_deskripsi_iklan', 100);

        $rules = [
            'tags' => 'nullable|string',
            'listing_type_id' => 'nullable|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => "required|string|max:{$descLimit}",
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
            'foto_fitur' => 'nullable|image|mimes:' . get_setting('allowed_image_types', 'jpeg,png,jpg,webp') . '|max:' . get_setting('max_image_size', 2048),
            'galeri' => 'nullable|array',
            'galeri.*' => 'image|mimes:' . get_setting('allowed_image_types', 'jpeg,png,jpg,webp') . '|max:' . get_setting('max_image_size', 2048),
            'whatsapp_visibility' => 'nullable|integer|in:0,1,2',
            'comment_visibility' => 'nullable|integer|in:0,1,2',
            'website' => 'nullable|url|max:255',
            'ad_package' => 'required|in:standard,premium',
        ];

        // ADD OTP and WhatsApp validation ONLY for guests
        if (!auth()->check()) {
            $rules['whatsapp_number'] = 'required|string';
            $rules['otp'] = 'required|digits:6';
        }

        $data = $request->validate($rules, [
            'foto_fitur.image' => 'File harus berupa gambar.',
            'foto_fitur.mimes' => 'Format gambar harus ' . str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp')) . '.',
            'foto_fitur.max' => 'Ukuran foto fitur tidak boleh lebih dari ' . (get_setting('max_image_size', 2048) / 1024) . 'MB.',
            'galeri.*.image' => 'File galeri harus berupa gambar.',
            'galeri.*.mimes' => 'Format gambar galeri harus ' . str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp')) . '.',
            'galeri.*.max' => 'Ukuran setiap foto galeri tidak boleh lebih dari ' . (get_setting('max_image_size', 2048) / 1024) . 'MB.',
        ]);

        if (!$request->filled('listing_type_id')) {
            $defaultType = \App\Models\ListingType::where('slug', 'lainnya')->first() ?: \App\Models\ListingType::first();
            $data['listing_type_id'] = $defaultType?->id;
        }

        if (!auth()->check()) {
            // ── Verify OTP ──────────────────────────────────────────────────────
            $whatsapp = \App\Models\User::normalizeWhatsappNumber($data['whatsapp_number']);
            $otp = $data['otp'];
            $lookup = hash('sha256', $otp);

            $user = \App\Models\User::where('whatsapp', $whatsapp)
                ->where('wa_otp1_lookup', $lookup)
                ->first();

            if (!$user || ! \Illuminate\Support\Facades\Hash::check($otp, $user->wa_otp1)) {
                return back()->withErrors(['otp' => 'Kode OTP tidak valid.'])->withInput();
            }

            if ($user->wa_otp1_expires_at->isPast()) {
                return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa.'])->withInput();
            }

            // OTP is valid! Associate listing with this user.
            $data['user_id'] = $user->id;
            
            // Login user
            auth()->login($user, true);

            // Clear OTP
            $user->update([
                'wa_otp1' => null,
                'wa_otp1_lookup' => null,
                'wa_otp1_expires_at' => null,
            ]);
        } else {
            $data['user_id'] = auth()->id();
        }

        $user = auth()->user();
        $isPremiumPackage = false; // Premium dinonaktifkan sementara — semua iklan adalah standar gratis
        
        // Cek kuota slot iklan gratis
        if ($user->ads_quota <= 0) {
            return back()->withErrors(['error' => 'Jatah slot iklan gratis Anda sudah habis. Silakan hubungi admin untuk menambah slot iklan.'])->withInput();
        }

        $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        $data['is_active'] = \DB::raw('true');
        $data['expires_at'] = now()->addDays((int)get_setting('expire_iklan', 30));
        if ($isPremiumPackage) {
            $data['is_premium'] = \DB::raw('true');
        }

        $listing = \App\Models\Listing::create($data);

        // Decrement quota only for non-premium ads
        if (!$isPremiumPackage) {
            $user->decrement('ads_quota');
        }

        // Upload Foto Fitur
        if ($request->hasFile('foto_fitur')) {
            $file = $request->file('foto_fitur');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $tempPath = $file->storeAs('temp_uploads', $fileName);
            $fullPath = storage_path('app/private/' . $tempPath);
            ProcessListingImageUpload::dispatchSync($fullPath, $listing->id, 'foto_fitur', $fileName);
        }

        // Upload Galeri
        if ($request->hasFile('galeri')) {
            $maxPhotos = $isPremiumPackage ? get_setting('max_foto_iklan_premium', 8) : get_setting('max_foto_iklan', 0);
            foreach (array_slice($request->file('galeri'), 0, $maxPhotos) as $file) {
                $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                $tempPath = $file->storeAs('temp_uploads', $fileName);
                $fullPath = storage_path('app/private/' . $tempPath);
                ProcessListingImageUpload::dispatchSync($fullPath, $listing->id, 'galeri', $fileName);
            }
        }

        // Process Tags from Tagify
        $tagIds = [];
        if ($request->filled('tags')) {
            $tagifyTags = json_decode($request->tags, true);
            $maxAllowed = $isPremiumPackage ? get_setting('max_category_premium', 10) : get_setting('max_category', 3);
            
            foreach (array_slice($tagifyTags, 0, $maxAllowed) as $cat) {
                $tagName = trim($cat['value']);
                $slug = \Illuminate\Support\Str::slug($tagName);

                // Cari berdasarkan nama (case-insensitive) atau slug
                $tag = \App\Models\Tag::whereRaw('LOWER(name) = ?', [strtolower($tagName)])
                    ->orWhere('slug', $slug)
                    ->first();

                if (!$tag) {
                    $tag = \App\Models\Tag::create([
                        'name' => $tagName,
                        'slug' => $slug,
                        'icon' => 'fa-solid fa-tag',
                        'sort_order' => (int)\App\Models\Tag::max('sort_order') + 1,
                        'is_approved' => \DB::raw('false')
                    ]);
                }

                $tagIds[] = $tag->id;
            }
        }

        $listing->tags()->sync($tagIds);
        $listing->updateSearchableField();

        // Link to existing premium request if provided
        if ($request->filled('premium_request_id')) {
            $premiumRequest = \App\Models\PremiumRequest::where('user_id', auth()->id())
                ->where('id', $request->premium_request_id)
                ->whereNull('listing_id')
                ->first();
            
            if ($premiumRequest) {
                $updateData = ['listing_id' => $listing->id];
                
                // If it was already approved (active), set expiry and make listing premium
                if ($premiumRequest->status === 'active') {
                    $updateData['expires_at'] = now()->addDays($premiumRequest->package->duration_days);
                    $listing->update([
                        'is_premium'   => \DB::raw('true'),
                        'listing_rank' => 100,
                    ]);
                }
                
                $premiumRequest->update($updateData);
                return redirect()->route('dashboard')->with('success', 'Iklan berhasil dibuat dan dihubungkan dengan paket premium Anda.');
            }
        }

        return redirect()->route('dashboard')->with('success', 'Iklan Anda berhasil dikirim dan ditayangkan.');
    }

    public function edit($id)
    {
        $listing = \App\Models\Listing::with('photos')->where('user_id', auth()->id())->findOrFail($id);
        $categories = \App\Models\Tag::whereRaw('is_approved = true')->orderBy('sort_order')->get();

        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        $districts = \App\Models\District::orderBy('name')->get();

        return view('listings.edit', compact('listing', 'categories', 'listingTypes', 'districts'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'tags' => 'nullable|string',
            'listing_type_id' => 'nullable|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:' . ($listing->is_premium ? get_setting('huruf_deskripsi_iklan_premium', 2000) : get_setting('huruf_deskripsi_iklan', 100)),
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
            'foto_fitur' => 'nullable|image|mimes:' . get_setting('allowed_image_types', 'jpeg,png,jpg,webp') . '|max:' . get_setting('max_image_size', 2048),
            'galeri' => 'nullable|array',
            'galeri.*' => 'image|mimes:' . get_setting('allowed_image_types', 'jpeg,png,jpg,webp') . '|max:' . get_setting('max_image_size', 2048),
            'whatsapp_visibility' => 'nullable|integer|in:0,1,2',
            'comment_visibility' => 'nullable|integer|in:0,1,2',
            'website' => 'nullable|url|max:255',
        ], [
            'foto_fitur.image' => 'File harus berupa gambar.',
            'foto_fitur.mimes' => 'Format gambar harus ' . str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp')) . '.',
            'foto_fitur.max' => 'Ukuran foto fitur tidak boleh lebih dari ' . (get_setting('max_image_size', 2048) / 1024) . 'MB.',
            'galeri.*.image' => 'File galeri harus berupa gambar.',
            'galeri.*.mimes' => 'Format gambar galeri harus ' . str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp')) . '.',
            'galeri.*.max' => 'Ukuran setiap foto galeri tidak boleh lebih dari ' . (get_setting('max_image_size', 2048) / 1024) . 'MB.',
        ]);

        if ($data['title'] !== $listing->title) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        }

        $listing->update($data);

        // Upload New Foto Fitur
        if ($request->hasFile('foto_fitur')) {
            // Delete old featured photo(s)
            $oldFeatured = $listing->photos()->where('collection', 'foto_fitur')->get();
            foreach($oldFeatured as $p) {
                if ($p->photo_path) {
                    $this->imageService->deleteByPath($p->photo_path);
                }
                $p->delete();
            }

            $file = $request->file('foto_fitur');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $tempPath = $file->storeAs('temp_uploads', $fileName);
            $fullPath = storage_path('app/private/' . $tempPath);
            ProcessListingImageUpload::dispatchSync($fullPath, $listing->id, 'foto_fitur', $fileName);
        }

        // Upload New Galeri Photos
        if ($request->hasFile('galeri')) {
            $currentCount = $listing->photos()->where('collection', 'galeri')->count();
            
            // Determine limit based on type
            $maxPhotos = get_setting('max_foto_iklan', 4);
            if ($listing->is_premium) {
                $maxPhotos = get_setting('max_foto_iklan_premium', 12);
            }
            
            $remaining = $maxPhotos - $currentCount;

            if ($remaining > 0) {
                $files = $request->file('galeri');
                if (!is_array($files)) { $files = [$files]; }
                
                foreach (array_slice($files, 0, $remaining) as $file) {
                    // Store file temporarily
                    $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                    $tempPath = $file->storeAs('temp_uploads', $fileName);
                    $fullPath = storage_path('app/private/' . $tempPath);

                    // Dispatch Job Synchronously
                    ProcessListingImageUpload::dispatchSync($fullPath, $listing->id, 'galeri', $fileName);
                }
                
                if (count($files) > $remaining) {
                    session()->flash('warning', 'Beberapa foto galeri dilewati karena sudah mencapai batas maksimal.');
                }
            } else {
                session()->flash('error', 'Gagal menambah foto galeri: Jatah foto sudah penuh.');
            }
        }

        // Process Tags from Tagify
        $tagIds = [];
        if ($request->filled('tags')) {
            $tagifyTags = json_decode($request->tags, true);
            $maxAllowed = $listing->is_premium ? get_setting('max_category_premium', 10) : get_setting('max_category', 3);
            
            foreach (array_slice($tagifyTags, 0, $maxAllowed) as $cat) {
                $tagName = trim($cat['value']);
                $slug = \Illuminate\Support\Str::slug($tagName);

                // Cari berdasarkan nama (case-insensitive) atau slug
                $tag = \App\Models\Tag::whereRaw('LOWER(name) = ?', [strtolower($tagName)])
                    ->orWhere('slug', $slug)
                    ->first();

                if (!$tag) {
                    $tag = \App\Models\Tag::create([
                        'name' => $tagName,
                        'slug' => $slug,
                        'icon' => 'fa-solid fa-tag',
                        'sort_order' => (int)\App\Models\Tag::max('sort_order') + 1,
                        'is_approved' => \DB::raw('false')
                    ]);
                }
                $tagIds[] = $tag->id;
            }
        }

        $listing->tags()->sync($tagIds);
        $listing->updateSearchableField();

        return redirect()->route('dashboard')->with('success', 'Iklan Anda berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($id);
        
        if (!$listing->is_active) {
            // Check if it's an admin-created ad that has expired
            if ($listing->activation_code && $listing->expires_at && $listing->expires_at->isPast()) {
                return back()->with('error', 'Masa aktivasi iklan ini sudah habis (10 hari). Silakan buat iklan baru.');
            }

            // Activating
            if ($listing->activation_code) {
                $days = (int)get_setting('expire_iklan', 30);
                $expiresAt = now()->addDays($days)->toDateTimeString();
                \DB::update("UPDATE listings SET is_active = true, expires_at = ?, activation_code = NULL, updated_at = NOW() WHERE id = ?", [$expiresAt, $id]);
            } else {
                \DB::update("UPDATE listings SET is_active = true, updated_at = NOW() WHERE id = ?", [$id]);
            }
            
            $msg = 'Iklan berhasil diaktifkan.';
        } else {
            // Deactivating
            \DB::update("UPDATE listings SET is_active = false, updated_at = NOW() WHERE id = ?", [$id]);
            $msg = 'Iklan berhasil dinonaktifkan.';
        }

        return back()->with('success', $msg);
    }

    public function destroy($id)
    {
        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($id);
        
        // Delete photos from local storage
        foreach ($listing->photos as $photo) {
            if ($photo->photo_path) {
                $this->imageService->deleteByPath($photo->photo_path);
            }
        }

        $listing->delete();

        return redirect()->route('dashboard')->with('success', 'Iklan Anda telah dihapus.');
    }

    public function deletePhoto($id)
    {
        $photo = \App\Models\ListingPhoto::whereHas('listing', function($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        if ($photo->photo_path) {
            $this->imageService->deleteByPath($photo->photo_path);
        }

        $photo->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    public function contactAdmin(\Illuminate\Http\Request $request, $id)
    {
        $listing = \App\Models\Listing::findOrFail($id);
        
        $request->validate([
            'visitor_whatsapp' => 'required|string',
            'visitor_message' => 'required|string|max:1000',
        ]);

        $adminWa = config('services.whatsapp.admin_number_2', '628117007201');
        $message = "Halo Admin, saya tertarik dengan iklan berikut:\n\n"
                 . "Judul: " . $listing->title . "\n"
                 . "Link: " . route('listings.show', $listing->slug) . "\n\n"
                 . "Pesan Saya: " . $request->visitor_message . "\n"
                 . "Nomor WA Saya: " . $request->visitor_whatsapp;

        $url = "https://wa.me/{$adminWa}?text=" . urlencode($message);

        return redirect()->away($url);
    }
}
