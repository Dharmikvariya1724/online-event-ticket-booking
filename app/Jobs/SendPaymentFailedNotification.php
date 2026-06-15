<?php

namespace App\Jobs;

use App\Mail\PaymentFailed;
use App\Models\Reservation;
use App\Models\EmailLog;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentFailedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 10;

    public function __construct(
        public readonly Reservation $reservation,
        public readonly string      $errorMessage = 'Your payment could not be processed.'
    ) {
        $this->onQueue(config('notifications.queue', 'notifications'));
    }

    public function handle(SmsService $sms): void
    {
        $reservation = $this->reservation->fresh(['event']);

        if (!$reservation->customer_email && !$reservation->customer_phone) {
            Log::warning('[SendPaymentFailedNotification] No customer contact details', [
                'reservation' => $reservation->token,
            ]);
            return;
        }

        Log::info('[SendPaymentFailedNotification] Processing', [
            'reservation' => $reservation->token,
            'attempt'     => $this->attempts(),
        ]);

        // Email
        if ($reservation->customer_email) {
            try {
                Mail::to($reservation->customer_email)
                    ->send(new PaymentFailed($reservation, $this->errorMessage));

                Log::info('[SendPaymentFailedNotification] Email sent', [
                    'to' => $reservation->customer_email,
                ]);
                EmailLog::logSent(
                    $reservation->customer_email,
                    'Payment failed - ' . $reservation->event->title,
                    'payment_failed',
                    $reservation,
                    ['error' => $this->errorMessage]
                );
            } catch (\Exception $e) {
                Log::error('[SendPaymentFailedNotification] Email failed', [
                    'reservation' => $reservation->token,
                    'error'       => $e->getMessage(),
                ]);
                EmailLog::logFailed(
                    $reservation->customer_email,
                    'Payment failed - ' . $reservation->event->title,
                    $e->getMessage(),
                    'payment_failed',
                    $reservation,
                    ['error' => $this->errorMessage]
                );
                throw $e;
            }
        } else {
            Log::warning('[SendPaymentFailedNotification] Email skipped (missing customer_email)', [
                'reservation' => $reservation->token,
            ]);
        }

        // SMS
        if ($reservation->customer_phone) {
            $secsLeft = $reservation->secondsRemaining();
            $minsLeft = $secsLeft > 0 ? ceil($secsLeft / 60) : 0;
            $retryUrl = route('checkout.show', $reservation->token);

            $smsBody = "Ticketly: Payment failed for {$reservation->event->title}.\n";

            if ($minsLeft > 0) {
                $smsBody .= "Retry checkout within {$minsLeft} min.\nRetry: {$retryUrl}";
            } else {
                $smsBody .= "Hold expired. Rebook from event page.";
            }

            $sms->send($reservation->customer_phone, $smsBody);
        } else {
            Log::warning('[SendPaymentFailedNotification] SMS skipped (missing customer_phone)', [
                'reservation' => $reservation->token,
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[SendPaymentFailedNotification] Job permanently failed', [
            'reservation' => $this->reservation->token,
            'error'       => $e->getMessage(),
        ]);
    }
}
