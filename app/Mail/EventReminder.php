<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminder extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $label;

    public function __construct(
        public readonly Booking $booking,
        public readonly string $window
    ) {
        $this->label = $this->resolveReminderLabel();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('notifications.from_address', 'hello@ticketly.com'),
            subject: 'Event Reminder - ' . $this->getReminderSubjectSuffix() . ' | ' . $this->booking->event->title,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.event-reminder');
    }

    private function getReminderSubjectSuffix(): string
    {
        return match ($this->window) {
            '48h'       => 'In 48 hours',
            '24h'       => 'In 24 hours',
            'event_day' => 'Today at 8:00 AM',
            default     => 'Upcoming Event',
        };
    }

    private function resolveReminderLabel(): string
    {
        return match ($this->window) {
            '48h'       => 'Your event is in 2 days',
            '24h'       => 'Your event is tomorrow',
            'event_day' => 'Your event is today',
            default     => 'Your event is coming up',
        };
    }
}
