<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Listing;

class ListingModeration extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filter = 'pending'; // 'pending', 'all', 'active'
    public $search = '';
    public $type = '';
    public $district_id = '';
    public $category_id = '';
    public $selectedIds = [];

    protected $queryString = [
        'filter' => ['except' => 'pending'],
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'district_id' => ['except' => ''],
        'category_id' => ['except' => ''],
    ];

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'filter', 'type', 'district_id', 'category_id'])) {
            $this->resetPage();
        }
    }

    public function approve($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->update(['is_active' => true, 'is_draft' => false]);
        session()->flash('success', 'Listing "' . $listing->title . '" berhasil diaktifkan.');
    }

    public function hide($id)
    {
        $listing = Listing::findOrFail($id);
        $listing->update(['is_active' => false]);
        session()->flash('success', 'Listing "' . $listing->title . '" berhasil disembunyikan.');
    }

    public function approveSelected()
    {
        if (empty($this->selectedIds)) return;

        Listing::whereIn('id', $this->selectedIds)->update(['is_active' => true, 'is_draft' => false]);
        
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        session()->flash('success', $count . ' listing berhasil diaktifkan.');
    }

    public function hideSelected()
    {
        if (empty($this->selectedIds)) return;

        Listing::whereIn('id', $this->selectedIds)->update(['is_active' => false]);
        
        $count = count($this->selectedIds);
        $this->selectedIds = [];
        session()->flash('success', $count . ' listing berhasil disembunyikan.');
    }

    public function delete($id)
    {
        $listing = Listing::findOrFail($id);
        $title = $listing->title;
        $listing->delete(); // Spatie MediaLibrary handles file deletion on model delete
        session()->flash('success', 'Listing "' . $title . '" berhasil dihapus beserta gambarnya.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedIds)) return;

        $listings = Listing::whereIn('id', $this->selectedIds)->get();
        foreach ($listings as $listing) {
            $listing->delete();
        }

        $count = count($this->selectedIds);
        $this->selectedIds = [];
        session()->flash('success', $count . ' listing berhasil dihapus beserta gambarnya.');
    }

    public function render()
    {
        $query = Listing::with('user', 'categories', 'district')
            ->where('is_draft', false);
        
        if ($this->filter === 'pending') {
            $query->where('is_active', false);
        } elseif ($this->filter === 'active') {
            $query->where('is_active', true);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->district_id) {
            $query->where('district_id', $this->district_id);
        }

        if ($this->category_id) {
            $query->whereHas('categories', function($q) {
                $q->where('categories.id', $this->category_id);
            });
        }

        $listings = $query->latest()->paginate(15);
        $districts = \App\Models\District::orderBy('name')->get();
        $categories = \App\Models\Category::whereNotNull('parent_id')->orderBy('name')->get();
        $listingTypes = collect([
            (object)['name' => 'usaha', 'label' => 'Usaha'],
            (object)['name' => 'blog', 'label' => 'Blog'],
        ]);

        return view('livewire.admin.listing-moderation', [
            'listings' => $listings,
            'districts' => $districts,
            'categories' => $categories,
            'listingTypes' => $listingTypes,
        ])->layout('layouts.main');
    }
}
