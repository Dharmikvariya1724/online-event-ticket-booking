<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BookingRepository
 *
 * Responsible for the single operation of converting a completed
 * Stripe PaymentIntent into a persisted Booking record.
 *
 * Idempotent: safe to call multiple times for the same reservation
 * (webhook retries, poll fallback). Uses lockForUpdate to prevent races.
 */
class BookingRepository
{
    /**
     * Create a booking from a confirmed reservation.
     * Returns existing booking if already created (idempotent).
     */
    public function createFromReservation(Reservation $reservation, string $paymentIntentId): Booking
    {
        return DB::transaction(function () use ($reservation, $paymentIntentId) {
            // Idempotency check inside transaction
            $existing = Booking::where('reservation_id', $reservation->id)
                ->where('status', 'paid')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                Log::info('[BookingRepository] Booking already exists – returning.', [
                    'reference' => $existing->reference,
                ]);
                return $existing;
            }

            $reservation->loadMissing('items');
            $customerName = trim((string) ($reservation->customer_name ?? ''));
            $customerEmail = trim((string) ($reservation->customer_email ?? ''));
            $customerPhone = trim((string) ($reservation->customer_phone ?? ''));

            if ($customerName === '') {
                $customerName = 'Guest Customer';
            }
            if ($customerEmail === '') {
                $customerEmail = $this->guestEmailForReservation($reservation);
            }
            if ($customerPhone === '') {
                $customerPhone = null;
            }

            $reservation->update([
                'customer_name'  => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
            ]);

            $customer = null;
            if (!$this->isGuestEmail($customerEmail)) {
                $customer = Customer::updateOrCreate(
                    ['email' => $customerEmail],
                    [
                        'name'  => $customerName,
                        'phone' => $customerPhone,
                    ]
                );
            }

            $booking = Booking::create([
                'event_id'                 => $reservation->event_id,
                'reservation_id'           => $reservation->id,
                'customer_id'              => $customer?->id,
                'customer_name'            => $customerName,
                'customer_email'           => $customerEmail,
                'customer_phone'           => $customerPhone,
                'promo_code_id'            => $reservation->promo_code_id,
                'discount_amount'          => $reservation->discount_amount,
                'subtotal'                 => $reservation->subtotal,
                'portal_fee'               => $reservation->portal_fee ?? 0,
                'service_fee'              => $reservation->service_fee ?? 0,
                'total'                    => $reservation->total,
                'currency'                 => ticketly_currency(),
                'stripe_payment_intent_id' => $paymentIntentId,
                'status'                   => 'paid',
            ]);

            foreach ($reservation->items as $item) {
                BookingItem::create([
                    'booking_id'     => $booking->id,
                    'ticket_tier_id' => $item->ticket_tier_id,
                    'quantity'       => $item->quantity,
                    'unit_price'     => $item->unit_price,
                    'subtotal'       => $item->subtotal,
                ]);
            }

            // Increment promo usage
            if ($reservation->promo_code_id) {
                $reservation->promoCode?->increment('used_count');
            }

            $reservation->update(['status' => 'completed']);

            Log::info('[BookingRepository] Booking created.', [
                'reference' => $booking->reference,
                'total'     => $booking->total,
            ]);

            return $booking;
        });
    }

    /**
     * Find a booking by Stripe PaymentIntent ID.
     */
    public function findByPaymentIntent(string $intentId): ?Booking
    {
        return Booking::where('stripe_payment_intent_id', $intentId)->first();
    }

    /**
     * Find a booking by reference (customer-facing).
     */
    public function findByReference(string $reference): ?Booking
    {
        return Booking::with(['event', 'items.ticketTier', 'promoCode'])
            ->where('reference', strtoupper($reference))
            ->first();
    }

    private function guestEmailForReservation(Reservation $reservation): string
    {
        $token = strtolower((string) $reservation->token);
        $token = preg_replace('/[^a-z0-9]/', '', $token) ?: (string) $reservation->id;
        return 'guest+' . $token . '@ticketly.invalid';
    }

    private function isGuestEmail(string $email): bool
    {
        return str_ends_with(strtolower($email), '@ticketly.invalid');
    }
}
