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

class PublicIklanListingForm extends Component
{
    use WithFileUploads;

    public bool $termsAccepted = false;
    public $registration_code;

    public $title;
    public $description;
    public $parent_category_id;
    public $root_categories = [];

    public $district_id;
    public $subdistrict_id;
    public $whatsapp;
    public bool $show_whatsapp = true;

    public $featured_image;
    public $gallery_images = [];

    public $districts;
    public $subdistricts = [];
    public int $galleryLimit = 4;

    public function mount()
    {
        $this->districts = District::all();
        $this->root_categories = Category::forType('iklan')
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->pluck('name', 'id')
            ->toArray();

        $this->galleryLimit = (int) LapakSettingKV::getInt('free_gallery_max', 4);
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

    public function save()
    {

        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'parent_category_id' => 'required|exists:categories,id',
            'district_id' => 'required|exists:districts,id',
            'subdistrict_id' => 'nullable|exists:subdistricts,id',
            'whatsapp' => 'required|string',
            'show_whatsapp' => 'boolean',
            'registration_code' => 'required|numeric|digits:6',
            'termsAccepted' => 'required|accepted',
            'featured_image' => 'nullable|image|max:2048',
            'gallery_images.*' => 'image|max:2048',
        ], [
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
            'type' => 'iklan',
            'title' => $this->title,
            'slug' => $slug,
            'description' => $this->description,
            'district_id' => $this->district_id,
            'subdistrict_id' => $this->subdistrict_id,
            'whatsapp' => $this->whatsapp,
            'is_active' => false,
            'is_draft' => true,
            'code' => $code,
            'meta' => [
                'show_whatsapp' => $this->show_whatsapp,
            ]
        ]);

        $listing->categories()->sync([$this->parent_category_id]);

        if ($this->featured_image) {
            $listing->addMedia($this->featured_image->getRealPath())
                ->toMediaCollection('featured');
        }

        foreach ($this->gallery_images as $image) {
            $listing->addMedia($image->getRealPath())
                ->toMediaCollection('gallery');
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
        return view('livewire.public.public-iklan-listing-form')->layout('layouts.main');
    }
}
