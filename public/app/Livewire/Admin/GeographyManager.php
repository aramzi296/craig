<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\District;
use App\Models\Subdistrict;

class GeographyManager extends Component
{
    // Districts
    public $districtName;
    public $editingDistrictId;
    public $showDistrictForm = false;

    // Subdistricts
    public $subdistrictName;
    public $subdistrictDistrictId;
    public $editingSubdistrictId;
    public $showSubdistrictForm = false;

    // ----- Districts -----
    public function openCreateDistrict()
    {
        $this->reset(['districtName', 'editingDistrictId']);
        $this->showDistrictForm = true;
    }

    public function editDistrict($id)
    {
        $d = District::findOrFail($id);
        $this->editingDistrictId = $d->id;
        $this->districtName = $d->name;
        $this->showDistrictForm = true;
    }

    public function saveDistrict()
    {
        $this->validate(['districtName' => 'required|string|max:255']);
        $slug = \Illuminate\Support\Str::slug($this->districtName);
        $originalSlug = $slug;
        $count = 1;
        while (District::where('slug', $slug)->where('id', '!=', $this->editingDistrictId)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        if ($this->editingDistrictId) {
            District::findOrFail($this->editingDistrictId)->update(['name' => $this->districtName, 'slug' => $slug]);
        } else {
            District::create(['name' => $this->districtName, 'slug' => $slug]);
        }
        $this->reset(['districtName', 'editingDistrictId', 'showDistrictForm']);
        session()->flash('success', 'Kecamatan disimpan.');
    }

    public function deleteDistrict($id)
    {
        District::findOrFail($id)->delete();
        session()->flash('success', 'Kecamatan dihapus.');
    }

    // ----- Subdistricts -----
    public function openCreateSubdistrict()
    {
        $this->reset(['subdistrictName', 'subdistrictDistrictId', 'editingSubdistrictId']);
        $this->showSubdistrictForm = true;
    }

    public function editSubdistrict($id)
    {
        $s = Subdistrict::findOrFail($id);
        $this->editingSubdistrictId = $s->id;
        $this->subdistrictName = $s->name;
        $this->subdistrictDistrictId = $s->district_id;
        $this->showSubdistrictForm = true;
    }

    public function saveSubdistrict()
    {
        $this->validate([
            'subdistrictName'       => 'required|string|max:255',
            'subdistrictDistrictId' => 'required|exists:districts,id',
        ]);
        $slug = \Illuminate\Support\Str::slug($this->subdistrictName);
        $originalSlug = $slug;
        $count = 1;
        while (Subdistrict::where('slug', $slug)->where('id', '!=', $this->editingSubdistrictId)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        if ($this->editingSubdistrictId) {
            Subdistrict::findOrFail($this->editingSubdistrictId)->update([
                'name' => $this->subdistrictName, 'slug' => $slug, 'district_id' => $this->subdistrictDistrictId,
            ]);
        } else {
            Subdistrict::create([
                'name' => $this->subdistrictName, 'slug' => $slug, 'district_id' => $this->subdistrictDistrictId,
            ]);
        }
        $this->reset(['subdistrictName', 'subdistrictDistrictId', 'editingSubdistrictId', 'showSubdistrictForm']);
        session()->flash('success', 'Kelurahan disimpan.');
    }

    public function deleteSubdistrict($id)
    {
        Subdistrict::findOrFail($id)->delete();
        session()->flash('success', 'Kelurahan dihapus.');
    }

    public function render()
    {
        return view('livewire.admin.geography-manager', [
            'districts'    => District::withCount('subdistricts')->orderBy('name')->get(),
            'subdistricts' => Subdistrict::with('district')->orderBy('name')->get(),
            'districtList' => District::orderBy('name')->get(),
        ])->layout('layouts.main');
    }
}
