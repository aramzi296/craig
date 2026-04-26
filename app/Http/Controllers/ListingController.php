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
        $categories = \App\Models\Category::orderBy('sort_order')->get();
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
            'categories' => 'nullable|string',
            'listing_type_id' => 'required|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => "required|string|max:{$descLimit}",
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'whatsapp_visibility' => 'required|integer|in:0,1,2',
            'comment_visibility' => 'required|integer|in:0,1,2',
            'website' => 'nullable|url|max:255',
            'ad_package' => 'required|in:standard,premium',
        ];

        // ADD OTP and WhatsApp validation ONLY for guests
        if (!auth()->check()) {
            $rules['whatsapp_number'] = 'required|string';
            $rules['otp'] = 'required|digits:6';
        }

        $data = $request->validate($rules);

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
        $isPremiumPackage = $request->ad_package === 'premium';
        
        // Check quota: only if NOT choosing premium package
        if (!$isPremiumPackage) {
            if ($user->ads_quota <= 0) {
                return back()->withErrors(['error' => 'Kuota iklan gratis Anda sudah habis. Silakan pilih paket premium atau hubungi admin untuk menambah kuota.'])->withInput();
            }
        }

        $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        $data['is_active'] = \DB::raw('true');
        if ($isPremiumPackage) {
            $data['is_premium'] = \DB::raw('true');
        }

        $listing = \App\Models\Listing::create($data);

        // Decrement quota only for non-premium ads
        if (!$isPremiumPackage) {
            $user->decrement('ads_quota');
        }

        // Upload Photos
        if ($request->hasFile('photos')) {
            $maxPhotos = get_setting('max_foto_iklan', 0);
            // Since it's a new listing, we'll check if it's premium. 
            // BUT wait, premium is usually set AFTER creation or via listing type?
            // Let's check listing type.
            $type = \App\Models\ListingType::find($data['listing_type_id']);
            // If the package is premium, use premium limit.
            if ($isPremiumPackage) {
                $maxPhotos = get_setting('max_foto_iklan_premium', 8);
            }

            foreach (array_slice($request->file('photos'), 0, $maxPhotos) as $file) {
                // Store file temporarily (Hardcoded Absolute Path approach)
                $tempDir = '/www/wwwroot/sebatam.com/craig/storage/app/private/temp_uploads';
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }
                
                $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($tempDir, $fileName);
                $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

                // Dispatch Job
                ProcessListingImageUpload::dispatch($fullPath, $listing->id, 'foto_fitur', $fileName);
            }
        }

        // Process Categories from Tagify
        $categoryIds = [];
        if ($request->filled('categories')) {
            $tagifyCategories = json_decode($request->categories, true);
            $maxAllowed = $isPremiumPackage ? get_setting('max_category_premium', 10) : get_setting('max_category', 3);
            
            foreach (array_slice($tagifyCategories, 0, $maxAllowed) as $cat) {
                $categoryName = $cat['value'];
                $category = \App\Models\Category::firstOrCreate(
                    ['name' => $categoryName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($categoryName),
                        'icon' => 'fa-solid fa-tag',
                        'sort_order' => \App\Models\Category::max('sort_order') + 1
                    ]
                );
                $categoryIds[] = $category->id;
            }
        }

        $listing->categories()->sync($categoryIds);

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
                    $listing->update(['is_premium' => \DB::raw('true')]);
                }
                
                $premiumRequest->update($updateData);
                return redirect()->route('dashboard')->with('success', 'Iklan berhasil dibuat dan dihubungkan dengan paket premium Anda.');
            }
        }

        if ($isPremiumPackage) {
            return redirect()->route('dashboard.premium.upgrade', $listing->id)->with('success', 'Iklan berhasil dibuat. Silakan pilih paket premium untuk mengaktifkan fitur premium.');
        }

        return redirect()->route('dashboard')->with('success', 'Iklan Anda berhasil dikirim dan ditayangkan.');
    }

    public function edit($id)
    {
        $listing = \App\Models\Listing::with('photos')->where('user_id', auth()->id())->findOrFail($id);
        $categories = \App\Models\Category::orderBy('sort_order')->get();
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();
        $districts = \App\Models\District::orderBy('name')->get();

        return view('listings.edit', compact('listing', 'categories', 'listingTypes', 'districts'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'categories' => 'nullable|string',
            'listing_type_id' => 'required|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:' . ($listing->is_premium ? get_setting('huruf_deskripsi_iklan_premium', 2000) : get_setting('huruf_deskripsi_iklan', 100)),
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'whatsapp_visibility' => 'required|integer|in:0,1,2',
            'comment_visibility' => 'required|integer|in:0,1,2',
            'website' => 'nullable|url|max:255',
        ]);

        if ($data['title'] !== $listing->title) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        }

        $listing->update($data);

        // Upload New Photos
        if ($request->hasFile('photos')) {
            $currentCount = $listing->photos()->count();
            
            // Determine limit based on type
            $maxPhotos = get_setting('max_foto_iklan', 4);
            $type = \App\Models\ListingType::find($request->listing_type_id);
            if (($type && $type->slug == 'premium') || $listing->is_premium) {
                $maxPhotos = get_setting('max_foto_iklan_premium', 12);
            }
            
            $remaining = $maxPhotos - $currentCount;

            if ($remaining > 0) {
                $files = $request->file('photos');
                if (!is_array($files)) { $files = [$files]; }
                
                foreach (array_slice($files, 0, $remaining) as $file) {
                    // Store file temporarily
                    $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                    $tempPath = $file->storeAs('temp_uploads', $fileName);
                    $fullPath = storage_path('app/' . $tempPath);

                    // Dispatch Job
                    ProcessListingImageUpload::dispatch($fullPath, $listing->id, 'foto_fitur', $fileName);
                }
                
                if (count($files) > $remaining) {
                    session()->flash('warning', 'Beberapa foto dilewati karena sudah mencapai batas maksimal.');
                }
            } else {
                session()->flash('error', 'Gagal menambah foto: Jatah foto sudah penuh.');
            }
        }

        // Process Categories from Tagify
        $categoryIds = [];
        if ($request->filled('categories')) {
            $tagifyCategories = json_decode($request->categories, true);
            $maxAllowed = $listing->is_premium ? get_setting('max_category_premium', 10) : get_setting('max_category', 3);
            
            foreach (array_slice($tagifyCategories, 0, $maxAllowed) as $cat) {
                $categoryName = $cat['value'];
                $category = \App\Models\Category::firstOrCreate(
                    ['name' => $categoryName],
                    [
                        'slug' => \Illuminate\Support\Str::slug($categoryName),
                        'icon' => 'fa-solid fa-tag',
                        'sort_order' => \App\Models\Category::max('sort_order') + 1
                    ]
                );
                $categoryIds[] = $category->id;
            }
        }

        $listing->categories()->sync($categoryIds);

        return redirect()->route('dashboard')->with('success', 'Iklan Anda berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($id);
        $listing->is_active = !$listing->is_active;
        $listing->save();

        return back()->with('success', 'Status iklan berhasil diubah.');
    }

    public function destroy($id)
    {
        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($id);
        
        // Delete photos from ImageKit
        foreach ($listing->photos as $photo) {
            if ($photo->ik_file_id) {
                $this->imageService->deleteFileById($photo->ik_file_id);
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

        if ($photo->ik_file_id) {
            $this->imageService->deleteFileById($photo->ik_file_id);
        }

        $photo->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }
}
