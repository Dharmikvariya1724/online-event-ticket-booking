<?php

namespace App\Http\Controllers;

use App\Jobs\SendBookingConfirmation;
use App\Models\Booking;
use App\Models\Organiser;
use App\Models\Payout;
use App\Models\Reservation;
use App\Repositories\BookingRepository;
use App\Services\PaymentFailureNotifier;
use App\Services\RefundService;
use App\Services\ServiceFeeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Refund as StripeRefund;
use Stripe\Stripe;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function __construct(
        private readonly BookingRepository $bookingRepo,
        private readonly RefundService     $refundService,
        private readonly PaymentFailureNotifier $paymentFailureNotifier
    ) {}

    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload');
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature');
            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook: ' . $event->type, ['id' => $event->id]);

        match ($event->type) {
            'payment_intent.succeeded'      => $this->handlePaymentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
            'charge.refunded'               => $this->handleChargeRefunded($event->data->object),
            'checkout.session.completed'    => $this->handleCheckoutSession($event->data->object),
            'payout.created'                => $this->handlePayoutEvent($event->data->object, $event->account ?? null),
            'payout.updated'                => $this->handlePayoutEvent($event->data->object, $event->account ?? null),
            'payout.paid'                   => $this->handlePayoutEvent($event->data->object, $event->account ?? null),
            'payout.failed'                 => $this->handlePayoutEvent($event->data->object, $event->account ?? null),
            'payout.canceled'               => $this->handlePayoutEvent($event->data->object, $event->account ?? null),
            default                         => null,
        };

        return response('OK', 200);
    }

    private function handlePaymentSucceeded(object $intent): void
    {
        $reservation = $this->findReservation($intent);
        if (!$reservation) return;
        if ($reservation->status === 'completed') return;

        if ($reservation->status !== 'pending' || $reservation->isExpired()) {
            if ($reservation->status === 'pending' && $reservation->isExpired()) {
                ReservationController::releaseReservation($reservation);
            }

            Log::warning('Webhook: payment arrived for expired/inactive reservation, auto-refunding', [
                'reservation'    => $reservation->token,
                'payment_intent' => $intent->id,
                'status'         => $reservation->status,
            ]);
            $this->refundExpiredIntent($intent->id);
            return;
        }

        $existing = $this->bookingRepo->findByPaymentIntent($intent->id);
        if ($existing) return;

        $booking = $this->bookingRepo->createFromReservation($reservation, $intent->id);

        if (!empty($intent->latest_charge)) {
            $booking->update(['stripe_charge_id' => $intent->latest_charge]);
        }

        SendBookingConfirmation::dispatchSync($booking);

        Log::info('Webhook: booking created ' . $booking->reference);
    }

    private function handlePaymentFailed(object $intent): void
    {
        $reservation = $this->findReservation($intent);
        if (!$reservation) return;

        $error = $intent->last_payment_error?->message ?? 'Payment could not be processed.';
        $this->paymentFailureNotifier->notify($reservation, $error);
    }

    private function handleChargeRefunded(object $charge): void
    {
        // If refund was issued outside our system (e.g. Stripe dashboard)
        $booking = Booking::where('stripe_charge_id', $charge->id)->first()
            ?? Booking::where('stripe_payment_intent_id', $charge->payment_intent)->first();

        if (!$booking || $booking->isFullyRefunded()) return;

        $refundAmount = $charge->amount_refunded / 100;
        $isFullRefund = (bool) $charge->refunded;

        $booking->update([
            'status'           => $isFullRefund ? 'refunded' : 'partially_refunded',
            'refund_amount'    => $refundAmount,
            'refunded_at'      => now(),
            'stripe_charge_id' => $charge->id,
        ]);

        dispatch(new \App\Jobs\SendRefundConfirmation($booking->fresh(), $refundAmount));

        Log::info('Webhook: refund recorded for ' . $booking->reference);
    }

    private function handleCheckoutSession(object $session): void
    {
        if ($session->payment_status !== 'paid' || !$session->payment_intent) return;

        Stripe::setApiKey(config('services.stripe.secret'));
        $intent      = \Stripe\PaymentIntent::retrieve($session->payment_intent);
        $reservation = $this->findReservation($intent);
        if (!$reservation) return;
        if ($reservation->status === 'completed') return;

        if ($reservation->status !== 'pending' || $reservation->isExpired()) {
            if ($reservation->status === 'pending' && $reservation->isExpired()) {
                ReservationController::releaseReservation($reservation);
            }

            Log::warning('Webhook: checkout.session.completed for expired/inactive reservation, auto-refunding', [
                'reservation'    => $reservation->token,
                'payment_intent' => $intent->id,
                'status'         => $reservation->status,
            ]);
            $this->refundExpiredIntent($intent->id);
            return;
        }

        $existing = $this->bookingRepo->findByPaymentIntent($intent->id);
        if ($existing) return;

        $booking = $this->bookingRepo->createFromReservation($reservation, $intent->id);
        SendBookingConfirmation::dispatchSync($booking);
    }

    private function findReservation(object $intent): ?Reservation
    {
        if (!empty($intent->metadata['reservation_id'])) {
            $r = Reservation::find((int) $intent->metadata['reservation_id']);
            if ($r) return $r;
        }
        return Reservation::where('stripe_payment_intent_id', $intent->id)->first();
    }

    private function refundExpiredIntent(string $paymentIntentId): void
    {
        try {
            StripeRefund::create([
                'payment_intent' => $paymentIntentId,
                'reason'         => 'requested_by_customer',
                'metadata'       => ['auto_refund' => 'expired_hold'],
            ]);
        } catch (\Exception $e) {
            Log::warning('Webhook: auto-refund for expired hold failed', [
                'payment_intent' => $paymentIntentId,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    private function handlePayoutEvent(object $payout, ?string $accountId): void
    {
        if (empty($accountId)) return;

        $organiser = Organiser::where('stripe_account_id', $accountId)->first();
        if (!$organiser) return;

        $amount = ServiceFeeCalculator::fromPence((int) ($payout->amount ?? 0));
        $currency = strtoupper((string) ($payout->currency ?? ticketly_currency()));
        $status = (string) ($payout->status ?? 'pending');

        Payout::updateOrCreate(
            ['stripe_payout_id' => (string) $payout->id],
            [
                'user_id'  => $organiser->id,
                'amount'   => $amount,
                'currency' => $currency,
                'status'   => $status,
            ]
        );
    }
}
