<?php

namespace App\Livewire\Admin;

use App\Models\ListingType;
use Livewire\Component;
use Livewire\WithPagination;

class ListingTypeManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $typeId;
    public $name, $label, $icon, $sort_order = 0;
    public $is_active = true, $show_in_menu = true;
    public $showForm = false;

    protected $rules = [
        'name' => 'required|string|max:50|unique:listing_types,name',
        'label' => 'required|string|max:50',
        'icon' => 'nullable|string|max:50',
        'sort_order' => 'required|integer',
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
    ];

    public function render()
    {
        $types = ListingType::orderBy('sort_order')->orderBy('name')->paginate(10);
        return view('livewire.admin.listing-type-manager', [
            'types' => $types
        ])->layout('layouts.main');
    }

    public function openCreate()
    {
        $this->reset(['typeId', 'name', 'label', 'icon', 'sort_order', 'is_active', 'show_in_menu']);
        $this->showForm = true;
    }

    public function edit($id)
    {
        $type = ListingType::findOrFail($id);
        $this->typeId = $type->id;
        $this->name = $type->name;
        $this->label = $type->label;
        $this->icon = $type->icon;
        $this->sort_order = $type->sort_order;
        $this->is_active = $type->is_active;
        $this->show_in_menu = $type->show_in_menu;
        $this->showForm = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->typeId) {
            $rules['name'] = 'required|string|max:50|unique:listing_types,name,' . $this->typeId;
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'show_in_menu' => $this->show_in_menu,
        ];

        if ($this->typeId) {
            ListingType::findOrFail($this->typeId)->update($data);
            $msg = 'Tipe listing berhasil diperbarui.';
        } else {
            ListingType::create($data);
            $msg = 'Tipe listing baru berhasil ditambahkan.';
        }

        $this->showForm = false;
        session()->flash('success', $msg);
        $this->dispatch('swal', ['title' => 'Sukses!', 'text' => $msg, 'icon' => 'success']);
    }

    public function delete($id)
    {
        ListingType::findOrFail($id)->delete();
        session()->flash('success', 'Tipe listing berhasil dihapus.');
        $this->dispatch('swal', ['title' => 'Terhapus!', 'text' => 'Tipe listing telah dihapus.', 'icon' => 'success']);
    }
}
