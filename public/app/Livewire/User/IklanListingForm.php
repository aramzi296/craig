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

class IklanListingForm extends Component
{
    use WithFileUploads;

    public $listingId;
    public bool $termsAccepted = false;

    public $title;
    public $description;
    public $parent_category_id;
    public $root_categories = [];

    public $district_id;
    public $subdistrict_id;
    public $whatsapp;
    public bool $show_whatsapp = true;

    public $featured_image;

    public ?string $existingFeaturedPreviewUrl = null;


    public $districts;
    public $subdistricts = [];


    public function mount($id = null)
    {
        $this->districts = District::all();
        $this->root_categories = Category::forType('iklan')
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->orderBy('order_index')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if ($id) {
            $listing = Listing::findOrFail($id);
            if ($listing->user_id !== Auth::id()) {
                abort(403);
            }
            abort_unless($listing->type === 'iklan', 404);

            $this->listingId = $listing->id;
            $this->title = $listing->title;
            $this->description = $listing->description;
            $this->district_id = $listing->district_id;
            $this->subdistrict_id = $listing->subdistrict_id;
            $this->whatsapp = $listing->whatsapp;
            $this->show_whatsapp = $listing->meta['show_whatsapp'] ?? true;
            
            // Set category
            $listingCat = $listing->categories()->first();
            if ($listingCat) {
                $this->parent_category_id = $listingCat->parent_id ?: $listingCat->id;
            }

            if ($this->district_id) {
                $this->subdistricts = Subdistrict::where('district_id', $this->district_id)->get();
            }

            $listing->loadMissing('media');
            if ($listing->hasMedia('featured')) {
                $this->existingFeaturedPreviewUrl = $listing->getFirstMediaUrl('featured', 'thumbnail')
                    ?: $listing->getFirstMediaUrl('featured');
            }

        } else {
             // Try to use current user's whatsapp if exists
             $this->whatsapp = Auth::user()->whatsapp ?? '';
        }
    }



    public function updatedParentCategoryId($value)
    {
        // No subcategories needed
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
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'parent_category_id' => 'required|exists:categories,id',
            'district_id' => 'required|exists:districts,id',
            'subdistrict_id' => 'nullable|exists:subdistricts,id',
            'whatsapp' => 'required|string',
            'show_whatsapp' => 'boolean',
            'featured_image' => 'nullable|image|max:2048',
        ];

        if ($readyToPublish) {
            $rules['termsAccepted'] = 'required|accepted';
        }

        $this->validate($rules);

        $finalCategoryId = $this->parent_category_id;

        $data = [
            'type' => 'iklan',
            'title' => $this->title,
            'description' => $this->description,
            'district_id' => $this->district_id,
            'subdistrict_id' => $this->subdistrict_id,
            'whatsapp' => $this->whatsapp,
            'is_draft' => ! $readyToPublish,
            'is_active' => $readyToPublish,
        ];

        if ($this->listingId) {
            $existingListing = Listing::find($this->listingId);
            $data['meta'] = array_merge($existingListing->meta ?? [], ['show_whatsapp' => $this->show_whatsapp]);
        } else {
            $data['meta'] = ['show_whatsapp' => $this->show_whatsapp];
        }

        if (! $this->listingId) {
            $slug = Str::slug($this->title);
            $originalSlug = $slug;
            $count = 1;
            while (Listing::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            $data['slug'] = $slug;
            $data['user_id'] = Auth::id();
            $listing = Listing::create($data);
        } else {
            $listing = Listing::findOrFail($this->listingId);
            $listing->update($data);
        }

        $listing->categories()->sync([$finalCategoryId]);

        if ($this->featured_image) {
            $listing->clearMediaCollection('featured');
            $listing->addMedia($this->featured_image->getRealPath())
                ->usingName($this->featured_image->getClientOriginalName())
                ->toMediaCollection('featured');
        }



        session()->flash('success', $readyToPublish ? 'Iklan berhasil dipublikasikan.' : 'Iklan berhasil disimpan sebagai draft.');
        return redirect()->route('user.iklan.listings');
    }



    public function render()
    {
        return view('livewire.user.listing-form-iklan')->layout('layouts.main');
    }
}
