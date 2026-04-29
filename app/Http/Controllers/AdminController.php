<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessListingImageUpload;
use App\Services\WhatsappService;
use App\Models\WhatsappLog;



class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => \App\Models\User::count(),
            'categories' => \App\Models\Category::count(),
            'listings' => \App\Models\Listing::count(),
            'featured' => \App\Models\Listing::whereRaw('is_featured = true')->count(),
        ];

        $latestListings = \App\Models\Listing::with(['categories', 'user'])->latest()->take(10)->get();
        
        return view('admin.dashboard', compact('stats', 'latestListings'));
    }

    public function categories()
    {
        $categories = \App\Models\Category::withCount('listings')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.categories.create');
    }

    public function storeCategory(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'icon' => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

        \App\Models\Category::create($data);

        return redirect()->route('admin.categories')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function editCategory($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function updateCategory(\Illuminate\Http\Request $request, $id)
    {
        $category = \App\Models\Category::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$id,
            'icon' => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_approved' => 'nullable|boolean',
        ]);

        $data['is_approved'] = $request->has('is_approved') ? \DB::raw('true') : \DB::raw('false');


        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

        $category->update($data);

        return redirect()->route('admin.categories')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        
        if ($category->listings()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki listing.');
        }

        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'Kategori berhasil dihapus.');
    }

    public function toggleCategoryApproval($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        $newStatusSql = $category->is_approved ? 'false' : 'true';
        
        \DB::update("UPDATE categories SET is_approved = $newStatusSql, updated_at = NOW() WHERE id = ?", [$id]);

        return back()->with('success', 'Status persetujuan kategori berhasil diubah.');
    }



    public function listings(Request $request)
    {
        $query = \App\Models\Listing::query()->with(['categories', 'user', 'listingType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('district', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('listing_type_id')) {
            $query->where('listing_type_id', $request->listing_type_id);
        }

        if ($request->filled('status')) {
            $val = $request->status ? 'true' : 'false';
            $query->whereRaw("is_active = $val");
        }

        $listings = $query->latest()->paginate(20)->withQueryString();
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.listings.index', compact('listings', 'listingTypes'));
    }

    public function searchUsers(Request $request)
    {
        $search = $request->q;
        $users = \App\Models\User::where('name', 'like', "%{$search}%")
            ->orWhere('whatsapp', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->latest()
            ->take(10)
            ->get(['id', 'name', 'whatsapp']);

        return response()->json($users->map(function($user) {
            return [
                'id' => $user->id,
                'text' => $user->name . ' (' . $user->whatsapp . ')'
            ];
        }));
    }

    public function createListing(Request $request)
    {
        $categories = \App\Models\Category::orderBy('sort_order')->get();
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->orderBy('name')->get();
        $districts = \App\Models\District::orderBy('name')->get();
        
        $selectedUserId = $request->query('user_id');
        if ($selectedUserId) {
            $users = \App\Models\User::where('id', $selectedUserId)->get();
        } else {
            // Only fetch latest 20 users for initial view to keep it fast
            $users = \App\Models\User::latest()->take(20)->get();
        }
        
        return view('admin.listings.create', compact('categories', 'listingTypes', 'districts', 'users'));
    }

    public function storeListing(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'categories' => 'nullable|string',
            'listing_type_id' => 'required|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
            'user_id' => 'required|exists:users,id',
            'website' => 'nullable|url|max:255',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $rawCategories = $data['categories'] ?? null;
        unset($data['categories']);

        $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        
        // Iklan yang dibuat admin untuk user selalu tidak langsung aktif (menunggu aktivasi)
        $data['is_active'] = \DB::raw('false');
        $data['expires_at'] = now()->addDays(10);
        $data['activation_code'] = (string) random_int(100000, 999999);

        $listing = \App\Models\Listing::create($data);

        // Process Categories from Tagify
        $categoryIds = [];
        if ($rawCategories) {
            $tagifyCategories = json_decode($rawCategories, true);
            foreach ($tagifyCategories as $cat) {
                $categoryName = trim($cat['value']);
                $slug = \Illuminate\Support\Str::slug($categoryName);

                $category = \App\Models\Category::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
                    ->orWhere('slug', $slug)
                    ->first();

                if (!$category) {
                    $category = \App\Models\Category::create([
                        'name' => $categoryName,
                        'slug' => $slug,
                        'icon' => 'fa-solid fa-tag',
                        'sort_order' => (int)\App\Models\Category::max('sort_order') + 1,
                        'is_approved' => \DB::raw('true')
                    ]);
                }
                $categoryIds[] = $category->id;
            }
        }

        $listing->categories()->sync($categoryIds);

        // Upload Photos
        if ($request->hasFile('photos')) {
            $maxPhotos = 12; // Admin has higher limit or just use a safe number
            foreach (array_slice($request->file('photos'), 0, $maxPhotos) as $file) {
                // Store file temporarily
                $tempDir = storage_path('app/private/temp_uploads');
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

        $msg = 'Listing berhasil dibuat.';
        if (!$listing->is_active && $listing->activation_code) {
            $url = route('listings.show', ['slug' => $listing->slug, 'code' => $listing->activation_code]);
            $msg .= " Kode Aktivasi: <strong>{$listing->activation_code}</strong>. <br>Link Aktivasi: <a href='{$url}' target='_blank'>{$url}</a>";
        }

        return redirect()->route('admin.listings')->with('success', $msg);
    }

    public function editListing($id)
    {
        $listing = \App\Models\Listing::findOrFail($id);
        $categories = \App\Models\Category::orderBy('sort_order')->get();
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->orderBy('name')->get();
        $districts = \App\Models\District::orderBy('name')->get();
        return view('admin.listings.edit', compact('listing', 'categories', 'listingTypes', 'districts'));
    }

    public function updateListing(\Illuminate\Http\Request $request, $id)
    {
        $listing = \App\Models\Listing::findOrFail($id);

        $data = $request->validate([
            'categories' => 'nullable|string',
            'listing_type_id' => 'required|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
            'website' => 'nullable|url|max:255',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $rawCategories = $data['categories'] ?? null;
        unset($data['categories']);

        if ($data['title'] !== $listing->title) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        }

        $listing->update($data);

        // Process Categories from Tagify
        $categoryIds = [];
        if ($rawCategories) {
            $tagifyCategories = json_decode($rawCategories, true);
            foreach ($tagifyCategories as $cat) {
                $categoryName = trim($cat['value']);
                $slug = \Illuminate\Support\Str::slug($categoryName);

                $category = \App\Models\Category::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
                    ->orWhere('slug', $slug)
                    ->first();

                if (!$category) {
                    $category = \App\Models\Category::create([
                        'name' => $categoryName,
                        'slug' => $slug,
                        'icon' => 'fa-solid fa-tag',
                        'sort_order' => (int)\App\Models\Category::max('sort_order') + 1,
                        'is_approved' => \DB::raw('true')
                    ]);
                }
                $categoryIds[] = $category->id;
            }
        }

        $listing->categories()->sync($categoryIds);

        // Upload Photos
        if ($request->hasFile('photos')) {
            $currentCount = $listing->photos()->count();
            $maxPhotos = 12; 
            $remaining = $maxPhotos - $currentCount;

            if ($remaining > 0) {
                foreach (array_slice($request->file('photos'), 0, $remaining) as $file) {
                    // Store file temporarily
                    $tempDir = storage_path('app/private/temp_uploads');
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
        }

        return redirect()->route('admin.listings')->with('success', 'Listing berhasil diperbarui.');
    }

    public function destroyListing($id)
    {
        $listing = \App\Models\Listing::findOrFail($id);
        $listing->delete();

        return redirect()->route('admin.listings')->with('success', 'Listing berhasil dihapus.');
    }

    public function deleteListingPhoto($id)
    {
        $photo = \App\Models\ListingPhoto::findOrFail($id);
        
        // Use ImageService if possible, or just delete if it's simpler
        // We need ImageService to delete from ImageKit
        $imageService = app(\App\Services\ImageService::class);
        
        if ($photo->ik_file_id) {
            $imageService->deleteFileById($photo->ik_file_id);
        }

        $photo->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    public function toggleListingStatus($id)
    {
        $listing = \App\Models\Listing::findOrFail($id);
        
        if (!$listing->is_active) {
            // Activating
            $days = (int)get_setting('expire_iklan', 30);
            $expiresAt = now()->addDays($days)->toDateTimeString();
            
            \DB::update("UPDATE listings SET is_active = true, expires_at = ?, activation_code = NULL, updated_at = NOW() WHERE id = ?", [$expiresAt, $id]);
            
            $statusText = 'diaktifkan';
        } else {
            // Deactivating
            \DB::update("UPDATE listings SET is_active = false, updated_at = NOW() WHERE id = ?", [$id]);
            $statusText = 'dinonaktifkan';
        }

        return back()->with('success', "Listing #{$listing->id} ({$listing->title}) berhasil {$statusText}.");
    }

    public function users(Request $request)
    {
        $query = \App\Models\User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'whatsapp' => 'required|string|max:20',
        ]);

        $normalizedWa = \App\Models\User::normalizeWhatsappNumber($data['whatsapp']);

        // Check if WA already exists
        if (\App\Models\User::where('whatsapp', $normalizedWa)->exists()) {
            return back()->with('error', 'Nomor WhatsApp ini sudah terdaftar.')->withInput();
        }

        // Generate automatic name and email (Match WhatsappBotService logic)
        $randomName = 'user-' . rand(100000, 999999);
        $randomSuffix = rand(100, 999);
        $autoEmail = $normalizedWa . '+' . $randomSuffix . '@sebatam.com';
        
        $randomPassword = \Illuminate\Support\Str::random(16);

        \App\Models\User::create([
            'name' => $randomName,
            'whatsapp' => $normalizedWa,
            'email' => $autoEmail,
            'password' => \Illuminate\Support\Facades\Hash::make($randomPassword),
            'is_verified' => \DB::raw('true'),
            'ads_quota' => get_setting('jumlah_iklan_user_default', 1),
        ]);

        return redirect()->route('admin.users')->with('success', "Pengguna baru ({$normalizedWa}) berhasil ditambahkan sebagai {$randomName}.");
    }

    public function editUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(\Illuminate\Http\Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
        ]);

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleAdminStatus($id)
    {
        if (auth()->id() == $id) {
            return back()->with('error', 'Anda tidak bisa mengubah status admin Anda sendiri.');
        }

        $user = \App\Models\User::findOrFail($id);
        $newStatusSql = $user->is_admin ? 'false' : 'true';
        
        \DB::update("UPDATE users SET is_admin = $newStatusSql, updated_at = NOW() WHERE id = ?", [$id]);

        return back()->with('success', 'Status peran pengguna berhasil diubah.');
    }

    public function destroyUser($id)
    {
        if ($id == auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = \App\Models\User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    public function slotManagement(Request $request)
    {
        $query = \App\Models\User::query()->withCount([
            'listings', 
            'listings as active_listings_count' => function($q) {
                $q->whereRaw('is_active = true');
            }
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $totalUsers = \App\Models\User::count();
        $totalQuota = \App\Models\User::sum('ads_quota');
        $zeroQuotaUsers = \App\Models\User::where('ads_quota', '<=', 0)->count();

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.users.slot', compact('users', 'totalUsers', 'totalQuota', 'zeroQuotaUsers'));
    }

    public function updateSingleSlot(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => 'required|in:add,set,reduce',
            'amount' => 'required|integer|min:0',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $amount = (int) $request->amount;

        if ($request->action === 'add') {
            $user->increment('ads_quota', $amount);
            $msg = "Berhasil menambah {$amount} slot untuk {$user->name}.";
        } elseif ($request->action === 'set') {
            $user->update(['ads_quota' => $amount]);
            $msg = "Berhasil mengatur slot {$user->name} menjadi {$amount}.";
        } elseif ($request->action === 'reduce') {
            $newQuota = max(0, $user->ads_quota - $amount);
            $user->update(['ads_quota' => $newQuota]);
            $msg = "Berhasil mengurangi {$amount} slot untuk {$user->name}.";
        }

        return back()->with('success', $msg);
    }

    public function updateBulkSlot(Request $request)
    {
        $request->validate([
            'action' => 'required|in:add,set,reduce',
            'amount' => 'required|integer|min:0',
        ]);

        $amount = (int) $request->amount;

        if ($request->action === 'add') {
            \App\Models\User::increment('ads_quota', $amount);
            $msg = "Berhasil menambah {$amount} slot untuk SEMUA pengguna.";
        } elseif ($request->action === 'set') {
            \App\Models\User::query()->update(['ads_quota' => $amount]);
            $msg = "Berhasil mengatur slot SEMUA pengguna menjadi {$amount}.";
        } elseif ($request->action === 'reduce') {
            // Using a more complex query to avoid negative values
            \App\Models\User::query()->update([
                'ads_quota' => \DB::raw("CASE WHEN ads_quota - $amount < 0 THEN 0 ELSE ads_quota - $amount END")
            ]);
            $msg = "Berhasil mengurangi {$amount} slot untuk SEMUA pengguna.";
        }

        return back()->with('success', $msg);
    }


    // Listing Types Management
    public function listingTypes()
    {
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.listing_types.index', compact('listingTypes'));
    }

    public function createListingType()
    {
        return view('admin.listing_types.create');
    }

    public function storeListingType(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);
        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        \App\Models\ListingType::create($data);
        return redirect()->route('admin.listing_types')->with('success', 'Tipe listing berhasil ditambahkan.');
    }

    public function editListingType($id)
    {
        $listingType = \App\Models\ListingType::findOrFail($id);
        return view('admin.listing_types.edit', compact('listingType'));
    }

    public function updateListingType(Request $request, $id)
    {
        $listingType = \App\Models\ListingType::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);
        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        $listingType->update($data);
        return redirect()->route('admin.listing_types')->with('success', 'Tipe listing berhasil diperbarui.');
    }

    public function destroyListingType($id)
    {
        $listingType = \App\Models\ListingType::findOrFail($id);
        if ($listingType->listings()->count() > 0) {
            return back()->with('error', 'Tipe listing tidak dapat dihapus karena masih digunakan oleh listing.');
        }
        $listingType->delete();
        return redirect()->route('admin.listing_types')->with('success', 'Tipe listing berhasil dihapus.');
    }

    // Premium Packages
    public function premiumPackages()
    {
        $packages = \App\Models\PremiumPackage::orderBy('price')->get();
        return view('admin.premium_packages.index', compact('packages'));
    }

    public function createPremiumPackage()
    {
        return view('admin.premium_packages.create');
    }

    public function storePremiumPackage(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);
        \App\Models\PremiumPackage::create($data);
        return redirect()->route('admin.premium_packages')->with('success', 'Paket premium berhasil ditambahkan.');
    }

    public function editPremiumPackage($id)
    {
        $package = \App\Models\PremiumPackage::findOrFail($id);
        return view('admin.premium_packages.edit', compact('package'));
    }

    public function updatePremiumPackage(Request $request, $id)
    {
        $package = \App\Models\PremiumPackage::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);
        $data['is_active'] = $request->is_active ? \DB::raw('true') : \DB::raw('false');
        $package->update($data);
        return redirect()->route('admin.premium_packages')->with('success', 'Paket premium berhasil diperbarui.');
    }

    public function destroyPremiumPackage($id)
    {
        $package = \App\Models\PremiumPackage::findOrFail($id);
        $package->delete();
        return redirect()->route('admin.premium_packages')->with('success', 'Paket premium berhasil dihapus.');
    }

    // Premium Requests
    public function premiumRequests()
    {
        $requests = \App\Models\PremiumRequest::with(['user', 'listing', 'package'])->latest()->get();
        return view('admin.premium_requests.index', compact('requests'));
    }

    public function approvePremiumRequest($id)
    {
        $premiumRequest = \App\Models\PremiumRequest::findOrFail($id);
        $premiumRequest->status = 'active';
        
        // Only set expires_at if listing is already linked
        if ($premiumRequest->listing_id) {
            $premiumRequest->expires_at = now()->addDays((int)$premiumRequest->package->duration_days);
        } else {
            $premiumRequest->expires_at = null; // Stays null until used
        }
        
        $premiumRequest->save();

        // Update listing if already linked
        $listing = $premiumRequest->listing;
        if ($listing) {
            $listing->update([
                'is_premium'   => \DB::raw('true'),
                'listing_rank' => 100,
            ]);
        }

        return back()->with('success', 'Permintaan premium berhasil disetujui.');
    }

    public function resetPremiumRequest($id)
    {
        $premiumRequest = \App\Models\PremiumRequest::findOrFail($id);
        $premiumRequest->status = 'pending';
        $premiumRequest->save();
        
        // Deactivate premium features on listing
        $listing = $premiumRequest->listing;
        if ($listing) {
            // Recalculate rank as a free ad for this user
            $maxFreeRank = \App\Models\Listing::where('user_id', $listing->user_id)
                ->whereRaw('is_premium = false')
                ->where('id', '!=', $listing->id)
                ->max('listing_rank');
            $newRank = $maxFreeRank ? $maxFreeRank + 1000 : 1000;

            $listing->update([
                'is_premium'   => \DB::raw('false'),
                'listing_rank' => $newRank,
            ]);
        }

        return back()->with('success', 'Permintaan premium dikembalikan ke status pending dan fitur premium dinonaktifkan.');
    }

    public function rejectPremiumRequest($id)
    {
        $premiumRequest = \App\Models\PremiumRequest::findOrFail($id);
        $premiumRequest->status = 'rejected';
        $premiumRequest->save();
        
        // Deactivate premium features on listing
        $listing = $premiumRequest->listing;
        if ($listing) {
            // Recalculate rank as a free ad for this user
            $maxFreeRank = \App\Models\Listing::where('user_id', $listing->user_id)
                ->whereRaw('is_premium = false')
                ->where('id', '!=', $listing->id)
                ->max('listing_rank');
            $newRank = $maxFreeRank ? $maxFreeRank + 1000 : 1000;

            $listing->update([
                'is_premium'   => \DB::raw('false'),
                'listing_rank' => $newRank,
            ]);
        }

        return back()->with('success', 'Permintaan premium telah ditolak.');
    }

    // User Verification
    // Settings Management
    public function settings()
    {
        $settings = \App\Models\Setting::all();
        return view('admin.settings.index', compact('settings'));
    }

    public function createSetting()
    {
        return view('admin.settings.create');
    }

    public function storeSetting(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|max:255|unique:settings',
            'value' => 'nullable|string',
            'description' => 'nullable|string|max:255',
        ]);

        \App\Models\Setting::create($data);
        \Illuminate\Support\Facades\Cache::forget("setting.{$data['key']}");

        return redirect()->route('admin.settings')->with('success', 'Parameter berhasil ditambahkan.');
    }

    public function editSetting($id)
    {
        $setting = \App\Models\Setting::findOrFail($id);
        return view('admin.settings.edit', compact('setting'));
    }

    public function updateSetting(Request $request, $id)
    {
        $setting = \App\Models\Setting::findOrFail($id);

        $data = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,'.$id,
            'value' => 'nullable|string',
            'description' => 'nullable|string|max:255',
        ]);

        $setting->update($data);
        \Illuminate\Support\Facades\Cache::forget("setting.{$data['key']}");

        return redirect()->route('admin.settings')->with('success', 'Parameter berhasil diperbarui.');
    }

    public function destroySetting($id)
    {
        $setting = \App\Models\Setting::findOrFail($id);
        $key = $setting->key;
        $setting->delete();
        \Illuminate\Support\Facades\Cache::forget("setting.{$key}");

        return redirect()->route('admin.settings')->with('success', 'Parameter berhasil dihapus.');
    }

    public function toggleUserVerification($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $newStatusSql = $user->is_verified ? 'false' : 'true';
        
        // Direct SQL update to force PG boolean keywords and avoid Eloquent casting issues
        \DB::update("UPDATE users SET is_verified = $newStatusSql, updated_at = NOW() WHERE id = ?", [$id]);

        return back()->with('success', 'Status verifikasi akun berhasil diubah.');
    }

    public function whatsappForm(Request $request)
    {
        $templates = \App\Models\WaTemplate::orderBy('name')->get();
        return view('admin.whatsapp.index', compact('templates'));
    }

    public function whatsappHistory(Request $request)
    {
        $query = WhatsappLog::query();
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('admin_notes', 'like', "%{$search}%");
            });
        }
        
        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.whatsapp.history', compact('logs'));
    }

    public function waTemplates()
    {
        $templates = \App\Models\WaTemplate::latest()->get();
        return view('admin.whatsapp.templates.index', compact('templates'));
    }

    public function createWaTemplate()
    {
        return view('admin.whatsapp.templates.create');
    }

    public function storeWaTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        \App\Models\WaTemplate::create($data);

        return redirect()->route('admin.wa_templates')->with('success', 'Template WA berhasil dibuat.');
    }

    public function editWaTemplate($id)
    {
        $template = \App\Models\WaTemplate::findOrFail($id);
        return view('admin.whatsapp.templates.edit', compact('template'));
    }

    public function updateWaTemplate(Request $request, $id)
    {
        $template = \App\Models\WaTemplate::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $template->update($data);

        return redirect()->route('admin.wa_templates')->with('success', 'Template WA berhasil diperbarui.');
    }

    public function destroyWaTemplate($id)
    {
        $template = \App\Models\WaTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.wa_templates')->with('success', 'Template WA berhasil dihapus.');
    }

    public function sendWaMessage(Request $request, WhatsappService $whatsappService)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $response = $whatsappService->sendMessage($request->phone, $request->message);

        // Record the message in database
        WhatsappLog::create([
            'phone' => $request->phone,
            'message' => $request->message,
            'status' => $response ? 'sent' : 'failed',
        ]);

        if ($response) {
            return back()->with('success', 'Pesan WhatsApp berhasil dikirim.');
        }

        return back()->with('error', 'Gagal mengirim pesan WhatsApp. Pastikan layanan API WA aktif.');
    }

    public function updateWhatsappLog(Request $request, $id)
    {
        $log = WhatsappLog::findOrFail($id);
        $log->update($request->validate([
            'admin_notes' => 'nullable|string',
        ]));

        return back()->with('success', 'Catatan admin berhasil diperbarui.');
    }

    public function destroyWhatsappLog($id)
    {
        $log = WhatsappLog::findOrFail($id);
        $log->delete();

        return back()->with('success', 'Catatan WhatsApp berhasil dihapus.');
    }
}
