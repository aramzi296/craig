<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\Category;
use App\Models\Listing;
use Livewire\WithPagination;

class ListingFilter extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $type; // 'usaha' or 'lapak'

    // Filters
    public $cat1 = ''; // Level 1
    public $cat2 = ''; // Level 2
    public $cat3 = ''; // Level 3
    
    public $district_id = '';
    public $subdistrict_id = '';
    
    // Lapak additional filters
    public $price_min = '';
    public $price_max = '';
    public $lapak_type = '';
    public $condition = '';

    // Data for dropdowns
    public $districts;
    public $subdistricts = [];
    public $categories_lvl1;
    public $categories_lvl2 = [];
    public $categories_lvl3 = [];

    public function mount($type = 'usaha')
    {
        $this->type = $type;
        $this->districts = District::all();
        $this->categories_lvl1 = Category::forType($this->type)->whereNull('parent_id')->where('is_approved', true)->orderBy('name')->get();
    }

    public function updatedCat1($value)
    {
        $this->cat2 = '';
        $this->cat3 = '';
        if ($value) {
            $this->categories_lvl2 = Category::where('parent_id', $value)
                ->where('is_approved', true)
                ->orderBy('name')
                ->get();
        } else {
            $this->categories_lvl2 = [];
        }
        $this->categories_lvl3 = [];
        $this->resetPage();
    }

    public function updatedCat2($value)
    {
        $this->cat3 = '';
        if ($value) {
            $this->categories_lvl3 = Category::where('parent_id', $value)
                ->where('is_approved', true)
                ->orderBy('name')
                ->get();
        } else {
            $this->categories_lvl3 = [];
        }
        $this->resetPage();
    }

    public function updatedDistrictId($value)
    {
        if ($value) {
            $this->subdistricts = Subdistrict::where('district_id', $value)->get();
        } else {
            $this->subdistricts = [];
        }
        $this->subdistrict_id = '';
        $this->resetPage();
    }

    public function updated()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Listing::with(['media', 'categories', 'district', 'subdistrict'])
            ->where('is_active', true)
            ->where('type', $this->type);

        // Filter Kategori (Hirarkis)
        $selectedCatId = $this->cat3 ?: ($this->cat2 ?: $this->cat1);
        if ($selectedCatId) {
            // Dapatkan ID kategori ini dan semua turunannya (rekursif)
            $categoryIds = Category::where('id', $selectedCatId)
                ->orWhere('parent_id', $selectedCatId)
                ->pluck('id')
                ->toArray();
            
            // Tambahkan level 3 jika yang dipilih adalah level 1 (agar mencakup cucu)
            if ($this->cat1 && !$this->cat2) {
                $lvl2Ids = Category::where('parent_id', $this->cat1)->pluck('id');
                $lvl3Ids = Category::whereIn('parent_id', $lvl2Ids)->pluck('id')->toArray();
                $categoryIds = array_merge($categoryIds, $lvl3Ids);
            }

            $query->whereHas('categories', function($q) use ($categoryIds) {
                $q->whereIn('categories.id', array_unique($categoryIds));
            });
        }

        if ($this->district_id) {
            $query->where('district_id', $this->district_id);
        }
        if ($this->subdistrict_id) {
            $query->where('subdistrict_id', $this->subdistrict_id);
        }

        if ($this->type == 'lapak') {
            // Hide expired lapak from public pages
            if (\Illuminate\Support\Facades\Schema::hasColumn('listings', 'lapak_expires_at')) {
                $query->where(function ($q) {
                    $q->whereNull('lapak_expires_at')
                        ->orWhereDate('lapak_expires_at', '>=', now()->toDateString());
                });
            }

            if ($this->price_min) {
                $query->where('price', '>=', $this->price_min);
            }
            if ($this->price_max) {
                $query->where('price', '<=', $this->price_max);
            }
            if ($this->lapak_type) {
                $query->where('lapak_type', $this->lapak_type);
            }
            if ($this->condition) {
                $query->where('item_condition', $this->condition);
            }
        }

        $listings = $query->latest()->paginate(12);

        return view('livewire.listing-filter', [
            'listings' => $listings
        ]);
    }
}
