<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly ?string $ticketContent = null,
        public readonly string  $ticketMime    = 'application/pdf',
        public readonly string  $ticketExt     = 'pdf'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from:    config('notifications.from_address', 'hello@ticketly.com'),
            subject: '🎟 Booking Confirmed – ' . $this->booking->reference . ' | ' . $this->booking->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmed',
        );
    }

    public function attachments(): array
    {
        if (!$this->ticketContent) return [];

        $filename = 'ticket-' . $this->booking->reference . '.' . $this->ticketExt;

        return [
            Attachment::fromData(
                fn () => $this->ticketContent,
                $filename
            )->withMime($this->ticketMime),
        ];
    }
}
