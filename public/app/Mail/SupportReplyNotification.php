<?php

namespace App\Mail;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportReplyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $reply;

    public function __construct(SupportTicket $ticket, string $reply)
    {
        $this->ticket = $ticket;
        $this->reply = $reply;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Balasan Bantuan Sebatam: ' . $this->ticket->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.support-reply-notification',
            with: [
                'ticket' => $this->ticket,
                'reply' => $this->reply,
                'url' => route('user.support.show', $this->ticket->id),
            ],
        );
    }
}
