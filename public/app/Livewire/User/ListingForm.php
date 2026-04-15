<?php

namespace App\Livewire\User;

use App\Models\Category;
use App\Models\District;
use App\Models\Listing;
use App\Models\Subdistrict;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class ListingForm extends Component
{
    use WithFileUploads;

    public $listingId;

    public $type = 'usaha';

    public $title;

    public $description;

    // Category Selection Levels
    public $cat1; // Level 1 selected

    public $cat2; // Level 2 selected

    public $cat3; // Level 3 selected (final)

    public $category_id; // Final category selected in form (saved to pivot)

    public $district_id;

    public $subdistrict_id;

    public $address;

    public $phone;

    public $whatsapp;

    public $website;

    /** @var array<string, array{closed: bool, open: string, close: string}> */
    public array $openingHoursForm = [];

    // Lapak fields
    public $price;

    public $lapak_type;

    public $item_condition;

    // Media fields
    public $featured_image;

    public $gallery_images = [];

    /** Preview URLs for existing media (edit mode). */
    public ?string $existingFeaturedPreviewUrl = null;

    /** @var list<string> */
    public array $existingGalleryPreviewUrls = [];

    // Sources for Dropdowns
    public $districts;

    public $subdistricts = [];

    public $categories_lvl1;

    public $categories_lvl2 = [];

    public $categories_lvl3 = [];

    public function mount($id = null)
    {
        $this->openingHoursForm = $this->defaultOpeningHoursForm();
        $this->districts = District::all();
        $this->categories_lvl1 = Category::whereNull('parent_id')->get();

        if ($id) {
            $listing = Listing::findOrFail($id);
            if ($listing->user_id !== Auth::id()) {
                abort(403);
            }

            $this->listingId = $listing->id;
            $this->type = $listing->type;
            $this->title = $listing->title;
            $this->description = $listing->description;
            $this->district_id = $listing->district_id;
            $this->subdistrict_id = $listing->subdistrict_id;
            $this->address = $listing->address;
            $this->phone = $listing->phone;
            $this->whatsapp = $listing->whatsapp;
            $this->website = $listing->website;

            $this->price = $listing->price;
            $this->lapak_type = $listing->lapak_type;
            $this->item_condition = $listing->item_condition;

            // Load category hierarchy back
            $currentCat = $listing->categories()->first();
            if ($currentCat) {
                if ($currentCat->level == 3) {
                    $this->cat3 = $currentCat->id;
                    $this->cat2 = $currentCat->parent_id;
                    $parentOfCat2 = Category::find($this->cat2);
                    $this->cat1 = $parentOfCat2 ? $parentOfCat2->parent_id : null;
                } elseif ($currentCat->level == 2) {
                    $this->cat2 = $currentCat->id;
                    $this->cat1 = $currentCat->parent_id;
                } else {
                    $this->cat1 = $currentCat->id;
                }
                $this->loadSubCategories();
            }

            if ($this->district_id) {
                $this->subdistricts = Subdistrict::where('district_id', $this->district_id)->get();
            }

            $listing->loadMissing('media');
            if ($listing->hasMedia('featured')) {
                $this->existingFeaturedPreviewUrl = $listing->getFirstMediaUrl('featured', 'thumbnail')
                    ?: $listing->getFirstMediaUrl('featured');
            }
            foreach ($listing->getMedia('gallery') as $media) {
                $this->existingGalleryPreviewUrls[] = $media->getUrl('thumbnail') ?: $media->getUrl();
            }

            $this->hydrateOpeningHoursFromListing($listing);
        }
    }

    /**
     * @return array<string, array{closed: bool, open: string, close: string}>
     */
    protected function defaultOpeningHoursForm(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $form = [];
        foreach ($days as $d) {
            $form[$d] = ['closed' => false, 'open' => '', 'close' => ''];
        }

        return $form;
    }

    protected function hydrateOpeningHoursFromListing(Listing $listing): void
    {
        $existing = $listing->opening_hours;
        if (! is_array($existing)) {
            return;
        }
        foreach ($this->openingHoursForm as $day => $_) {
            if (! isset($existing[$day])) {
                continue;
            }
            $slot = $existing[$day];
            if (! is_array($slot)) {
                continue;
            }
            if (! empty($slot['closed'])) {
                $this->openingHoursForm[$day]['closed'] = true;
                $this->openingHoursForm[$day]['open'] = '';
                $this->openingHoursForm[$day]['close'] = '';
            } else {
                $this->openingHoursForm[$day]['closed'] = false;
                $this->openingHoursForm[$day]['open'] = (string) ($slot['open'] ?? '');
                $this->openingHoursForm[$day]['close'] = (string) ($slot['close'] ?? '');
            }
        }
    }

    /**
     * @return array<string, array<string, mixed>>|null
     */
    protected function buildOpeningHoursForSave(): ?array
    {
        $out = [];
        foreach ($this->openingHoursForm as $day => $slot) {
            if (! empty($slot['closed'])) {
                $out[$day] = ['closed' => true];
            } elseif (! empty($slot['open']) && ! empty($slot['close'])) {
                $out[$day] = [
                    'open' => $slot['open'],
                    'close' => $slot['close'],
                ];
            }
        }

        return $out === [] ? null : $out;
    }

    public function updatedCat1($value)
    {
        $this->cat2 = null;
        $this->cat3 = null;
        if ($value) {
            $this->categories_lvl2 = Category::where('parent_id', $value)->get();
        } else {
            $this->categories_lvl2 = [];
        }
        $this->categories_lvl3 = [];
    }

    public function updatedCat2($value)
    {
        $this->cat3 = null;
        if ($value) {
            $this->categories_lvl3 = Category::where('parent_id', $value)->get();
        } else {
            $this->categories_lvl3 = [];
        }
    }

    protected function loadSubCategories()
    {
        if ($this->cat1) {
            $this->categories_lvl2 = Category::where('parent_id', $this->cat1)->get();
        }
        if ($this->cat2) {
            $this->categories_lvl3 = Category::where('parent_id', $this->cat2)->get();
        }
    }

    public function updatedDistrictId($value)
    {
        if ($value) {
            $this->subdistricts = Subdistrict::where('district_id', $value)->get();
        } else {
            $this->subdistricts = [];
        }
        $this->subdistrict_id = null;
    }

    public function save($readyToPublish = false)
    {
        // Determine final category_id
        $this->category_id = $this->cat3 ?: ($this->cat2 ?: $this->cat1);

        $rules = [
            'type' => 'required|in:usaha,lapak',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cat1' => 'required|exists:categories,id', // At least lvl 1 must be picked
            'category_id' => 'required|exists:categories,id',
            'district_id' => 'required|exists:districts,id',
            'subdistrict_id' => 'required|exists:subdistricts,id',
            'featured_image' => 'nullable|image|max:2048',
            'gallery_images.*' => 'image|max:2048',
        ];

        if ($this->type === 'lapak') {
            $rules['price'] = 'required|numeric|min:0';
            $rules['lapak_type'] = 'required|string';
            $rules['item_condition'] = 'required|string';
        }

        $this->validate($rules);

        $data = [
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'district_id' => $this->district_id,
            'subdistrict_id' => $this->subdistrict_id,
            'address' => $this->address,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'website' => $this->website,
            'opening_hours' => $this->buildOpeningHoursForSave(),
            'is_draft' => ! $readyToPublish,
        ];

        if (! $this->listingId) {
            $slug = Str::slug($this->title);
            $originalSlug = $slug;
            $count = 1;
            while (Listing::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            $data['slug'] = $slug;
        }

        if ($this->type === 'lapak') {
            $data['price'] = $this->price;
            $data['lapak_type'] = $this->lapak_type;
            $data['item_condition'] = $this->item_condition;
        }

        if ($this->listingId) {
            $listing = Listing::findOrFail($this->listingId);

            // If saved as draft, ensure it's not active; if published, activate it immediately.
            $data['is_active'] = $readyToPublish ? true : false;

            $listing->update($data);
        } else {
            $data['user_id'] = Auth::id();
            $data['is_active'] = $readyToPublish ? true : false; // New listings become active immediately when published
            $listing = Listing::create($data);
        }

        $listing->categories()->sync([$this->category_id]);

        if ($this->featured_image) {
            $listing->clearMediaCollection('featured');
            $listing->addMedia($this->featured_image->getRealPath())
                ->usingName($this->featured_image->getClientOriginalName())
                ->toMediaCollection('featured');
        }

        if (! empty($this->gallery_images)) {
            foreach ($this->gallery_images as $image) {
                $listing->addMedia($image->getRealPath())
                    ->usingName($image->getClientOriginalName())
                    ->toMediaCollection('gallery');
            }
        }

        if ($readyToPublish) {
            session()->flash('success', 'Listing berhasil dipublikasikan.');
        } else {
            session()->flash('success', 'Listing berhasil disimpan sebagai Draft.');
        }

        return redirect()->route('user.listings');
    }

    public function render()
    {
        return view('livewire.user.listing-form')->layout('layouts.main');
    }
}
