<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Category;
use App\Models\ListingType;

class CategoryManager extends Component
{
    public $name;
    public $parent_id;
    public $listing_type = '';
    public $editingId;
    public $showForm = false;
    public $filterType = '';
    public $search = '';
    public $order_index = 0;

    // Subcategory Review
    public $reviewingSubcategoryId = null;
    public $reviewName;
    public $reviewParentId;

    public function openCreate()
    {
        $this->reset(['name', 'parent_id', 'listing_type', 'editingId', 'order_index']);
        $this->showForm = true;
    }

    public function edit($id)
    {
        $cat = Category::findOrFail($id);
        $this->editingId = $cat->id;
        $this->name = $cat->name;
        $this->parent_id = $cat->parent_id;
        $this->listing_type = $cat->listing_type ?? '';
        $this->order_index = $cat->order_index ?? 0;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'listing_type' => 'nullable|string|exists:listing_types,name',
            'order_index' => 'nullable|integer',
        ]);

        $level = 1;
        if ($this->parent_id) {
            $parent = Category::find($this->parent_id);
            $level = $parent ? ($parent->level + 1) : 1;
            // Inherit listing_type from parent if empty
            if (empty($this->listing_type) && $parent && $parent->listing_type) {
                $this->listing_type = $parent->listing_type;
            }
        }

        $slug = \Illuminate\Support\Str::slug($this->name);
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $this->editingId)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update([
                'name'      => $this->name,
                'slug'      => $slug,
                'parent_id' => $this->parent_id ?: null,
                'level'     => $level,
                'listing_type' => $this->listing_type ?: null,
                'order_index' => $this->order_index ?: 0,
            ]);
        } else {
            Category::create([
                'name'      => $this->name,
                'slug'      => $slug,
                'parent_id' => $this->parent_id ?: null,
                'level'     => $level,
                'listing_type' => $this->listing_type ?: null,
                'order_index' => $this->order_index ?: 0,
            ]);
        }

        $this->reset(['name', 'parent_id', 'listing_type', 'editingId', 'showForm', 'order_index']);
        session()->flash('success', 'Kategori berhasil disimpan.');
    }

    public function delete($id)
    {
        Category::findOrFail($id)->delete();
        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function reviewSubcategory($id)
    {
        $sub = Category::findOrFail($id);
        $this->reviewingSubcategoryId = $sub->id;
        $this->reviewName = $sub->name;
        $this->reviewParentId = $sub->parent_id;
        $this->listing_type = $sub->listing_type ?? '';
        $this->order_index = $sub->order_index ?? 0;
    }

    public function cancelReview()
    {
        $this->reset(['reviewingSubcategoryId', 'reviewName', 'reviewParentId', 'order_index']);
    }

    public function approveSubcategory()
    {
        $this->validate([
            'reviewName' => 'required|string|max:255',
            'reviewParentId' => 'required|exists:categories,id',
            'listing_type' => 'nullable|string|exists:listing_types,name',
            'order_index' => 'nullable|integer',
        ]);

        $sub = Category::findOrFail($this->reviewingSubcategoryId);

        // Calculate level
        $parent = Category::find($this->reviewParentId);
        $level = $parent ? ($parent->level + 1) : 1;

        // Update instead of create
        $sub = Category::findOrFail($this->reviewingSubcategoryId);

        // Ensure unique slug
        $slug = \Illuminate\Support\Str::slug($this->reviewName);
        $originalSlug = $slug;
        $count = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $sub->id)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $sub->update([
            'name' => $this->reviewName,
            'slug' => $slug,
            'parent_id' => $this->reviewParentId,
            'level' => $level,
            'is_approved' => true,
            'listing_type' => $this->listing_type ?: $parent->listing_type,
            'order_index' => $this->order_index ?: 0,
        ]);

        $this->cancelReview();
        session()->flash('success', 'Sub-kategori berhasil disetujui dan ditambahkan ke kategori utama.');
    }

    public function rejectSubcategory($id)
    {
        $sub = Category::findOrFail($id);
        
        // Move listings from this subcategory to its parent in the pivot table
        if ($sub->parent_id) {
            $pivots = \Illuminate\Support\Facades\DB::table('category_listing')
                ->where('category_id', $sub->id)
                ->get();

            foreach ($pivots as $pivot) {
                $exists = \Illuminate\Support\Facades\DB::table('category_listing')
                    ->where('listing_id', $pivot->listing_id)
                    ->where('category_id', $sub->parent_id)
                    ->exists();

                if (!$exists) {
                    \Illuminate\Support\Facades\DB::table('category_listing')
                        ->where('id', $pivot->id)
                        ->update(['category_id' => $sub->parent_id]);
                } else {
                    // If already exists, just delete the rejected one
                    \Illuminate\Support\Facades\DB::table('category_listing')
                        ->where('id', $pivot->id)
                        ->delete();
                    
                }
            }
        } else {
            // No parent, just detach
            \Illuminate\Support\Facades\DB::table('category_listing')
                ->where('category_id', $sub->id)
                ->delete();
        }

        $sub->delete();
        session()->flash('success', 'Sub-kategori ditolak dan dihapus.');
    }

    public function render()
    {
        $query = Category::with('parent')->where('is_approved', true);
        if ($this->filterType !== '') {
            $query->where('listing_type', $this->filterType);
        }
        if ($this->search !== '') {
            $query->where('name', 'ILIKE', '%' . $this->search . '%');
        }

        return view('livewire.admin.category-manager', [
            'categories' => $query->orderBy('level')->orderBy('order_index')->orderBy('name')->get(),
            'roots'      => Category::whereNull('parent_id')->where('is_approved', true)->orderBy('order_index')->orderBy('name')->get(),
            'proposed'   => Category::with(['creator', 'parent'])->where('is_approved', false)->latest()->get(),
            'listingTypes'=> ListingType::all(),
        ])->layout('layouts.main');
    }
}
