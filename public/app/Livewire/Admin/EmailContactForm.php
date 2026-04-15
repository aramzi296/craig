<?php

namespace App\Livewire\Admin;

use App\Mail\AdminSentEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class EmailContactForm extends Component
{
    public string $to = '';

    public string $subject = '';

    public string $body = '';

    protected function rules(): array
    {
        return [
            'to' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:5'],
        ];
    }

    public function send(): void
    {
        $this->validate();

        $user = Auth::user();

        try {
            Mail::to($this->to)->send(new AdminSentEmail(
                $this->subject,
                $this->body,
                $user->email,
                $user->name,
            ));
        } catch (\Throwable $e) {
            session()->flash('error', 'Email gagal terkirim: '.$e->getMessage());

            return;
        }

        session()->flash('success', 'Email berhasil dikirim ke '.$this->to.'.');
        $this->reset(['to', 'subject', 'body']);
    }

    public function render()
    {
        return view('livewire.admin.email-contact-form')->layout('layouts.main');
    }
}
