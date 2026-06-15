<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Event $event) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('notifications.from_address', 'hello@ticketly.com'),
            subject: 'Your event has been approved - ' . $this->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.event-approved');
    }
}
