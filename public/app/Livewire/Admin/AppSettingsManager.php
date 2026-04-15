<?php

namespace App\Livewire\Admin;

use App\Models\LapakSettingKV;
use Livewire\Component;

class AppSettingsManager extends Component
{
    // List of settings
    public $settings;

    // Form fields for modal
    public $setting_id;
    public $setting_name;
    public $setting_key;
    public $setting_value;
    public $description;

    public bool $isModalOpen = false;

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->settings = LapakSettingKV::orderBy('setting_name')->get();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['setting_id', 'setting_name', 'setting_key', 'setting_value', 'description']);

        if ($id) {
            $setting = LapakSettingKV::findOrFail($id);
            $this->setting_id = $setting->id;
            $this->setting_name = $setting->setting_name;
            $this->setting_key = $setting->setting_key;
            $this->setting_value = $setting->setting_value;
            $this->description = $setting->description;
        }

        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function save()
    {
        $rules = [
            'setting_name' => 'required|string|max:255',
            'setting_key' => 'required|string|max:255|unique:app_settings,setting_key,' . $this->setting_id,
            'setting_value' => 'required|string',
            'description' => 'nullable|string',
        ];

        $this->validate($rules);

        LapakSettingKV::updateOrCreate(
            ['id' => $this->setting_id],
            [
                'setting_name' => $this->setting_name,
                'setting_key' => $this->setting_key,
                'setting_value' => $this->setting_value,
                'description' => $this->description,
            ]
        );

        $this->closeModal();
        $this->loadSettings();

        session()->flash('success', 'Setting Aplikasi berhasil disimpan.');
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Setting Aplikasi berhasil disimpan.', 'icon' => 'success']);
    }

    public function delete($id)
    {
        $setting = LapakSettingKV::findOrFail($id);
        $setting->delete();
        $this->loadSettings();
        $this->dispatch('swal', ['title' => 'Terhapus!', 'text' => 'Setting berhasil dihapus.', 'icon' => 'success']);
    }

    public function render()
    {
        return view('livewire.admin.app-settings-manager')->layout('layouts.main');
    }
}
