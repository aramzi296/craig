<?php

namespace App\Livewire;

use App\Models\Listing;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class BlogIndex extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $category = '';

    protected $queryString = ['search', 'category'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Listing::blog()
            ->with(['categories', 'user'])
            ->where('is_active', true);

        if (trim($this->search) !== '') {
            $query = Listing::search($this->search, function ($meiliSearch, $query, $options) {
                if (is_array($options)) {
                    $options['matchingStrategy'] = 'all';
                    return $meiliSearch->search($query, $options);
                }
                return $meiliSearch;
            })->query(fn($q) => $q->with(['categories', 'user'])->where('is_active', true)->where('type', 'blog'));
        }

        $blogs = $query
            ->when($this->category, function ($query) {
                $query->whereHas('categories', function ($q) {
                    $q->where('slug', $this->category);
                });
            })
            ->latest()
            ->paginate(9);

        $categories = Category::where('listing_type', 'blog')->get();

        return view('livewire.blog-index', [
            'blogs' => $blogs,
            'categories' => $categories
        ])->layout('layouts.main');
    }
}
