<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
        public readonly float   $refundAmount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from:    config('notifications.from_address', 'hello@ticketly.com'),
            subject: '💰 Refund Confirmed – ' . $this->booking->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.refund-confirmed');
    }
}