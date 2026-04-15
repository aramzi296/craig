<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WaMessage;

class WaMessageManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $selectedMessages = [];
    public $selectAll = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedMessages = WaMessage::query()
                ->when($this->search, function ($query) {
                    $query->where('from_number', 'like', '%' . $this->search . '%')
                        ->orWhere('to_number', 'like', '%' . $this->search . '%')
                        ->orWhere('message', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->limit(100) // Limit to avoid selecting too many at once
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedMessages = [];
        }
    }

    public function deleteMessage($id)
    {
        WaMessage::findOrFail($id)->delete();
        session()->flash('success', 'Pesan berhasil dihapus.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedMessages)) {
            session()->flash('warning', 'Pilih minimal satu pesan untuk dihapus.');
            return;
        }

        WaMessage::whereIn('id', $this->selectedMessages)->delete();
        $this->selectedMessages = [];
        $this->selectAll = false;
        session()->flash('success', count($this->selectedMessages) . ' Pesan yang dipilih berhasil dihapus.');
    }

    public function render()
    {
        $messages = WaMessage::query()
            ->when($this->search, function ($query) {
                $query->where('from_number', 'like', '%' . $this->search . '%')
                    ->orWhere('to_number', 'like', '%' . $this->search . '%')
                    ->orWhere('message', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.wa-message-manager', [
            'messages' => $messages,
        ])->layout('layouts.main');
    }
}
