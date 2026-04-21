<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        return view('listings.create', compact('categories', 'listingTypes', 'districts', 'layout', 'section'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $rules = [
            'categories' => 'nullable|string',
            'listing_type_id' => 'required|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:' . config('sebatam.huruf_deskripsi_iklan', 100),
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
            'features' => 'nullable|array|max:8',
            'features.*' => 'nullable|string|max:' . config('sebatam.huruf_fitur', 40),
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'whatsapp_visibility' => 'required|integer|in:0,1,2',
            'comment_visibility' => 'required|integer|in:0,1,2',
            'website' => 'nullable|url|max:255',
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

        $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        $data['is_active'] = true;

        $listing = \App\Models\Listing::create($data);

        // Upload Photos
        if ($request->hasFile('photos')) {
            $maxPhotos = config('sebatam.max_foto_iklan', 0);
            // Since it's a new listing, we'll check if it's premium. 
            // BUT wait, premium is usually set AFTER creation or via listing type?
            // Let's check listing type.
            $type = \App\Models\ListingType::find($data['listing_type_id']);
            // If the type is premium, use premium limit.
            // For now, let's assume if it's premium type, it gets the limit.
            if ($type && $type->slug == 'premium') {
                $maxPhotos = config('sebatam.max_foto_iklan_premium', 8);
            }

            foreach (array_slice($request->file('photos'), 0, $maxPhotos) as $file) {
                $this->imageService->uploadListingPhoto($file, $listing->id, 'foto_fitur');
            }
        }

        // Process Categories from Tagify
        $categoryIds = [];
        if ($request->filled('categories')) {
            $tagifyCategories = json_decode($request->categories, true);
            $maxAllowed = config('sebatam.max_category', 3);
            
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
            'description' => 'required|string|max:' . ($listing->is_premium ? config('sebatam.huruf_deskripsi_iklan_premium', 2000) : config('sebatam.huruf_deskripsi_iklan', 100)),
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
            'features' => 'nullable|array|max:8',
            'features.*' => 'nullable|string|max:' . config('sebatam.huruf_fitur', 40),
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
            $maxPhotos = config('sebatam.max_foto_iklan', 4);
            $type = \App\Models\ListingType::find($request->listing_type_id);
            if (($type && $type->slug == 'premium') || $listing->is_premium) {
                $maxPhotos = config('sebatam.max_foto_iklan_premium', 12);
            }
            
            $remaining = $maxPhotos - $currentCount;

            if ($remaining > 0) {
                $files = $request->file('photos');
                if (!is_array($files)) { $files = [$files]; }
                
                foreach (array_slice($files, 0, $remaining) as $file) {
                    $this->imageService->uploadListingPhoto($file, $listing->id, 'foto_fitur');
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
            $maxAllowed = config('sebatam.max_category', 3);
            
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
