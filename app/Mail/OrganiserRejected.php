<?php

namespace App\Mail;

use App\Models\Organiser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganiserRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Organiser $organiser,
        public readonly string $reason
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('notifications.from_address', 'hello@ticketly.com'),
            subject: 'Your Ticketly organiser account was not approved',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.organiser-rejected');
    }
}
