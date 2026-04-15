<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Services\WhatsappService;

class WaContactForm extends Component
{
    public $phone;
    public $message;

    protected $rules = [
        'phone' => 'required',
        'message' => 'required|min:5',
    ];

    public function send(WhatsappService $whatsappService)
    {
        $this->validate();

        $result = $whatsappService->sendMessage($this->phone, $this->message);

        if ($result && (
            (isset($result['status']) && $result['status'] === 'success') || 
            (isset($result['message']) && str_contains(strtolower($result['message']), 'success')) ||
            (isset($result['code']) && ($result['code'] == 200 || $result['code'] == 201)) ||
            (!isset($result['error']) && !isset($result['status'])) // fallback
        )) {
            session()->flash('success', "Pesan WA berhasil dikirim ke {$this->phone}.");
            $this->reset(['phone', 'message']);
        } else {
            session()->flash('error', "Gagal mengirim pesan WA. GOWA mungkin membalas dengan status berbeda.");
        }
    }

    public function render()
    {
        return view('livewire.admin.wa-contact-form')->layout('layouts.main');
    }
}
