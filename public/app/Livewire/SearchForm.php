<?php

namespace App\Livewire;

use Livewire\Component;

class SearchForm extends Component
{
    public $keyword = '';

    public function search()
    {
        $this->dispatch('searchSubmitted', $this->keyword);
    }

    public function render()
    {
        return view('livewire.search-form');
    }
}
