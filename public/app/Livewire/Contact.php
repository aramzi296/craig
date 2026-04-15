<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\WhatsappService;

class Contact extends Component
{
    public $name;
    public $email;
    public $subject;
    public $message;

    protected $rules = [
        'name' => 'required|string|min:3',
        'email' => 'required|email',
        'subject' => 'required|string',
        'message' => 'required|string|min:10',
    ];

    public function sendMessage()
    {
        $this->validate();

        // Save to database
        \App\Models\ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'is_read' => false,
        ]);

        // Admin WhatsApp from config or fallback
        $adminWa = config('services.whatsapp.admin_number') ?: '628123456789';
        
        $text = "📩 *Pesan Kontak Baru - Sebatam.com*\n\n"
              . "*Nama:* {$this->name}\n"
              . "*Email:* {$this->email}\n"
              . "*Subjek:* {$this->subject}\n\n"
              . "*Pesan:* \n{$this->message}";

        try {
            app(WhatsappService::class)->sendMessage($adminWa, $text);
            
            $this->reset(['name', 'email', 'subject', 'message']);
            session()->flash('success', 'Pesan Anda berhasil dikirim ke Admin Sebatam. Terima kasih!');
            $this->dispatch('swal', ['title' => 'Terkirim!', 'text' => 'Pesan Anda telah kami terima.', 'icon' => 'success']);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengirim pesan. Silakan coba lagi nanti.');
        }
    }

    public function render()
    {
        return view('livewire.contact')->layout('layouts.main');
    }
}
