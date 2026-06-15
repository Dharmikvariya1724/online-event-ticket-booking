<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFailed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Reservation $reservation,
        public readonly string      $errorMessage = 'Your payment could not be processed.'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from:    config('notifications.from_address', 'hello@ticketly.com'),
            subject: '⚠️ Payment Failed – ' . $this->reservation->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-failed');
    }
}