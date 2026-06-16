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

        $latestListings = \App\Models\Listing::with(['tags', 'user'])->latest()->take(10)->get();
        
        return view('admin.dashboard', compact('stats', 'latestListings'));
    }

    public function categories(\Illuminate\Http\Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $status = $request->input('status');

        $hasFilters = $request->filled('search') || $request->filled('type') || $request->filled('status');

        if (!$hasFilters) {
            // Default: Hierarchical view
            $parentCategories = \App\Models\Category::whereNull('parent_id')
                ->with(['children' => function($q) {
                    $q->withCount('listings')->orderBy('sort_order');
                }])
                ->withCount('listings')
                ->orderBy('sort_order')
                ->get();

            $categories = collect();
            foreach ($parentCategories as $parent) {
                $categories->push($parent);
                foreach ($parent->children as $child) {
                    $categories->push($child);
                }
            }

            // Orphan subcategories
            $orphanSubcategories = \App\Models\Category::whereNotNull('parent_id')
                ->whereNotIn('parent_id', $parentCategories->pluck('id'))
                ->withCount('listings')
                ->orderBy('sort_order')
                ->get();

            foreach ($orphanSubcategories as $orphan) {
                $categories->push($orphan);
            }
        } else {
            // Filtered view with structured layout
            $categoriesQuery = \App\Models\Category::withCount('listings');

            if ($request->filled('search')) {
                $categoriesQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            if ($request->filled('type')) {
                if ($type === 'parent') {
                    $categoriesQuery->whereNull('parent_id');
                } elseif ($type === 'sub') {
                    $categoriesQuery->whereNotNull('parent_id');
                }
            }

            if ($request->filled('status')) {
                if ($status === 'approved') {
                    $categoriesQuery->whereRaw('is_approved = true');
                } elseif ($status === 'unapproved') {
                    $categoriesQuery->whereRaw('is_approved = false');
                }
            }

            $filteredCategories = $categoriesQuery->orderBy('sort_order')->orderBy('name')->get();

            if ($type === 'sub') {
                $categories = $filteredCategories;
            } else {
                $parentIds = $filteredCategories->whereNull('parent_id')->pluck('id')->toArray();
                $subParentIds = $filteredCategories->whereNotNull('parent_id')->pluck('parent_id')->toArray();
                $allParentIds = array_unique(array_merge($parentIds, $subParentIds));

                $parents = \App\Models\Category::whereNull('parent_id')
                    ->whereIn('id', $allParentIds)
                    ->withCount('listings')
                    ->orderBy('sort_order')
                    ->get();

                $categories = collect();
                foreach ($parents as $parent) {
                    $parentMatches = $filteredCategories->contains('id', $parent->id);
                    
                    if ($parentMatches || $type !== 'parent') {
                        if ($type !== 'sub') {
                            $categories->push($parent);
                        }
                    }

                    if ($type !== 'parent') {
                        $matchingChildren = $filteredCategories->where('parent_id', $parent->id)->sortBy('sort_order');
                        foreach ($matchingChildren as $child) {
                            $categories->push($child);
                        }
                    }
                }

                $matchedOrphans = $filteredCategories->whereNotNull('parent_id')
                    ->whereNotIn('parent_id', $parents->pluck('id'))
                    ->sortBy('sort_order');
                foreach ($matchedOrphans as $orphan) {
                    $categories->push($orphan);
                }
            }
        }

        return view('admin.categories.index', compact('categories'));
    }

    public function createCategory()
    {
        $parentCategories = \App\Models\Category::whereNull('parent_id')->orderBy('sort_order')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function storeCategory(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'icon' => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

        \App\Models\Category::create($data);

        return redirect()->route('admin.categories')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function editCategory($id)
    {
        $category = \App\Models\Category::findOrFail($id);
        $parentCategories = \App\Models\Category::whereNull('parent_id')->where('id', '!=', $id)->orderBy('sort_order')->get();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function updateCategory(\Illuminate\Http\Request $request, $id)
    {
        $category = \App\Models\Category::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$id,
            'icon' => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_approved' => 'nullable|boolean',
            'parent_id' => 'nullable|exists:categories,id',
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
        $query = \App\Models\Listing::query()->with(['tags', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $normalizedSearch = \App\Models\User::normalizeWhatsappNumber($search);
            
            // Mengambil ID listing yang cocok dari Meilisearch
            $listingIds = \App\Models\Listing::search($search)->keys();

            $query->where(function($q) use ($search, $normalizedSearch, $listingIds) {
                $q->whereIn('id', $listingIds);

                $q->orWhereHas('user', function($uQuery) use ($search, $normalizedSearch) {
                    $uQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('whatsapp', 'like', "%{$search}%");

                    if ($normalizedSearch) {
                        $uQuery->orWhere('whatsapp', 'like', "%{$normalizedSearch}%");
                    }
                });
            });
        }

        if ($request->filled('status')) {
            $val = $request->status ? 'true' : 'false';
            $query->whereRaw("is_active = $val");
        }

        $listings = $query->latest()->paginate(20)->withQueryString();

        return view('admin.listings.index', compact('listings'));
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
        $categories = \App\Models\Category::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->whereRaw('is_approved = true')->orderBy('sort_order');
            }])
            ->whereRaw('is_approved = true')
            ->orderBy('sort_order')
            ->get();

        $tags = \App\Models\Tag::orderBy('sort_order')->get();
        $districts = \App\Models\District::orderBy('name')->get();
        $subdistricts = \App\Models\Subdistrict::orderBy('name')->get();
        
        $selectedUserId = $request->query('user_id');
        if ($selectedUserId) {
            $users = \App\Models\User::where('id', $selectedUserId)->get();
        } else {
            // Only fetch latest 20 users for initial view to keep it fast
            $users = \App\Models\User::latest()->take(20)->get();
        }
        
        return view('admin.listings.create', compact('categories', 'tags', 'districts', 'subdistricts', 'users'));
    }

    public function storeListing(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric',
            'district_id' => 'nullable|exists:districts,id',
            'subdistrict_id' => 'nullable|exists:subdistricts,id',
            'address' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'website' => 'nullable|url|max:255',
            'foto_fitur' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'galeri' => 'nullable|array',
            'galeri.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'comment_visibility' => 'nullable|integer|in:0,1,2',
        ]);

        $rawTags = $data['tags'] ?? null;
        unset($data['tags']);

        $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        
        // Iklan yang dibuat admin untuk user selalu tidak langsung aktif (menunggu aktivasi)
        $data['is_active'] = \DB::raw('false');
        $data['expires_at'] = now()->addDays(10);
        $data['activation_code'] = (string) random_int(100000, 999999);
        $data['whatsapp_visibility'] = 2;

        $listing = \App\Models\Listing::create($data);

        // Process Categories from Tagify
        $tagIds = [];
        if ($rawTags) {
            $tagifyTags = json_decode($rawTags, true);
            foreach ($tagifyTags as $cat) {
                $tagName = trim($cat['value']);
                $slug = \Illuminate\Support\Str::slug($tagName);

                $tag = \App\Models\Tag::findOrCreateByName($tagName, true);
                $tagIds[] = $tag->id;
            }
        }

        $listing->tags()->sync($tagIds);
        
        if ($request->filled('category_id')) {
            $listing->categories()->sync([$request->category_id]);
        }
        $listing->updateSearchableField();

        // Upload Foto Fitur
        if ($request->hasFile('foto_fitur')) {
            $file = $request->file('foto_fitur');
            $tempDir = storage_path('app/private/temp_uploads');
            if (!file_exists($tempDir)) { mkdir($tempDir, 0777, true); }
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($tempDir, $fileName);
            $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
            ProcessListingImageUpload::dispatchSync($fullPath, $listing->id, 'foto_fitur', $fileName);
        }

        // Upload Galeri
        if ($request->hasFile('galeri')) {
            foreach ($request->file('galeri') as $file) {
                $tempDir = storage_path('app/private/temp_uploads');
                if (!file_exists($tempDir)) { mkdir($tempDir, 0777, true); }
                $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($tempDir, $fileName);
                $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
                ProcessListingImageUpload::dispatchSync($fullPath, $listing->id, 'galeri', $fileName);
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
        $listing = \App\Models\Listing::with(['photos', 'categories', 'tags'])->findOrFail($id);
        
        $categories = \App\Models\Category::whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->whereRaw('is_approved = true')->orderBy('sort_order');
            }])
            ->whereRaw('is_approved = true')
            ->orderBy('sort_order')
            ->get();

        $tags = \App\Models\Tag::orderBy('sort_order')->get();
        $districts = \App\Models\District::orderBy('name')->get();
        $subdistricts = \App\Models\Subdistrict::orderBy('name')->get();
        return view('admin.listings.edit', compact('listing', 'categories', 'tags', 'districts', 'subdistricts'));
    }

    public function updateListing(\Illuminate\Http\Request $request, $id)
    {
        $listing = \App\Models\Listing::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric',
            'district_id' => 'nullable|exists:districts,id',
            'subdistrict_id' => 'nullable|exists:subdistricts,id',
            'address' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'foto_fitur' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'galeri' => 'nullable|array',
            'galeri.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'comment_visibility' => 'nullable|integer|in:0,1,2',
        ]);

        $rawTags = $data['tags'] ?? null;
        unset($data['tags']);

        if ($data['title'] !== $listing->title) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        }

        $listing->update($data);

        // Process Categories from Tagify
        $tagIds = [];
        if ($rawTags) {
            $tagifyTags = json_decode($rawTags, true);
            foreach ($tagifyTags as $cat) {
                $tagName = trim($cat['value']);
                $slug = \Illuminate\Support\Str::slug($tagName);

                $tag = \App\Models\Tag::findOrCreateByName($tagName, true);
                $tagIds[] = $tag->id;
            }
        }

        $listing->tags()->sync($tagIds);
        
        if ($request->filled('category_id')) {
            $listing->categories()->sync([$request->category_id]);
        }
        $listing->updateSearchableField();

        // Upload Foto Fitur
        if ($request->hasFile('foto_fitur')) {
            $file = $request->file('foto_fitur');
            $tempDir = storage_path('app/private/temp_uploads');
            if (!file_exists($tempDir)) { mkdir($tempDir, 0777, true); }
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($tempDir, $fileName);
            $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
            ProcessListingImageUpload::dispatchSync($fullPath, $listing->id, 'foto_fitur', $fileName);
        }

        // Upload Galeri
        if ($request->hasFile('galeri')) {
            foreach ($request->file('galeri') as $file) {
                $tempDir = storage_path('app/private/temp_uploads');
                if (!file_exists($tempDir)) { mkdir($tempDir, 0777, true); }
                $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($tempDir, $fileName);
                $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
                ProcessListingImageUpload::dispatchSync($fullPath, $listing->id, 'galeri', $fileName);
            }
        }

        return redirect()->back()->with('success', 'Listing berhasil diperbarui.');
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

    public function photos(Request $request)
    {
        $query = \App\Models\ListingPhoto::with(['listing.user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $normalizedSearch = \App\Models\User::normalizeWhatsappNumber($search);
            
            // Mengambil ID listing yang cocok dari Meilisearch
            $listingIds = \App\Models\Listing::search($search)->keys();

            $query->whereHas('listing', function($q) use ($search, $normalizedSearch, $listingIds) {
                $q->where(function($subQ) use ($search, $normalizedSearch, $listingIds) {
                    $subQ->whereIn('id', $listingIds);

                    $subQ->orWhereHas('user', function($uQuery) use ($search, $normalizedSearch) {
                        $uQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('whatsapp', 'like', "%{$search}%");

                        if ($normalizedSearch) {
                            $uQuery->orWhere('whatsapp', 'like', "%{$normalizedSearch}%");
                        }
                    });
                });
            });
        }

        $photos = $query->latest()->paginate(24)->withQueryString();

        return view('admin.photos.index', compact('photos'));
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
            'name' => 'nullable|string|max:255',
        ]);

        $normalizedWa = \App\Models\User::normalizeWhatsappNumber($data['whatsapp']);

        // Check if WA already exists
        if (\App\Models\User::where('whatsapp', $normalizedWa)->exists()) {
            return back()->with('error', 'Nomor WhatsApp ini sudah terdaftar.')->withInput();
        }

        // Generate automatic name and email (Match WhatsappBotService logic)
        $userName = $request->filled('name') ? trim($request->name) : ('user-' . rand(100000, 999999));
        $randomSuffix = rand(100, 999);
        $autoEmail = $normalizedWa . '+' . $randomSuffix . '@sebatam.com';
        
        $randomPassword = \Illuminate\Support\Str::random(16);

        \App\Models\User::create([
            'name' => $userName,
            'whatsapp' => $normalizedWa,
            'email' => $autoEmail,
            'password' => \Illuminate\Support\Facades\Hash::make($randomPassword),
            'is_verified' => \DB::raw('true'),
            'ads_quota' => get_setting('jumlah_iklan_user_default', 1),
        ]);

        return redirect()->route('admin.users')->with('success', "Pengguna baru ({$normalizedWa}) berhasil ditambahkan sebagai {$userName}.");
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
            'whatsapp' => 'required|string|max:20',
        ]);

        $normalizedWa = \App\Models\User::normalizeWhatsappNumber($data['whatsapp']);

        if (!$normalizedWa) {
            return back()->withErrors(['whatsapp' => 'Nomor WhatsApp tidak valid.'])->withInput();
        }

        // Check if normalized WA already exists for another user
        if (\App\Models\User::where('whatsapp', $normalizedWa)->where('id', '!=', $id)->exists()) {
            return back()->withErrors(['whatsapp' => 'Nomor WhatsApp ini sudah terdaftar.'])->withInput();
        }

        $data['whatsapp'] = $normalizedWa;

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
    public function syncMeilisearch()
    {
        try {
            // Import data secara programatis (karena command artisan scout:import bersifat console-only)
            \App\Models\Listing::makeAllSearchable();
            
            // Sinkronkan pengaturan index menggunakan proses CLI
            if (class_exists(\Illuminate\Support\Facades\Process::class)) {
                \Illuminate\Support\Facades\Process::path(base_path())->run('php artisan scout:sync-index-settings');
            } else {
                exec('php artisan scout:sync-index-settings');
            }
            
            return redirect()->back()->with('success', 'Berhasil menyinkronkan data Listing dan pengaturan index ke Meilisearch!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyinkronkan Meilisearch: ' . $e->getMessage());
        }
    }

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

    public function reports(Request $request)
    {
        $query = \App\Models\ListingReport::with(['listing', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reporter_whatsapp', 'like', "%{$search}%")
                  ->orWhereHas('listing', function($lQuery) use ($search) {
                      $lQuery->where('title', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($uQuery) use ($search) {
                      $uQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('whatsapp', 'like', "%{$search}%");
                  });
            });
        }

        $reports = $query->latest()->paginate(20)->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function resolveReport($id)
    {
        $report = \App\Models\ListingReport::findOrFail($id);
        $report->update(['status' => 'resolved']);

        return back()->with('success', 'Laporan berhasil diselesaikan.');
    }

    public function dismissReport($id)
    {
        $report = \App\Models\ListingReport::findOrFail($id);
        $report->update(['status' => 'dismissed']);

        return back()->with('success', 'Laporan berhasil diabaikan.');
    }

    public function contacts(Request $request)
    {
        $query = \App\Models\ContactMessage::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->latest()->paginate(20)->withQueryString();

        return view('admin.contacts.index', compact('messages'));
    }

    public function markContactAsRead($id)
    {
        $message = \App\Models\ContactMessage::findOrFail($id);
        $message->update(['status' => 'read']);

        return back()->with('success', 'Pesan kontak berhasil ditandai sebagai dibaca.');
    }

    public function destroyContact($id)
    {
        $message = \App\Models\ContactMessage::findOrFail($id);
        $message->delete();

        return back()->with('success', 'Pesan kontak berhasil dihapus.');
    }

    /**
     * Menampilkan halaman integrasi n8n listing.
     */
    public function n8nListings()
    {
        return view('admin.n8n.index');
    }

    /**
     * Mengirim data teks dan file (multiple) ke n8n webhook.
     */
    public function sendToN8n(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:50',
            'files' => 'required|array|min:1', // Wajib minimal 1 file gambar
            'files.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // Maksimal 10MB per gambar
        ]);

        $text = $request->input('text');
        $n8nUrl = config('services.n8n.listing_webhook_url');

        try {
            $multipart = [
                [
                    'name' => 'text',
                    'contents' => $text,
                ]
            ];

            // Masukkan multiple files jika ada
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $realPath = $file->getRealPath();
                    $handle = fopen($realPath, 'r');
                    if ($handle !== false) {
                        $multipart[] = [
                            'name' => 'files[]',
                            'contents' => $handle,
                            'filename' => $file->getClientOriginalName(),
                            'headers' => [
                                'Content-Type' => $file->getClientMimeType()
                            ]
                        ];
                    }
                }
            }

            // Kirim POST request ke n8n menggunakan opsi multipart langsung ke Guzzle
            $response = \Illuminate\Support\Facades\Http::asMultipart()
                ->withOptions([
                    'multipart' => $multipart,
                    'laravel_data' => $multipart
                ])->post($n8nUrl);

            // Tutup semua resource handle yang terbuka
            foreach ($multipart as $item) {
                if (isset($item['contents']) && is_resource($item['contents'])) {
                    fclose($item['contents']);
                }
            }

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dikirim ke n8n.',
                    'n8n_response' => $response->json() ?? $response->body()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim ke n8n. Kode Status: ' . $response->status(),
                'details' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('n8n Webhook Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi n8n: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membaca setiap listing yang belum ada tagar dan mengirimkan ID & Deskripsi ke webhook n8n
     */
    public function generateTags(Request $request)
    {
        set_time_limit(0);

        // Ambil limit dari request, default = 1
        $limit = (int) $request->input('limit', 1);
        if ($limit < 1) {
            $limit = 1;
        }

        // Ambil listing yang tidak memiliki tag, batasi sesuai limit
        $listings = \App\Models\Listing::doesntHave('tags')->take($limit)->get();

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        $webhookUrl = config('services.n8n.listing_tagar_webhook_url');

        foreach ($listings as $listing) {
            $processedCount++;
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->post($webhookUrl, [
                    'id' => $listing->id,
                    'description' => $listing->description,
                ]);

                if ($response->successful()) {
                    $successCount++;
                } else {
                    $failedCount++;
                    \Illuminate\Support\Facades\Log::warning("Gagal mengirim listing ID {$listing->id} ke webhook. Status: " . $response->status());
                }
            } catch (\Exception $e) {
                $failedCount++;
                \Illuminate\Support\Facades\Log::error("Kesalahan saat mengirim listing ID {$listing->id} ke webhook: " . $e->getMessage());
            }
        }

        if ($processedCount === 0) {
            return back()->with('success', 'Semua listing sudah memiliki tagar.');
        }

        return back()->with('success', "Proses pembuatan tagar selesai. Total listing diproses: {$processedCount}. Berhasil: {$successCount}, Gagal: {$failedCount}.");
    }

    /**
     * Membaca listing yang belum memiliki kategori dan mengirimkan ID & Deskripsi ke webhook n8n kategori.
     * Ini digunakan oleh Admin untuk menginisiasi proses auto-kategorisasi pada listing (Set Kategori).
     */
    public function setCategory(Request $request)
    {
        set_time_limit(0);

        // Ambil limit dari request, default = 1
        $limit = (int) $request->input('limit', 1);
        if ($limit < 1) {
            $limit = 1;
        }

        // Ambil ID listing yang sedang diproses oleh n8n dari cache
        $processingIds = \Illuminate\Support\Facades\Cache::get('listings_category_processing', []);

        // Filter cache: hanya simpan ID yang BENAR-BENAR belum memiliki kategori
        if (!empty($processingIds)) {
            $stillUncategorizedIds = \App\Models\Listing::whereIn('id', $processingIds)
                ->doesntHave('categories')
                ->pluck('id')
                ->toArray();
            
            if (count($stillUncategorizedIds) !== count($processingIds)) {
                $processingIds = $stillUncategorizedIds;
                \Illuminate\Support\Facades\Cache::put('listings_category_processing', $processingIds, now()->addMinutes(10));
            }
        }

        // Ambil listing yang tidak memiliki kategori dan tidak sedang diproses, batasi sesuai limit
        $listings = \App\Models\Listing::doesntHave('categories')
            ->whereNotIn('id', $processingIds)
            ->take($limit)
            ->get();

        $processedCount = 0;
        $successCount = 0;
        $failedCount = 0;

        $webhookUrl = config('services.n8n.listing_category_webhook_url');

        if (empty($webhookUrl)) {
            return back()->with('error', 'Webhook URL kategori listing (N8N_LISTING_CATEGORY_WEBHOOK_URL) belum dikonfigurasi.');
        }

        $sentIds = [];

        $parentCategories = \App\Models\Category::whereNull('parent_id')
            ->whereRaw('is_approved = true')
            ->pluck('name')
            ->toArray();

        foreach ($listings as $listing) {
            $processedCount++;
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->post($webhookUrl, [
                    'id' => $listing->id,
                    'description' => $listing->description,
                    'induk_kategori' => $parentCategories,
                ]);

                if ($response->successful()) {
                    $successCount++;
                    $sentIds[] = $listing->id;
                } else {
                    $failedCount++;
                    \Illuminate\Support\Facades\Log::warning("Gagal mengirim listing ID {$listing->id} ke webhook kategori. Status: " . $response->status());
                }
            } catch (\Exception $e) {
                $failedCount++;
                \Illuminate\Support\Facades\Log::error("Kesalahan saat mengirim listing ID {$listing->id} ke webhook kategori: " . $e->getMessage());
            }
        }

        // Tambahkan ID yang berhasil dikirim ke cache list pemrosesan
        if (!empty($sentIds)) {
            $processingIds = array_merge($processingIds, $sentIds);
            \Illuminate\Support\Facades\Cache::put('listings_category_processing', $processingIds, now()->addMinutes(10));
        }

        if ($processedCount === 0) {
            return back()->with('success', 'Semua listing sudah memiliki kategori atau sedang diproses oleh n8n.');
        }

        return back()->with('success', "Proses pengaturan kategori selesai. Total listing diproses: {$processedCount}. Berhasil: {$successCount}, Gagal: {$failedCount}.");
    }

    public function clearCategories()
    {
        \Illuminate\Support\Facades\DB::table('category_listing')->truncate();
        \Illuminate\Support\Facades\Cache::forget('listings_category_processing');

        return back()->with('success', 'Semua kategori pada seluruh listing berhasil dihapus.');
    }

    public function showDeduplicateTags()
    {
        $allTags = \App\Models\Tag::all();
        
        $groups = $allTags->groupBy(function ($tag) {
            return str_replace(' ', '', strtolower($tag->name));
        });
        
        $duplicateGroups = [];
        $totalDuplicatesCount = 0;
        
        foreach ($groups as $normalized => $group) {
            if ($group->count() > 1) {
                // Sort them similar to the deduplicate method to show which one will be kept
                $sorted = $group->sort(function ($a, $b) {
                    if ($a->is_approved !== $b->is_approved) {
                        return $b->is_approved <=> $a->is_approved;
                    }
                    
                    $aCount = $a->listings()->count();
                    $bCount = $b->listings()->count();
                    if ($aCount !== $bCount) {
                        return $bCount <=> $aCount;
                    }
                    
                    $aHasSpace = strpos($a->name, ' ') !== false;
                    $bHasSpace = strpos($b->name, ' ') !== false;
                    if ($aHasSpace !== $bHasSpace) {
                        return $bHasSpace <=> $aHasSpace;
                    }
                    
                    return $a->id <=> $b->id;
                });
                
                $primary = $sorted->first();
                $duplicates = $sorted->slice(1);
                
                $duplicateGroups[] = [
                    'primary' => $primary,
                    'duplicates' => $duplicates,
                    'count' => $group->count(),
                ];
                
                $totalDuplicatesCount += $duplicates->count();
            }
        }

        // Filter tagar terlarang yang mengandung nama kecamatan
        $forbiddenTags = $allTags->filter(function($tag) {
            return \App\Models\Tag::isForbidden($tag->name);
        })->values();
        
        return view('admin.tags.deduplicate', compact('duplicateGroups', 'totalDuplicatesCount', 'allTags', 'forbiddenTags'));
    }

    public function runDeduplicateTags()
    {
        $result = \App\Models\Tag::deduplicate();
        
        $mergedCount = count($result['merged'] ?? []);
        $cleanedCount = count($result['cleaned'] ?? []);
        
        $msg = "Proses pembersihan tagar selesai! Berhasil menggabungkan {$mergedCount} grup tagar duplikat";
        if ($cleanedCount > 0) {
            $msg .= " dan menghapus {$cleanedCount} tagar terlarang yang mengandung nama kecamatan.";
        } else {
            $msg .= ".";
        }
        
        return redirect()->route('admin.tags.deduplicate')->with('success', $msg);
    }

    public function createListingByJson()
    {
        return view('admin.listings.create_json');
    }

    public function storeListingByJson(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'json_data' => 'required|string',
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'website' => 'nullable|string|max:255',
        ]);

        $jsonData = trim($request->input('json_data'));
        $decoded = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['json_data' => 'Format JSON tidak valid: ' . json_last_error_msg()])->withInput();
        }

        // Validate required keys in decoded JSON
        $requiredKeys = ['judul', 'alamat', 'keterangan_usaha'];
        if (!empty($decoded['nomor_wa'])) {
            $requiredKeys[] = 'nama';
            $requiredKeys[] = 'nomor_wa';
        }
        foreach ($requiredKeys as $key) {
            if (empty($decoded[$key])) {
                return back()->withErrors(['json_data' => "Key '{$key}' wajib diisi di dalam JSON."])->withInput();
            }
        }

        // Check and normalize WhatsApp if provided
        $normalizedWa = null;
        if (!empty($decoded['nomor_wa'])) {
            $nomorWa = $decoded['nomor_wa'];
            $normalizedWa = \App\Models\User::normalizeWhatsappNumber($nomorWa);
            if (!$normalizedWa) {
                return back()->withErrors(['json_data' => 'Nomor WhatsApp di dalam JSON tidak valid.'])->withInput();
            }

            // Check if WA already exists
            if (\App\Models\User::where('whatsapp', $normalizedWa)->exists()) {
                return back()->with('error', 'Nomor WhatsApp ini sudah terdaftar.')->withInput();
            }
        }

        $fileDetails = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $tempDir = storage_path('app/private/temp_uploads');
            if (!file_exists($tempDir)) { 
                mkdir($tempDir, 0777, true); 
            }
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($tempDir, $fileName);
            $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;
            $fileDetails = [
                'fullPath' => $fullPath,
                'fileName' => $fileName,
            ];
        }

        $website = $request->input('website');

        try {
            $result = \Illuminate\Support\Facades\DB::transaction(function () use ($decoded, $normalizedWa, $website) {
                if ($normalizedWa) {
                    // Generate automatic email (Match WhatsappBotService logic)
                    $randomSuffix = rand(100, 999);
                    $autoEmail = $normalizedWa . '+' . $randomSuffix . '@sebatam.com';
                    $randomPassword = \Illuminate\Support\Str::random(16);

                    $user = \App\Models\User::create([
                        'name' => trim($decoded['nama']),
                        'whatsapp' => $normalizedWa,
                        'email' => $autoEmail,
                        'password' => \Illuminate\Support\Facades\Hash::make($randomPassword),
                        'is_verified' => \DB::raw('true'),
                        'ads_quota' => get_setting('jumlah_iklan_user_default', 1),
                    ]);
                } else {
                    $user = auth()->user();
                }

                // Create listing
                $listingData = [
                    'user_id' => $user->id,
                    'title' => trim($decoded['judul']),
                    'description' => trim($decoded['keterangan_usaha']),
                    'address' => trim($decoded['alamat']),
                    'slug' => \Illuminate\Support\Str::slug(trim($decoded['judul']) . '-' . uniqid()),
                    'is_active' => \DB::raw('true'),
                    'expires_at' => now()->addDays((int)get_setting('expire_iklan', 30)),
                    'whatsapp_visibility' => 2,
                    'website' => $website ? trim($website) : null,
                ];

                $listing = \App\Models\Listing::create($listingData);
                $listing->updateSearchableField();

                return [$user, $listing];
            });

            $userModel = $result[0];
            $listingModel = $result[1];

            // Upload Foto after transaction commits successfully
            if ($fileDetails) {
                ProcessListingImageUpload::dispatchSync($fileDetails['fullPath'], $listingModel->id, 'foto_fitur', $fileDetails['fileName']);
            }

            // Trigger tag generation webhook
            $webhookUrl = config('services.n8n.listing_tagar_webhook_url');
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->post($webhookUrl, [
                    'id' => $listingModel->id,
                    'description' => $listingModel->description,
                ]);

                if (!$response->successful()) {
                    \Illuminate\Support\Facades\Log::warning("Gagal mengirim listing ID {$listingModel->id} ke webhook buat tagar. Status: " . $response->status());
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Kesalahan saat mengirim listing ID {$listingModel->id} ke webhook buat tagar: " . $e->getMessage());
            }

            if ($normalizedWa) {
                return redirect()->route('admin.listings')->with('success', "Akun pengguna ({$normalizedWa}) dan listing '{$listingModel->title}' berhasil dibuat. Tagar otomatis sedang diproses.");
            } else {
                return redirect()->route('admin.listings')->with('success', "Listing '{$listingModel->title}' berhasil dibuat menggunakan akun admin Anda. Tagar otomatis sedang diproses.");
            }

        } catch (\Exception $e) {
            // If transaction failed but file was moved, cleanup
            if ($fileDetails && file_exists($fileDetails['fullPath'])) {
                @unlink($fileDetails['fullPath']);
            }
            \Illuminate\Support\Facades\Log::error("Gagal memproses Listing By JSON: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    public function compressImagesManually(\Illuminate\Http\Request $request, \App\Services\ImageService $imageService)
    {
        // Limit processing to 50 items per request to avoid timeouts
        $limitKB = (int) $request->input('limit_kb', 100);
        $maxItems = (int) $request->input('max_items', 50);

        try {
            $count = $imageService->compressLargeImages($limitKB, $maxItems);
            
            if ($count > 0) {
                return back()->with('success', "Berhasil mengompres {$count} gambar.");
            } else {
                return back()->with('success', "Tidak ada gambar berukuran lebih dari {$limitKB}KB yang perlu dikompres.");
            }
        } catch (\Exception $e) {
            return back()->with('error', "Terjadi kesalahan saat mengompres gambar: " . $e->getMessage());
        }
    }
}

