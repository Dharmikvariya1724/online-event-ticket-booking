<?php

namespace App\Jobs;

use App\Mail\RefundConfirmed;
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

class SendRefundConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        public readonly Booking $booking,
        public readonly float   $refundAmount
    ) {
        $this->onQueue(config('notifications.queue', 'notifications'));
    }

    public function handle(SmsService $sms): void
    {
        $booking = $this->booking->fresh(['event', 'items.ticketTier', 'promoCode']);

        Log::info('[SendRefundConfirmation] Processing', [
            'booking' => $booking->reference,
            'amount'  => $this->refundAmount,
            'attempt' => $this->attempts(),
        ]);

        // ── Email ──────────────────────────────────────────────────
        try {
            Mail::to($booking->customer_email)
                ->send(new RefundConfirmed($booking, $this->refundAmount));

            Log::info('[SendRefundConfirmation] Email sent', ['to' => $booking->customer_email]);
            EmailLog::logSent(
                $booking->customer_email,
                'Refund confirmed - ' . $booking->reference,
                'refund_confirmed',
                $booking,
                ['refund_amount' => $this->refundAmount]
            );
        } catch (\Exception $e) {
            Log::error('[SendRefundConfirmation] Email failed', [
                'booking' => $booking->reference,
                'error'   => $e->getMessage(),
            ]);
            EmailLog::logFailed(
                $booking->customer_email,
                'Refund confirmed - ' . $booking->reference,
                $e->getMessage(),
                'refund_confirmed',
                $booking,
                ['refund_amount' => $this->refundAmount]
            );
            throw $e;
        }

        // ── SMS ────────────────────────────────────────────────────
        if ($booking->customer_phone) {
            $amount  = number_format($this->refundAmount, 2);
            $smsBody = "💰 Ticketly – Refund Confirmed\n"
                . "Ref: {$booking->reference}\n"
                . "Event: {$booking->event->title}\n"
                . "Refund: " . ticketly_currency_symbol() . "{$amount}\n"
                . "Timeline: 5–10 business days to appear on your statement.\n"
                . "Questions? Email support@ticketly.com";

            $sms->send($booking->customer_phone, $smsBody);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[SendRefundConfirmation] Job permanently failed', [
            'booking' => $this->booking->reference,
            'amount'  => $this->refundAmount,
            'error'   => $e->getMessage(),
        ]);
    }
}
