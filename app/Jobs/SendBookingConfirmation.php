<?php

namespace App\Jobs;

use App\Mail\BookingConfirmed;
use App\Mail\PaymentFailed;
use App\Mail\RefundConfirmed;
use App\Models\Booking;
use App\Models\EmailLog;
use App\Services\PdfTicketService;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int    $tries   = 3;
    public int    $timeout = 60;
    public int    $backoff = 30; // seconds between retries

    public function __construct(public readonly Booking $booking)
    {
        $this->onQueue(config('notifications.queue', 'notifications'));
    }

    public function handle(SmsService $sms, PdfTicketService $pdf): void
    {
        $booking = $this->booking->fresh(['event', 'items.ticketTier', 'promoCode']);

        Log::info('[SendBookingConfirmation] Processing', [
            'booking'  => $booking->reference,
            'attempt'  => $this->attempts(),
        ]);

        // ── Generate PDF ticket ────────────────────────────────────
        $ticketContent = null;
        $ticketMime    = 'application/pdf';
        $ticketExt     = 'pdf';

        try {
            $ticketContent = $pdf->generate($booking);
            $ticketMime    = $pdf->getMimeType();
            $ticketExt     = $pdf->getExtension();
        } catch (\Exception $e) {
            Log::warning('[SendBookingConfirmation] PDF generation failed – continuing without attachment', [
                'booking' => $booking->reference,
                'error'   => $e->getMessage(),
            ]);
        }

        // ── Send Email ─────────────────────────────────────────────
        $shouldSendEmail = !empty($booking->customer_email)
            && !str_ends_with(strtolower((string) $booking->customer_email), '@ticketly.invalid');

        if ($shouldSendEmail) {
            try {
                Mail::to($booking->customer_email)
                    ->send(new BookingConfirmed($booking, $ticketContent, $ticketMime, $ticketExt));

                Log::info('[SendBookingConfirmation] Email sent', ['to' => $booking->customer_email]);
                EmailLog::logSent(
                    $booking->customer_email,
                    'Booking confirmed - ' . $booking->reference,
                    'booking_confirmed',
                    $booking,
                    ['booking_id' => $booking->id]
                );
            } catch (\Exception $e) {
                Log::error('[SendBookingConfirmation] Email failed', [
                    'booking' => $booking->reference,
                    'error'   => $e->getMessage(),
                ]);
                EmailLog::logFailed(
                    $booking->customer_email,
                    'Booking confirmed - ' . $booking->reference,
                    $e->getMessage(),
                    'booking_confirmed',
                    $booking,
                    ['booking_id' => $booking->id]
                );
                throw $e; // Re-throw so queue retries
            }
        } else {
            Log::info('[SendBookingConfirmation] Email skipped for guest checkout', [
                'booking' => $booking->reference,
            ]);
        }

        // ── Send SMS ───────────────────────────────────────────────
        if ($booking->customer_phone) {
            $smsBody = $this->buildBookingSms($booking);
            $sent    = $sms->send($booking->customer_phone, $smsBody);

            if (!$sent) {
                Log::warning('[SendBookingConfirmation] SMS delivery failed', [
                    'booking' => $booking->reference,
                    'phone'   => $booking->customer_phone,
                ]);
                // Don't fail the whole job for SMS – email is primary
            }
        }

        // ── Mark as sent ───────────────────────────────────────────
        $booking->update(['confirmation_sent_at' => now()]);

        Log::info('[SendBookingConfirmation] Completed', ['booking' => $booking->reference]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[SendBookingConfirmation] Job permanently failed after ' . $this->tries . ' attempts', [
            'booking' => $this->booking->reference,
            'error'   => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
    }

    private function buildBookingSms(Booking $booking): string
    {
        $date    = ticketly_format_datetime($booking->event->starts_at);
        $tickets = $booking->items->sum('quantity');
        return "🎟 Ticketly – Booking Confirmed!\n"
            . "Ref: {$booking->reference}\n"
            . "Event: {$booking->event->title}\n"
            . "Date: {$date}\n"
            . "Venue: {$booking->event->venue_name}, {$booking->event->city}\n"
            . "Tickets: {$tickets}\n"
            . "Total: " . ticketly_money($booking->total) . "\n"
            . "Present your ref at the door. Enjoy the event!";
    }
}
