<?php

namespace App\Jobs;

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

class SendEventCancellationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        public readonly Booking $booking,
        public readonly string  $reason
    ) {
        $this->onQueue(config('notifications.queue', 'notifications'));
    }

    public function handle(SmsService $sms): void
    {
        $booking = $this->booking->fresh(['event', 'items.ticketTier']);

        Log::info('[SendEventCancellationNotification] Processing', [
            'booking' => $booking->reference,
            'attempt' => $this->attempts(),
        ]);

        // ── Email ──────────────────────────────────────────────────
        try {
            Mail::send('emails.event-cancelled', [
                'booking' => $booking,
                'reason'  => $this->reason,
            ], function ($m) use ($booking) {
                $m->to($booking->customer_email)
                  ->subject('⚠️ Event Cancelled – ' . $booking->event->title . ' | Refund Initiated');
            });

            Log::info('[SendEventCancellationNotification] Email sent', ['to' => $booking->customer_email]);
            EmailLog::logSent(
                $booking->customer_email,
                'Event cancelled - ' . $booking->event->title,
                'event_cancelled',
                $booking,
                ['reason' => $this->reason]
            );
        } catch (\Exception $e) {
            Log::error('[SendEventCancellationNotification] Email failed', ['error' => $e->getMessage()]);
            EmailLog::logFailed(
                $booking->customer_email,
                'Event cancelled - ' . $booking->event->title,
                $e->getMessage(),
                'event_cancelled',
                $booking,
                ['reason' => $this->reason]
            );
            throw $e;
        }

        // ── SMS ────────────────────────────────────────────────────
        if ($booking->customer_phone) {
            $smsBody = "⚠️ IMPORTANT: {$booking->event->title} has been CANCELLED.\n"
                . "Ref: {$booking->reference}\n"
                . "A full refund of " . ticketly_money($booking->total) . " will be processed within 5-10 working days.\n"
                . "Apologies for the inconvenience – Ticketly";

            $sms->send($booking->customer_phone, $smsBody);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[SendEventCancellationNotification] Permanently failed', [
            'booking' => $this->booking->reference,
            'error'   => $e->getMessage(),
        ]);
    }
}
