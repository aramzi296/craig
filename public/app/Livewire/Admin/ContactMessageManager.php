<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ContactMessage;
use App\Mail\AdminSentEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class ContactMessageManager extends Component
{
    use WithPagination;

    public $selectedMessage = null;
    public $replyMessage = null;
    public $replySubject = '';
    public $replyBody = '';

    protected $listeners = ['refreshMessages' => '$refresh'];

    public function render()
    {
        $messages = ContactMessage::latest()->paginate(10);
        return view('livewire.admin.contact-message-manager', [
            'messages' => $messages
        ])->layout('layouts.main');
    }

    public function viewMessage($id)
    {
        $this->selectedMessage = ContactMessage::findOrFail($id);
        $this->selectedMessage->update(['is_read' => true]);
        $this->dispatch('refreshMessages');
    }

    public function closeModal()
    {
        $this->selectedMessage = null;
        $this->replyMessage = null;
        $this->reset(['replySubject', 'replyBody']);
    }

    public function openReply($id)
    {
        $this->replyMessage = ContactMessage::findOrFail($id);
        $this->replySubject = 'Re: ' . $this->replyMessage->subject;
        $this->replyBody = "";
    }

    public function sendReply()
    {
        $this->validate([
            'replySubject' => 'required',
            'replyBody' => 'required|min:5'
        ]);

        try {
            Mail::to($this->replyMessage->email)->send(new AdminSentEmail(
                $this->replySubject,
                $this->replyBody,
                Auth::user()->email,
                Auth::user()->name
            ));
            
            $this->replyMessage->update([
                'is_read' => true,
                'reply_message' => $this->replyBody,
                'replied_at' => now(),
            ]);

            $this->closeModal();
            $this->dispatch('swal', ['title' => 'Terkirim!', 'text' => 'Balasan email berhasil dikirim.', 'icon' => 'success']);
        } catch (\Exception $e) {
            $this->dispatch('swal', ['title' => 'Gagal!', 'text' => 'Gagal mengirim email: ' . $e->getMessage(), 'icon' => 'error']);
        }
    }

    public function deleteMessage($id)
    {
        ContactMessage::findOrFail($id)->delete();
        $this->dispatch('swal', ['title' => 'Terhapus!', 'text' => 'Pesan telah dihapus.', 'icon' => 'success']);
    }
}
