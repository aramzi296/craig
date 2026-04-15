<?php

namespace App\Livewire\User;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class SupportShow extends Component
{
    use WithFileUploads;

    public $ticket_id;
    public $reply_message;
    public $attachments = [];

    protected $rules = [
        'reply_message' => 'required|min:2',
        'attachments.*' => 'nullable|file|max:2048',
        'attachments' => 'nullable|array|max:5',
    ];

    public function mount($ticket)
    {
        $this->ticket_id = $ticket;
        $ticketModel = $this->getTicket();
        // Mark as viewed by user
        if (!$ticketModel->is_viewed_by_user) {
            $ticketModel->update(['is_viewed_by_user' => true]);
        }
    }

    public function getTicket()
    {
        return SupportTicket::with(['messages' => function($q) {
                $q->oldest();
            }, 'messages.media'])
            ->where('id', $this->ticket_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function sendReply()
    {
        $this->validate();

        $ticket = $this->getTicket();
        
        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $this->reply_message,
            'is_from_admin' => false,
        ]);

        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $message->addMedia($file->getRealPath())
                    ->usingName($file->getClientOriginalName())
                    ->toMediaCollection('attachments');
            }
        }

        // Update ticket status or last_activity
        $ticket->update([
            'status' => 'open',
            'is_viewed_by_admin' => false,
            'is_viewed_by_user' => true,
            'updated_at' => now()
        ]);

        $this->reply_message = '';
        $this->attachments = [];
        session()->flash('success', 'Balasan berhasil dikirim.');
    }

    public function render()
    {
        return view('livewire.user.support-show', [
            'ticket' => $this->getTicket()
        ])->layout('layouts.main');
    }
}
