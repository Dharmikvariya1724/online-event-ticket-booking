<?php

namespace App\Services;

use App\Jobs\SendPaymentFailedNotification;
use App\Models\Reservation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentFailureNotifier
{
    public function notify(Reservation $reservation, string $errorMessage = 'Your payment could not be processed.'): void
    {
        $reservation = $reservation->fresh(['event']) ?? $reservation->loadMissing('event');

        if (!$reservation->customer_email && !$reservation->customer_phone) {
            Log::warning('[PaymentFailureNotifier] No contact details, notification skipped', [
                'reservation' => $reservation->token,
            ]);
            return;
        }

        $cacheKey = sprintf(
            'payment_failed_notified:reservation:%d:intent:%s',
            $reservation->id,
            $reservation->stripe_payment_intent_id ?? 'none'
        );

        if (!Cache::add($cacheKey, now()->timestamp, now()->addMinutes(30))) {
            return;
        }

        try {
            // Attempt immediate delivery so customer is notified quickly.
            SendPaymentFailedNotification::dispatchSync($reservation, $errorMessage);
        } catch (\Throwable $e) {
            Log::warning('[PaymentFailureNotifier] Immediate notification failed, queueing retry', [
                'reservation' => $reservation->token,
                'error'       => $e->getMessage(),
            ]);

            SendPaymentFailedNotification::dispatch($reservation, $errorMessage)
                ->delay(now()->addSeconds(10));
        }
    }
}
