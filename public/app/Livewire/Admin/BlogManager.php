<?php

namespace App\Livewire\Admin;

use App\Models\Listing;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BlogManager extends Component
{
    use WithPagination, WithFileUploads;

    public $blogId;
    public $title, $description, $categoryId, $featuredImage;
    public $isPublished = true, $isPinned = false;
    public $showModal = false;
    public $selectedBlogs = [];
    public $selectAll = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'categoryId' => 'required',
        'featuredImage' => 'nullable|image|max:2048',
        'isPublished' => 'boolean',
        'isPinned' => 'boolean',
    ];

    public function render()
    {
        $blogs = Listing::blog()
            ->with('categories')
            ->latest()
            ->paginate(10);

        $categories = Category::where('listing_type', 'blog')->get();

        return view('livewire.admin.blog-manager', [
            'blogs' => $blogs,
            'categories' => $categories
        ])->layout('layouts.main');
    }

    public function openCreate()
    {
        $this->reset(['blogId', 'title', 'description', 'categoryId', 'featuredImage', 'isPublished', 'isPinned']);
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $blog = Listing::findOrFail($id);
        $this->blogId = $id;
        $this->title = $blog->title;
        $this->description = $blog->description;
        $this->categoryId = $blog->categories->first()->id ?? null;
        $this->isPublished = data_get($blog->meta, 'is_published', true);
        $this->isPinned = data_get($blog->meta, 'is_pinned', false);
        $this->featuredImage = null;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        $slug = Str::slug($this->title);
        $originalSlug = $slug;
        $count = 1;
        while (Listing::where('slug', $slug)->where('id', '!=', $this->blogId)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $data = [
            'type' => 'blog',
            'user_id' => Auth::id(),
            'title' => $this->title,
            'slug' => $slug,
            'description' => $this->description,
            'is_active' => $this->isPublished,
            'meta' => [
                'is_published' => $this->isPublished,
                'is_pinned' => $this->isPinned,
                'published_at' => $this->isPublished ? now() : null,
                'view_count' => $this->blogId ? data_get(Listing::find($this->blogId)->meta, 'view_count', 0) : 0,
            ]
        ];

        if ($this->blogId) {
            $blog = Listing::findOrFail($this->blogId);
            $blog->update($data);
            $blog->categories()->sync([$this->categoryId]);
            $msg = 'Berita berhasil diperbarui.';
        } else {
            $blog = Listing::create($data);
            $blog->categories()->attach($this->categoryId);
            $msg = 'Berita berhasil diterbitkan.';
        }

        if ($this->featuredImage) {
            $blog->clearMediaCollection('featured');
            $blog->addMedia($this->featuredImage->getRealPath())
                ->usingFileName($this->featuredImage->getClientOriginalName())
                ->toMediaCollection('featured');
            $this->featuredImage = null;
        }

        $this->reset(['blogId', 'title', 'description', 'categoryId', 'featuredImage', 'isPublished', 'isPinned']);
        $this->closeModal();
        session()->flash('success', $msg);
        $this->dispatch('swal', ['title' => 'Sukses!', 'text' => $msg, 'icon' => 'success']);
    }

    public function delete($id)
    {
        Listing::findOrFail($id)->delete();
        session()->flash('success', 'Berita berhasil dihapus.');
        $this->dispatch('swal', ['title' => 'Terhapus!', 'text' => 'Berita telah dihapus.', 'icon' => 'success']);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedBlogs = Listing::blog()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedBlogs = [];
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedBlogs)) return;

        Listing::whereIn('id', $this->selectedBlogs)->get()->each->delete();
        $this->selectedBlogs = [];
        $this->selectAll = false;
        
        session()->flash('success', 'Berita terpilih berhasil dihapus.');
        $this->dispatch('swal', ['title' => 'Terhapus!', 'text' => 'Berita terpilih telah dihapus.', 'icon' => 'success']);
    }

    public function toggleStatusSelected($status = false)
    {
        if (empty($this->selectedBlogs)) return;

        Listing::whereIn('id', $this->selectedBlogs)->get()->each(function($blog) use ($status) {
            $meta = $blog->meta;
            $meta['is_published'] = $status;
            if ($status && !isset($meta['published_at'])) {
                $meta['published_at'] = now();
            }
            $blog->update([
                'is_active' => $status,
                'meta' => $meta
            ]);
        });

        $this->selectedBlogs = [];
        $this->selectAll = false;

        $msg = $status ? 'Berita terpilih berhasil diterbitkan.' : 'Berita terpilih berhasil disembunyikan.';
        session()->flash('success', $msg);
        $this->dispatch('swal', ['title' => 'Sukses!', 'text' => $msg, 'icon' => 'success']);
    }
}
