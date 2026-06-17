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
        $categories = \Illuminate\Support\Facades\Cache::store('redis')->remember('categories:form_dropdown', 3600, function() {
            return \App\Models\Category::whereNull('parent_id')
                ->with(['children' => function($q) {
                    $q->whereRaw('is_approved = true')->orderBy('sort_order');
                }])
                ->whereRaw('is_approved = true')
                ->orderBy('sort_order')
                ->get();
        });

        $districts = \App\Models\District::orderBy('name')->get();
        $subdistricts = \App\Models\Subdistrict::orderBy('name')->get();
        
        // Mengambil tag dari cache global
        $tags = \Illuminate\Support\Facades\Cache::store('redis')->remember('tags:global_list', 3600, function() {
            return \App\Models\Tag::orderBy('sort_order')->get();
        });
        
        $layout = auth()->check() ? 'layouts.dashboard' : 'layouts.app';
        $section = auth()->check() ? 'dashboard_content' : 'content';

        $premiumRequest = null;
        if (request()->has('premium_request_id')) {
            $premiumRequest = \App\Models\PremiumRequest::where('user_id', auth()->id())
                ->where('id', request('premium_request_id'))
                ->whereNull('listing_id')
                ->first();
        }

        return view('listings.create', compact('categories', 'districts', 'subdistricts', 'tags', 'layout', 'section', 'premiumRequest'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $isPremium = $request->ad_package === 'premium';
        $descLimit = $isPremium ? get_setting('huruf_deskripsi_iklan_premium', 2000) : get_setting('huruf_deskripsi_iklan', 100);

        $rules = [
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => "required|string|max:{$descLimit}",
            'price' => 'nullable|numeric',
            'district_id' => 'nullable|exists:districts,id',
            'subdistrict_id' => 'nullable|exists:subdistricts,id',
            'address' => 'required|string|max:255',
            'foto_fitur' => 'required|image|mimes:' . get_setting('allowed_image_types', 'jpeg,png,jpg,webp') . '|max:' . get_setting('max_image_size', 2048),
            'galeri' => 'nullable|array',
            'galeri.*' => 'image|mimes:' . get_setting('allowed_image_types', 'jpeg,png,jpg,webp') . '|max:' . get_setting('max_image_size', 2048),
            'comment_visibility' => 'nullable|integer|in:0,1,2',
            'website' => 'nullable|url|max:255',
            'ad_package' => 'required|in:standard,premium',
        ];

        // ADD WhatsApp validation ONLY for guests
        if (!auth()->check()) {
            $rules['whatsapp_number'] = 'required|string';
        }

        $data = $request->validate($rules, [
            'foto_fitur.required' => 'Foto fitur wajib diunggah.',
            'foto_fitur.image' => 'File harus berupa gambar.',
            'foto_fitur.mimes' => 'Format gambar harus ' . str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp')) . '.',
            'foto_fitur.max' => 'Ukuran foto fitur tidak boleh lebih dari ' . (get_setting('max_image_size', 2048) / 1024) . 'MB.',
            'galeri.*.image' => 'File galeri harus berupa gambar.',
            'galeri.*.mimes' => 'Format gambar galeri harus ' . str_replace(',', ', ', get_setting('allowed_image_types', 'jpeg,png,jpg,webp')) . '.',
            'galeri.*.max' => 'Ukuran setiap foto galeri tidak boleh lebih dari ' . (get_setting('max_image_size', 2048) / 1024) . 'MB.',
        ]);

        $isGuest = false;
        if (!auth()->check()) {
            $whatsapp = \App\Models\User::normalizeWhatsappNumber($data['whatsapp_number']);
            if (!$whatsapp) {
                return back()->withErrors(['whatsapp_number' => 'Nomor WhatsApp tidak valid.'])->withInput();
            }

            $user = \App\Models\User::where('whatsapp', $whatsapp)->first();

            if (!$user) {
                // Register new user automatically
                $randomSuffix = rand(100, 999);
                $email = $whatsapp . '+' . $randomSuffix . '@sebatam.com';
                $password = \Illuminate\Support\Str::random(10);
                
                $user = \App\Models\User::create([
                    'name'      => 'user-' . rand(100000, 999999),
                    'whatsapp'  => $whatsapp,
                    'email'     => $email,
                    'password'  => \Illuminate\Support\Facades\Hash::make($password),
                    'ads_quota' => get_setting('jumlah_iklan_user_default', 1),
                ]);
            }

            // Associate listing with this user.
            $data['user_id'] = $user->id;
            $isGuest = true;
        } else {
            $user = auth()->user();
            $data['user_id'] = $user->id;
        }

        $isPremiumPackage = false; // Premium dinonaktifkan sementara — semua iklan adalah standar gratis
        
        // Cek kuota slot iklan gratis
        if ($user->ads_quota <= 0) {
            return back()->withErrors(['error' => 'Jatah slot iklan gratis Anda sudah habis. Silakan hubungi admin untuk menambah slot iklan.'])->withInput();
        }

        $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        
        if ($isGuest) {
            $data['is_active'] = \DB::raw('false');
            $data['activation_code'] = (string) random_int(10000, 99999);
        } else {
            $data['is_active'] = \DB::raw('true');
        }

        $data['expires_at'] = now()->addDays((int)get_setting('expire_iklan', 30));
        $data['whatsapp_visibility'] = 2;
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
            $maxAllowed = $isPremiumPackage ? get_setting('max_tagar_premium', 10) : get_setting('max_tagar', 3);
            
            foreach (array_slice($tagifyTags, 0, $maxAllowed) as $cat) {
                $tagName = trim($cat['value']);
                $slug = \Illuminate\Support\Str::slug($tagName);

                $tag = \App\Models\Tag::findOrCreateByName($tagName, false);

                $tagIds[] = $tag->id;
            }
        }

        $listing->tags()->sync($tagIds);
        if ($request->filled('category_id')) {
            $listing->categories()->sync([$request->category_id]);
        }
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

        if ($isGuest) {
            $layout = 'layouts.app';
            $section = 'content';
            $otp = $listing->activation_code;
            $whatsapp = $user->whatsapp;
            $botNumber = config('services.whatsapp.bot_number', '6282172292230');
            return view('listings.activation', compact('listing', 'otp', 'whatsapp', 'botNumber', 'layout', 'section'));
        }

        return redirect()->route('dashboard')->with('success', 'Iklan Anda berhasil dikirim dan ditayangkan.');
    }

    public function edit($id)
    {
        $listing = \App\Models\Listing::with('photos')->where('user_id', auth()->id())->findOrFail($id);
        $categories = \Illuminate\Support\Facades\Cache::store('redis')->remember('categories:form_dropdown', 3600, function() {
            return \App\Models\Category::whereNull('parent_id')
                ->with(['children' => function($q) {
                    $q->whereRaw('is_approved = true')->orderBy('sort_order');
                }])
                ->whereRaw('is_approved = true')
                ->orderBy('sort_order')
                ->get();
        });

        $districts = \App\Models\District::orderBy('name')->get();
        $subdistricts = \App\Models\Subdistrict::orderBy('name')->get();
        
        // Mengambil tag dari cache global
        $tags = \Illuminate\Support\Facades\Cache::store('redis')->remember('tags:global_list', 3600, function() {
            return \App\Models\Tag::orderBy('sort_order')->get();
        });

        return view('listings.edit', compact('listing', 'categories', 'districts', 'subdistricts', 'tags'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:' . ($listing->is_premium ? get_setting('huruf_deskripsi_iklan_premium', 2000) : get_setting('huruf_deskripsi_iklan', 100)),
            'price' => 'nullable|numeric',
            'district_id' => 'nullable|exists:districts,id',
            'subdistrict_id' => 'nullable|exists:subdistricts,id',
            'address' => 'required|string|max:255',
            'foto_fitur' => 'nullable|image|mimes:' . get_setting('allowed_image_types', 'jpeg,png,jpg,webp') . '|max:' . get_setting('max_image_size', 2048),
            'galeri' => 'nullable|array',
            'galeri.*' => 'image|mimes:' . get_setting('allowed_image_types', 'jpeg,png,jpg,webp') . '|max:' . get_setting('max_image_size', 2048),
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
            $maxAllowed = $listing->is_premium ? get_setting('max_tagar_premium', 10) : get_setting('max_tagar', 3);
            
            foreach (array_slice($tagifyTags, 0, $maxAllowed) as $cat) {
                $tagName = trim($cat['value']);
                $slug = \Illuminate\Support\Str::slug($tagName);

                $tag = \App\Models\Tag::findOrCreateByName($tagName, false);
                $tagIds[] = $tag->id;
            }
        }

        $listing->tags()->sync($tagIds);
        if ($request->filled('category_id')) {
            $listing->categories()->sync([$request->category_id]);
        }
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

    public function report(\Illuminate\Http\Request $request, $id)
    {
        $listing = \App\Models\Listing::findOrFail($id);

        $rules = [
            'reason' => 'required|string|in:Penipuan,Spam / Duplikat,Konten Tidak Layak,Usaha Sudah Tutup,Lainnya',
            'description' => 'nullable|string|max:1000',
        ];

        if (!auth()->check()) {
            $rules['reporter_whatsapp'] = 'required|string|max:20';
        }

        $data = $request->validate($rules, [
            'reason.required' => 'Alasan wajib dipilih.',
            'reason.in' => 'Alasan tidak valid.',
            'reporter_whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $reportData = [
            'listing_id' => $listing->id,
            'user_id' => auth()->id(),
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
            'status' => 'pending',
        ];

        if (!auth()->check()) {
            $whatsapp = \App\Models\User::normalizeWhatsappNumber($data['reporter_whatsapp']);
            if (!$whatsapp) {
                return back()->withErrors(['reporter_whatsapp' => 'Nomor WhatsApp tidak valid.'])->withInput();
            }
            $reportData['reporter_whatsapp'] = $whatsapp;
        } else {
            $reportData['reporter_whatsapp'] = auth()->user()->whatsapp;
        }

        \App\Models\ListingReport::create($reportData);

        return back()->with('success', 'Laporan Anda berhasil dikirim dan akan segera ditinjau oleh Admin. Terima kasih!');
    }

    public function whatsapp(Request $request, $id)
    {
        $listing = \App\Models\Listing::with('user')->findOrFail($id);

        \App\Models\ListingWhatsappClick::create([
            'listing_id' => $listing->id,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // $message = "Halo " . $listing->user->name . ", saya tertarik dengan usaha Anda di " . config('app.name') . ": " . $listing->title . ".";
        $message = "Saya membaca profil Anda di " . config('app.name') . " dengan judul: " . $listing->title . ".";
        $url = "https://wa.me/" . $listing->user->whatsapp . "?text=" . urlencode($message);

        if ($request->wantsJson()) {
            return response()->json([
                'whatsapp' => $listing->user->whatsapp,
                'url' => $url,
            ]);
        }

        return redirect()->away($url);
    }
}
