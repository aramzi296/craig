<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\District;
use App\Models\Listing;
use Livewire\Component;
use Livewire\WithPagination;

class SearchResult extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public string $keyword = '';

    // Optional: when set, limit listings to this type ('usaha' or 'lapak')
    public ?string $type = null;

    public string $categoryId = '';
    public string $parentCategoryId = '';
    public string $subCategoryId = '';

    public string $districtId = '';
    public string $sortBy = 'rel';
    public bool $isHome = false;

    protected $queryString = [
        'keyword' => ['except' => ''],
        'categoryId' => ['except' => ''],
        'districtId' => ['except' => ''],
        'sortBy' => ['except' => 'rel'],
    ];

    public function mount(?string $type = null): void
    {
        $this->type = $type;
        $this->syncCategoryDropdowns();
    }

    protected function syncCategoryDropdowns()
    {
        if ($this->categoryId) {
            $cat = Category::find($this->categoryId);
            if ($cat) {
                if ($cat->parent_id) {
                    $this->parentCategoryId = (string) $cat->parent_id;
                    $this->subCategoryId = (string) $cat->id;
                } else {
                    $this->parentCategoryId = (string) $cat->id;
                    $this->subCategoryId = '';
                }
            }
        } else {
            $this->parentCategoryId = '';
            $this->subCategoryId = '';
        }
    }

    public function updatedKeyword(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
    {
        $this->syncCategoryDropdowns();
        $this->resetPage();
    }

    public function updatedParentCategoryId($value): void
    {
        $this->categoryId = $value;
        $this->subCategoryId = '';
        $this->resetPage();
    }

    public function updatedSubCategoryId($value): void
    {
        if ($value === '') {
            $this->categoryId = $this->parentCategoryId;
        } else {
            $this->categoryId = $value;
        }
        $this->resetPage();
    }

    public function updatedDistrictId(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function setCategory(string|int|null $id): void
    {
        $this->categoryId = $id === null || $id === '' ? '' : (string) $id;
        $this->syncCategoryDropdowns();
        $this->resetPage();
    }

    public function search(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->keyword = '';
        $this->categoryId = '';
        $this->parentCategoryId = '';
        $this->subCategoryId = '';
        $this->districtId = '';
        $this->sortBy = 'rel';
        $this->resetPage();
    }

    public function getActiveFilterCountProperty(): int
    {
        $n = 0;
        if (trim($this->keyword) !== '') {
            $n++;
        }
        if ($this->categoryId !== '') {
            $n++;
        }
        if ($this->districtId !== '') {
            $n++;
        }

        return $n;
    }

    public function render()
    {
        $kw = trim($this->keyword);
        $allCatIds = [];
        if ($this->categoryId !== '') {
            $allCatIds = $this->getAllCategoryIds($this->categoryId);
        }

        if ($kw !== '') {
            $query = Listing::search($kw, function ($meiliSearch, $query, $options) {
                if (is_array($options)) {
                    $options['matchingStrategy'] = 'all';
                    return $meiliSearch->search($query, $options);
                }
                return $meiliSearch;
            });

            if ($this->type) {
                $query->where('type', $this->type);
            }

            if ($this->districtId !== '') {
                $query->where('district_id', (int) $this->districtId);
            }

            if ($allCatIds) {
                $query->whereIn('category_ids', $allCatIds);
            }

            $query->where('is_active', true);
            $query->where('is_draft', false);

            if ($this->sortBy === 'rel') {
                $query->orderByDesc('is_featured')->orderByDesc('created_at');
            }

            $query->query(function ($q) {
                $q->with(['categories', 'district', 'media', 'listingType']);
            });
        } else {
            $query = Listing::query()
                ->with(['categories', 'district', 'media', 'listingType'])
                ->where('is_active', true)
                ->where('is_draft', false);

            if ($this->isHome) {
                $query->latest();
            } else {
                if ($this->type) {
                    $query->where('type', $this->type);
                }

                if ($allCatIds) {
                    $query->whereHas('categories', function ($q) use ($allCatIds) {
                        $q->whereIn('categories.id', $allCatIds);
                    });
                }

                if ($this->districtId !== '') {
                    $query->where('district_id', $this->districtId);
                }

                if ($this->sortBy === 'rel') {
                    $query->orderByDesc('is_featured')->latest();
                }
            }
        }

        $listings = $query->paginate(12);

        $rootCategories = Category::query()
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->forType($this->type)
            ->orderBy('order_index')
            ->orderBy('name')
            ->get();

        $districts = District::query()->orderBy('name')->get();

        return view('livewire.search-result', [
            'listings' => $listings,
            'rootCategories' => $rootCategories,
            'districts' => $districts,
        ]);
    }

    private function getAllCategoryIds(string|int $catId): array
    {
        $childIds = Category::where('parent_id', $catId)->pluck('id');
        $grandChildIds = Category::whereIn('parent_id', $childIds)->pluck('id');
        
        return collect([$catId])
            ->merge($childIds)
            ->merge($grandChildIds)
            ->unique()
            ->values()
            ->map(fn($id) => (int) $id)
            ->toArray();
    }
}
