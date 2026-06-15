<?php

namespace App\Jobs;

use App\Mail\EventReminder;
use App\Models\Booking;
use App\Models\EmailLog;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    /**
     * @param Booking $booking
     * @param string  $window '48h' | '24h' | 'event_day'
     */
    public function __construct(
        public readonly Booking $booking,
        public readonly string $window
    ) {
        $this->onQueue(config('notifications.queue', 'notifications'));
    }

    public function handle(SmsService $sms): void
    {
        $booking = $this->booking->fresh(['event']);

        if (!$booking || !$booking->event) {
            Log::warning('[SendEventReminder] Skipping - booking/event missing', [
                'window' => $this->window,
            ]);
            return;
        }

        // Guard: do not remind for cancelled/refunded bookings.
        if (!$booking->isPaid()) {
            Log::info('[SendEventReminder] Skipping - booking not paid', [
                'booking' => $booking->reference,
                'window'  => $this->window,
            ]);
            return;
        }

        // Guard: do not remind for past events.
        if ($booking->event->starts_at->isPast()) {
            return;
        }

        Log::info('[SendEventReminder] Sending reminder', [
            'booking' => $booking->reference,
            'window'  => $this->window,
        ]);

        // Email reminder
        if ($booking->customer_email) {
            try {
                Mail::to($booking->customer_email)->send(new EventReminder($booking, $this->window));
                EmailLog::logSent(
                    $booking->customer_email,
                    'Event reminder - ' . $booking->event->title,
                    'event_reminder',
                    $booking,
                    ['window' => $this->window]
                );
            } catch (\Exception $e) {
                EmailLog::logFailed(
                    $booking->customer_email,
                    'Event reminder - ' . $booking->event->title,
                    $e->getMessage(),
                    'event_reminder',
                    $booking,
                    ['window' => $this->window]
                );
                throw $e;
            }
        }

        // SMS reminder
        if ($booking->customer_phone) {
            $label   = $this->getReminderLabel();
            $date    = ticketly_format_datetime($booking->event->starts_at);
            $smsBody = "Ticketly Reminder - {$label}\n"
                . "{$booking->event->title}\n"
                . "Date: {$date}\n"
                . "Venue: {$booking->event->venue_name}, {$booking->event->city}\n"
                . "Ref: {$booking->reference}";

            $sent = $sms->send($booking->customer_phone, $smsBody);
            if (!$sent) {
                Log::warning('[SendEventReminder] SMS delivery failed', [
                    'booking' => $booking->reference,
                    'phone'   => $booking->customer_phone,
                    'window'  => $this->window,
                ]);
            }
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[SendEventReminder] Job failed', [
            'booking' => $this->booking->reference,
            'window'  => $this->window,
            'error'   => $e->getMessage(),
        ]);
    }

    private function getReminderLabel(): string
    {
        return match ($this->window) {
            '48h'       => 'Your event is in 2 days',
            '24h'       => 'Your event is tomorrow',
            'event_day' => 'Your event is today',
            default     => 'Your event is coming up',
        };
    }
}
