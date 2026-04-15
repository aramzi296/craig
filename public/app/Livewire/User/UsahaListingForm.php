<?php

namespace App\Livewire\User;

use App\Models\Category;
use App\Models\District;
use App\Models\Listing;
use App\Models\Subdistrict;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class UsahaListingForm extends Component
{
    use WithFileUploads;

    public $listingId;
    public bool $termsAccepted = false;

    // Fields common
    public $title;
    public $description;

    // Category Selection
    public $categoryRows = [['parent' => '', 'child' => '']];
    public $root_categories = [];
    public $subCategoryOptions = []; // Index => [id => name]

    public $district_id;
    public $subdistrict_id;
    public $address;
    public $whatsapp;
    public $website;


    // Media fields
    public $featured_image;

    /** Preview URLs for existing media (edit mode). */
    public ?string $existingFeaturedPreviewUrl = null;



    // Sources for Dropdowns
    public $districts;
    public $subdistricts = [];
    public $categories_lvl1;
    public $categories_lvl2 = [];
    public $categories_lvl3 = [];

    public function mount($id = null)
    {
        $this->districts = District::all();
        
        $this->root_categories = Category::forType('usaha')
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->orderBy('order_index')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if ($id) {
            $listing = Listing::findOrFail($id);
            if ($listing->user_id !== Auth::id()) {
                abort(403);
            }
            abort_unless($listing->type === 'usaha', 404);

            $this->listingId = $listing->id;
            $this->title = $listing->title;
            $this->description = $listing->description;
            $this->district_id = $listing->district_id;
            $this->subdistrict_id = $listing->subdistrict_id;
            $this->address = $listing->address;
            $this->whatsapp = $listing->whatsapp;
            $this->website = $listing->website;

            // Load categories into rows
            $categories = $listing->categories()->get();
            if ($categories->isNotEmpty()) {
                $this->categoryRows = [];
                foreach ($categories as $index => $cat) {
                    $parent_id = $cat->parent_id;
                    $child_id = $cat->id;

                    // If it's a root category, it shouldn't be here in this specific UI pattern 
                    // (which expects parent-child), but we handle it.
                    if (!$parent_id) {
                        $parent_id = $child_id;
                        $child_id = '';
                    }

                    $this->categoryRows[] = [
                        'parent' => $parent_id,
                        'child' => $child_id
                    ];

                    // Load options for this parent
                    $this->subCategoryOptions[$index] = Category::where('parent_id', $parent_id)
                        ->where('is_approved', true)
                        ->pluck('name', 'id')
                        ->toArray();
                    
                    // Specific case for proposed/unapproved categories
                    if (!$cat->is_approved) {
                         $this->categoryRows[$index]['child'] = 'other';
                         $this->categoryRows[$index]['new_child'] = $cat->name;
                    }
                }
            }

            if ($this->district_id) {
                $this->subdistricts = Subdistrict::where('district_id', $this->district_id)->get();
            }

            $listing->loadMissing('media');
            if ($listing->hasMedia('featured')) {
                $this->existingFeaturedPreviewUrl = $listing->getFirstMediaUrl('featured', 'thumbnail')
                    ?: $listing->getFirstMediaUrl('featured');
            }


        }
    }




    public function addCategoryRow()
    {
        $this->categoryRows[] = ['parent' => '', 'child' => ''];
    }

    public function removeCategoryRow($index)
    {
        unset($this->categoryRows[$index]);
        unset($this->subCategoryOptions[$index]);
        $this->categoryRows = array_values($this->categoryRows);
        $this->subCategoryOptions = array_values($this->subCategoryOptions);
    }

    public function updatedCategoryRows($value, $key)
    {
        if (str_contains($key, '.parent')) {
            $index = explode('.', $key)[0];
            if ($value) {
                $this->subCategoryOptions[$index] = Category::where('parent_id', $value)
                    ->where('is_approved', true)
                    ->pluck('name', 'id')
                    ->toArray();
            } else {
                $this->subCategoryOptions[$index] = [];
            }
            $this->categoryRows[$index]['child'] = '';
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
        try {


            // Category ID logic removed in favor of sync()

            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'categoryRows' => 'required|array|min:1',
                'categoryRows.*.parent' => 'required',
                'categoryRows.*.child' => 'required',
                'district_id' => 'required|exists:districts,id',
                'subdistrict_id' => 'nullable|exists:subdistricts,id',
                'address' => 'required|string',
                'whatsapp' => 'required|string',
                'featured_image' => 'nullable|image|max:2048',
            ];

            // Persetujuan syarat & ketentuan hanya wajib saat user mempublikasikan listing.
            if ($readyToPublish) {
                $rules['termsAccepted'] = 'required|accepted';
            }

            $messages = [
                'categoryRows.*.parent.required' => 'Kategori utama wajib dipilih.',
                'categoryRows.*.child.required' => 'Sub kategori wajib dipilih.',
            ];

            $this->validate($rules, $messages);

            foreach ($this->categoryRows as $index => $row) {
                if (isset($row['child']) && $row['child'] === 'other') {
                    $this->validate([
                        "categoryRows.$index.new_child" => 'required|string|max:50',
                    ], [
                        "categoryRows.$index.new_child.required" => "Nama sub kategori baru wajib diisi.",
                    ]);
                }
            }

            // Collect selected categories and process 'other'
            $selectedCategories = [];
            foreach ($this->categoryRows as $row) {
                $childId = $row['child'];

                if ($row['child'] === 'other') {
                    // Similar to public form but with created_by
                    $child = Category::create([
                        'parent_id' => $row['parent'],
                        'name' => $row['new_child'],
                        'slug' => Str::slug($row['new_child']) . '-' . rand(100, 999),
                        'is_active' => true,
                        'is_approved' => false,
                        'listing_type' => 'usaha',
                        'created_by' => Auth::id(),
                    ]);

                    $childId = $child->id;

                    // Notify admin
                    try {
                        app(\App\Services\WhatsappService::class)->sendMessage(
                            config('services.whatsapp.admin_number'),
                            "🔔 *Pemberitahuan Sistem Sebatam*\n\nUser *" . Auth::user()->name . "* telah menambahkan sub-kategori baru:\n\n*Nama Sub:* " . $row['new_child'] . "\n\nSilakan cek di halaman admin."
                        );
                    } catch (\Exception $e) {}
                }

                if ($childId && $childId !== 'other') {
                    $selectedCategories[] = $childId;
                }
            }

            $data = [
                'type' => 'usaha',
                'title' => $this->title,
                'description' => $this->description,
                'district_id' => $this->district_id,
                'subdistrict_id' => $this->subdistrict_id,
                'address' => $this->address,
                'whatsapp' => $this->whatsapp,
                'website' => $this->website,
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

            if ($this->listingId) {
                $listing = Listing::findOrFail($this->listingId);

                // If saved as draft, ensure it's not active; if published, activate it immediately.
                $data['is_active'] = $readyToPublish ? true : false;

                $listing->update($data);
            } else {
                $data['user_id'] = Auth::id();
                $data['is_active'] = $readyToPublish ? true : false;
                $listing = Listing::create($data);
            }

            // Sync categories 
            $listing->categories()->sync(array_unique($selectedCategories));

            if ($this->featured_image) {
                $listing->clearMediaCollection('featured');
                $listing->addMedia($this->featured_image->getRealPath())
                    ->usingName($this->featured_image->getClientOriginalName())
                    ->toMediaCollection('featured');
            }



            if ($readyToPublish) {
                session()->flash('success', 'Listing berhasil dipublikasikan.');
            } else {
                session()->flash('success', 'Listing berhasil disimpan sebagai Draft.');
            }

            return redirect()->route('user.usaha.listings');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Input Tidak Valid',
                'text' => 'Terdapat beberapa kesalahan. Silakan periksa kembali kolom yang bertanda merah.'
            ]);
            throw $e;
        }
    }



    public function render()
    {
        return view('livewire.user.listing-form-usaha')->layout('layouts.main');
    }
}
