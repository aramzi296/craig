<?php

namespace App\Livewire\Admin;

use App\Models\ListingReview;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $reviewId;
    public $comment;
    public $rating;
    public $isEditing = false;

    protected $updatesQueryString = ['search'];

    protected $rules = [
        'comment' => 'required|string|min:5',
        'rating' => 'required|integer|min:1|max:5',
    ];

    public function edit($id)
    {
        $review = ListingReview::findOrFail($id);
        $this->reviewId = $review->id;
        $this->comment = $review->comment;
        $this->rating = $review->rating;
        $this->isEditing = true;
    }

    public function update()
    {
        $this->validate();

        $review = ListingReview::findOrFail($this->reviewId);
        $review->update([
            'comment' => $this->comment,
            'rating' => $this->rating,
        ]);

        $this->isEditing = false;
        $this->reset(['reviewId', 'comment', 'rating']);
        session()->flash('message', 'Ulasan berhasil diperbarui.');
    }

    public function cancel()
    {
        $this->isEditing = false;
        $this->reset(['reviewId', 'comment', 'rating']);
    }

    public function delete($id)
    {
        ListingReview::findOrFail($id)->delete();
        session()->flash('message', 'Ulasan berhasil dihapus.');
    }

    public function render()
    {
        $reviews = ListingReview::with(['listing', 'user'])
            ->when($this->search, function ($query) {
                $query->where('comment', 'like', '%' . $this->search . '%')
                    ->orWhere('author_name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('listing', function ($q) {
                        $q->where('title', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.review-manager', [
            'reviews' => $reviews
        ])->layout('layouts.main');
    }
}
