<?php

namespace App\Livewire\User;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class SupportCreate extends Component
{
    use WithFileUploads;

    public $subject;
    public $message;
    public $category = 'pertanyaan';
    public $attachments = [];

    protected $rules = [
        'subject' => 'required|min:5|max:255',
        'message' => 'required|min:10',
        'category' => 'required|in:keluhan,pertanyaan,permintaan_fitur,lainnya',
        'attachments.*' => 'nullable|file|max:2048', // 2MB
        'attachments' => 'nullable|array|max:5',
    ];

    public function submit()
    {
        $this->validate();

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $this->subject,
            'category' => $this->category,
            'status' => 'open',
            'is_viewed_by_admin' => false,
            'is_viewed_by_user' => true,
        ]);

        $supportMessage = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $this->message,
            'is_from_admin' => false,
        ]);

        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $supportMessage->addMedia($file->getRealPath())
                    ->usingName($file->getClientOriginalName())
                    ->toMediaCollection('attachments');
            }
        }

        return redirect()->route('user.support.show', $ticket->id)
            ->with('success', 'Pesan Anda telah terkirim ke admin. Kami akan segera merespon.');
    }

    public function render()
    {
        return view('livewire.user.support-create')->layout('layouts.main');
    }
}
