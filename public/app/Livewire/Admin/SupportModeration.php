<?php

namespace App\Livewire\Admin;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SupportModeration extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $selectedTicketId;
    public $reply_message;
    public $attachments = [];
    public $filterStatus = 'all';

    protected $rules = [
        'reply_message' => 'required|min:5',
        'attachments.*' => 'nullable|file|max:2048',
        'attachments' => 'nullable|array|max:5',
    ];

    public function selectTicket($id)
    {
        $this->selectedTicketId = $id;
        $this->reply_message = '';
        $this->attachments = [];
        
        $ticket = SupportTicket::find($id);
        if ($ticket) {
            $ticket->update(['is_viewed_by_admin' => true]);
        }
    }

    public function sendReply()
    {
        $this->validate();

        $ticket = SupportTicket::with('user')->findOrFail($this->selectedTicketId);
        
        $messageModel = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => Auth::id(), // Admin as sender
            'message' => $this->reply_message,
            'is_from_admin' => true,
        ]);

        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                $messageModel->addMedia($file->getRealPath())
                    ->usingName($file->getClientOriginalName())
                    ->toMediaCollection('attachments');
            }
        }

        $ticket->update([
            'status' => 'replied',
            'is_viewed_by_user' => false,
            'updated_at' => now(),
        ]);

        // --- Notify Member ---
        $this->notifyUser($ticket, $this->reply_message);

        $this->reply_message = '';
        $this->attachments = [];
        session()->flash('success', 'Balasan berhasil dikirim ke user.');
    }

    public function closeTicket($id)
    {
        SupportTicket::findOrFail($id)->update(['status' => 'closed']);
        session()->flash('success', 'Tiket berhasil ditutup.');
    }

    protected function notifyUser(SupportTicket $ticket, $reply)
    {
        $user = $ticket->user;
        if (!$user) return;

        // WhatsApp Notification
        try {
            $whatsapp = new WhatsappService();
            if ($user->whatsapp) {
                $msg = "🔔 *Pesan Bantuan Sebatam*\n\nAdmin telah membalas pesan Anda terkait: *{$ticket->subject}*.\n\n\"" . Str::limit($reply, 100) . "\"\n\nSilakan cek selengkapnya di dashboard member Sebatam.";
                $whatsapp->sendMessage($user->whatsapp, $msg);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Support WA fail: ' . $e->getMessage());
        }

        // Email Notification
        try {
            Mail::to($user->email)->send(new \App\Mail\SupportReplyNotification($ticket, $reply));
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Support Email fail: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = SupportTicket::with(['user', 'messages' => function($q) {
                $q->latest();
            }])
            ->latest('updated_at');

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $tickets = $query->paginate(10);

        $selectedTicket = null;
        if ($this->selectedTicketId) {
            $selectedTicket = SupportTicket::with(['messages' => function($q) {
                    $q->oldest();
                }, 'messages.media', 'user'])
                ->find($this->selectedTicketId);
        }

        return view('livewire.admin.support-moderation', [
            'tickets' => $tickets,
            'selectedTicket' => $selectedTicket
        ])->layout('layouts.main');
    }
}
