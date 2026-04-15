<?php

namespace App\Livewire;

use App\Models\Listing;
use Livewire\Component;

class BlogDetail extends Component
{
    public $blog;

    public function mount($slug)
    {
        $this->blog = Listing::blog()
            ->with('categories', 'user')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Increment view count in meta.
        $meta = $this->blog->meta ?? [];
        $meta['view_count'] = ($meta['view_count'] ?? 0) + 1;
        $this->blog->update(['meta' => $meta]);
    }

    public function render()
    {
        $latestBlogs = Listing::blog()
            ->where('id', '!=', $this->blog->id)
            ->where('is_active', true)
            ->latest()
            ->limit(4)
            ->get();

        return view('livewire.blog-detail', [
            'blog' => $this->blog,
            'latestBlogs' => $latestBlogs
        ])->layout('layouts.main');
    }
}
