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

        return view('listings.create', compact('categories', 'listingTypes'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'category_ids' => 'nullable|array|max:' . config('sebatam.max_category', 3),
            'category_ids.*' => 'exists:categories,id',
            'category_other' => 'required_without:category_ids|nullable|string|max:255',
            'listing_type_id' => 'required|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'features' => 'nullable|array|max:8',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240', // Max 10MB upload
        ]);

        $data['user_id'] = auth()->id();
        $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        $data['is_active'] = true;

        $listing = \App\Models\Listing::create($data);

        $categoryIds = $request->category_ids ?? [];
        $maxAllowed = config('sebatam.max_category', 3);

        if ($request->filled('category_other') && count($categoryIds) < $maxAllowed) {
            $newCategory = \App\Models\Category::firstOrCreate(
                ['name' => $request->category_other],
                [
                    'slug' => \Illuminate\Support\Str::slug($request->category_other),
                    'icon' => 'fa-solid fa-tag',
                    'sort_order' => \App\Models\Category::max('sort_order') + 1
                ]
            );
            $categoryIds[] = $newCategory->id;
        }

        $listing->categories()->sync($categoryIds);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $file) {
                // Defaulting new uploads to 'foto_fitur'
                $this->imageService->uploadListingPhoto($file, $listing->id, 'foto_fitur');
            }
        }

        return redirect()->route('dashboard')->with('success', 'Iklan Anda berhasil dikirim dan ditayangkan.');
    }

    public function edit($id)
    {
        $listing = \App\Models\Listing::with('photos')->where('user_id', auth()->id())->findOrFail($id);
        $categories = \App\Models\Category::orderBy('sort_order')->get();
        $listingTypes = \App\Models\ListingType::orderBy('sort_order')->get();

        return view('listings.edit', compact('listing', 'categories', 'listingTypes'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $listing = \App\Models\Listing::where('user_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'category_ids' => 'nullable|array|max:' . config('sebatam.max_category', 3),
            'category_ids.*' => 'exists:categories,id',
            'category_other' => 'required_without:category_ids|nullable|string|max:255',
            'listing_type_id' => 'required|exists:listing_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'features' => 'nullable|array|max:8',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        if ($data['title'] !== $listing->title) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title'] . '-' . uniqid());
        }

        $listing->update($data);
        
        $categoryIds = $request->category_ids ?? [];
        $maxAllowed = config('sebatam.max_category', 3);

        if ($request->filled('category_other') && count($categoryIds) < $maxAllowed) {
            $newCategory = \App\Models\Category::firstOrCreate(
                ['name' => $request->category_other],
                [
                    'slug' => \Illuminate\Support\Str::slug($request->category_other),
                    'icon' => 'fa-solid fa-tag',
                    'sort_order' => \App\Models\Category::max('sort_order') + 1
                ]
            );
            $categoryIds[] = $newCategory->id;
        }

        $listing->categories()->sync($categoryIds);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $file) {
                // If the listing already has a featured photo, maybe these should be something else?
                // For now, we'll keep adding to 'foto_fitur' as requested
                $this->imageService->uploadListingPhoto($file, $listing->id, 'foto_fitur');
            }
        }

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
        $listing->delete();

        return redirect()->route('dashboard')->with('success', 'Iklan Anda telah dihapus.');
    }
}
