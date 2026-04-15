<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\District;
use App\Models\LapakSettingKV;
use App\Models\Listing;
use App\Models\Subdistrict;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class PublicUsahaListingForm extends Component
{
    use WithFileUploads;

    public bool $termsAccepted = false;
    public $registration_code;

    public $title;
    public $description;
    
    // Dynamic Category Rows
    // Format: [['parent' => 123, 'child' => 456]]
    public $categoryRows = [['parent' => '', 'child' => '']];
    public $root_categories = [];
    public $subCategoryOptions = []; // Index => [id => name]

    public $district_id;
    public $subdistrict_id;
    public $address;
    public $whatsapp;
    public $website;



    public $featured_image;

    public $districts;
    public $subdistricts = [];


    public function mount()
    {
        $this->districts = District::all();
        
        $this->root_categories = Category::forType('usaha')
            ->where('is_approved', true)
            ->whereNull('parent_id')
            ->pluck('name', 'id')
            ->toArray();


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
        // Key format: "0.parent"
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

    public function updatedRegistrationCode($value)
    {
        if (strlen($value) === 6) {
            $phone = \Illuminate\Support\Facades\Cache::get('registration_code:' . $value);
            if ($phone) {
                $this->whatsapp = $phone;
                $this->resetErrorBag('registration_code');
            } else {
                $this->whatsapp = null;
                $this->addError('registration_code', 'Kode listing tidak valid atau sudah kedaluwarsa.');
            }
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






    public function save()
    {
        $this->validate([
            'categoryRows' => 'required|array|min:1',
            'categoryRows.*.parent' => 'required',
            'categoryRows.*.child' => 'required',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'district_id' => 'required|exists:districts,id',
            'subdistrict_id' => 'nullable|exists:subdistricts,id',
            'address' => 'required|string',
            'whatsapp' => 'required|string',
            'registration_code' => 'required|numeric|digits:6',
            'termsAccepted' => 'required|accepted',
            'featured_image' => 'nullable|image|max:2048',
            'featured_image' => 'nullable|image|max:2048',
        ], [
            'categoryRows.*.parent.required' => 'Kategori utama wajib dipilih.',
            'categoryRows.*.child.required' => 'Sub kategori wajib dipilih.',
            'registration_code.required' => 'Kode listing wajib diisi.',
            'registration_code.numeric' => 'Kode listing harus berupa angka.',
            'registration_code.digits' => 'Kode listing harus 6 digit.',
        ]);

        $phone = \Illuminate\Support\Facades\Cache::get('registration_code:' . $this->registration_code);

        if (!$phone) {
            $this->addError('registration_code', 'Kode listing tidak valid atau sudah kedaluwarsa.');
            return;
        }

        $this->whatsapp = $phone;

        // Additional validation for 'other' sub-category
        foreach ($this->categoryRows as $index => $row) {
            if ($row['child'] === 'other') {
                $this->validate([
                    "categoryRows.$index.new_child" => 'required|string|max:50',
                ], [
                    "categoryRows.$index.new_child.required" => "Nama sub kategori baru wajib diisi.",
                ]);
            }
        }

        $code = (string) mt_rand(100000, 999999);

        $slug = Str::slug($this->title);
        $originalSlug = $slug;
        $count = 1;
        while (Listing::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $listing = Listing::create([
            'user_id' => null,
            'type' => 'usaha',
            'title' => $this->title,
            'slug' => $slug,
            'description' => $this->description,
            'district_id' => $this->district_id,
            'subdistrict_id' => $this->subdistrict_id,
            'address' => $this->address,
            'whatsapp' => $this->whatsapp,
            'website' => $this->website,
            'is_active' => false,
            'is_draft' => true,
            'code' => $code,
        ]);

        // Sync all selected children
        $selectedCategories = [];
        foreach ($this->categoryRows as $row) {
            $childId = $row['child'];

            if ($row['child'] === 'other') {
                // Create child under existing parent
                $child = Category::create([
                    'parent_id' => $row['parent'],
                    'name' => $row['new_child'],
                    'slug' => Str::slug($row['new_child']) . '-' . rand(100, 999),
                    'is_active' => true,
                    'is_approved' => false,
                    'listing_type' => 'usaha',
                ]);

                $childId = $child->id;
            }

            if ($childId && $childId !== 'other') {
                $selectedCategories[] = $childId;
            }
        }

        $listing->categories()->sync(array_unique($selectedCategories));

        if ($this->featured_image) {
            $listing->addMedia($this->featured_image->getRealPath())
                ->toMediaCollection('featured');
        }



        if ($this->registration_code) {
            \Illuminate\Support\Facades\Cache::forget('registration_code:' . $this->registration_code);
        }

        // Send WhatsApp verification for publication
        \Illuminate\Support\Facades\Cache::put('awaiting_publication:' . $this->whatsapp, $listing->id, now()->addHours(24));
        
        try {
            $msg = "✨ *Postingan Berhasil Dibuat!*\n\n" .
                   "Halo, postingan *" . $this->title . "* Anda telah tersimpan di sistem kami sebagai draft.\n\n" .
                   "Apakah Anda ingin *menerbitkannya sekarang* agar bisa dilihat publik?\n\n" .
                   "Balas *YA* untuk menerbitkan.\n" .
                   "Balas *TIDAK* untuk simpan sebagai draft.";
            
            app(\App\Services\WhatsappService::class)->sendMessage($this->whatsapp, $msg);
        } catch (\Exception $e) {}

        return redirect()->route('public.listing.thank-you');
    }

    public function render()
    {
        return view('livewire.public.public-usaha-listing-form')->layout('layouts.main');
    }
}
