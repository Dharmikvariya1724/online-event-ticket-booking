<?php

namespace App\Mail;

use App\Models\Organiser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewOrganiser extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Organiser $organiser) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('notifications.from_address', 'hello@ticketly.com'),
            subject: 'New organiser registration - ' . $this->organiser->company_name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-new-organiser');
    }
}
