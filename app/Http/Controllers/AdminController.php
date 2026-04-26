<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WhatsappService;


class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total' => \App\Models\Listing::count(),
            'active' => \App\Models\Listing::whereRaw('is_active = true')->count(),
            'featured' => \App\Models\Listing::whereRaw('is_featured = true')->count(),
            'premium' => \App\Models\Listing::whereRaw('is_premium = true')->count(),
            'users' => \App\Models\User::count(),
            'categories' => \App\Models\Category::count(),
            'pending_premium' => \App\Models\PremiumRequest::where('status', 'pending')->count(),
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
        ]);

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

    public function createListing()
    {
        $categories = \App\Models\Category::orderBy('sort_order')->get();
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->orderBy('name')->get();
        $districts = \App\Models\District::orderBy('name')->get();
        return view('admin.listings.create', compact('categories', 'listingTypes', 'districts'));
    }

    public function storeListing(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'category_other' => 'required_without:category_ids|nullable|string|max:255',
            'listing_type_id' => 'required|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
        ]);

        $categoryOther = $data['category_other'] ?? null;
        unset($data['category_other'], $data['category_ids']);

        $data['user_id'] = auth()->id();
        $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        $data['is_active'] = \DB::raw('true');

        $listing = \App\Models\Listing::create($data);

        $categoryIds = $request->category_ids ?? [];
        if ($categoryOther) {
            $newCategory = \App\Models\Category::firstOrCreate(
                ['name' => $categoryOther],
                [
                    'slug' => \Illuminate\Support\Str::slug($categoryOther),
                    'icon' => 'fa-solid fa-tag',
                    'sort_order' => \App\Models\Category::max('sort_order') + 1
                ]
            );
            $categoryIds[] = $newCategory->id;
        }

        $listing->categories()->sync($categoryIds);

        return redirect()->route('admin.listings')->with('success', 'Listing berhasil dibuat.');
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'category_other' => 'required_without:category_ids|nullable|string|max:255',
            'listing_type_id' => 'required|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric',
            'district_id' => 'required|exists:districts,id',
        ]);

        $categoryOther = $data['category_other'] ?? null;
        unset($data['category_other'], $data['category_ids']);

        if ($data['title'] !== $listing->title) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        }

        $listing->update($data);

        $categoryIds = $request->category_ids ?? [];
        if ($categoryOther) {
            $newCategory = \App\Models\Category::firstOrCreate(
                ['name' => $categoryOther],
                [
                    'slug' => \Illuminate\Support\Str::slug($categoryOther),
                    'icon' => 'fa-solid fa-tag',
                    'sort_order' => \App\Models\Category::max('sort_order') + 1
                ]
            );
            $categoryIds[] = $newCategory->id;
        }

        $listing->categories()->sync($categoryIds);

        return redirect()->route('admin.listings')->with('success', 'Listing berhasil diperbarui.');
    }

    public function destroyListing($id)
    {
        $listing = \App\Models\Listing::findOrFail($id);
        $listing->delete();

        return redirect()->route('admin.listings')->with('success', 'Listing berhasil dihapus.');
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
        if ($id == auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah status admin Anda sendiri.');
        }

        $user = \App\Models\User::findOrFail($id);
        $newStatus = $user->is_admin ? 'false' : 'true';
        $user->update(['is_admin' => \DB::raw($newStatus)]);

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

    public function toggleListingStatus($id)
    {
        $listing = \App\Models\Listing::findOrFail($id);
        $newStatus = $listing->is_active ? 'false' : 'true';
        $listing->update(['is_active' => \DB::raw($newStatus)]);

        return back()->with('success', 'Status listing berhasil diubah.');
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
            $listing->update(['is_premium' => \DB::raw('true')]);
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
            $listing->update(['is_premium' => \DB::raw('false')]);
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
            $listing->update(['is_premium' => \DB::raw('false')]);
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
        $newStatus = $user->is_verified ? 'false' : 'true';
        $user->update(['is_verified' => \DB::raw($newStatus)]);

        return back()->with('success', 'Status verifikasi akun berhasil diubah.');
    }

    public function whatsappForm()
    {
        return view('admin.whatsapp.index');
    }

    public function sendWaMessage(Request $request, WhatsappService $whatsappService)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $response = $whatsappService->sendMessage($request->phone, $request->message);

        if ($response) {
            return back()->with('success', 'Pesan WhatsApp berhasil dikirim.');
        }

        return back()->with('error', 'Gagal mengirim pesan WhatsApp. Pastikan layanan API WA aktif.');
    }
}
