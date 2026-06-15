<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\TicketTier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * TicketReservationRepository
 *
 * ALL ticket hold and release logic lives here.
 * Uses SELECT FOR UPDATE to prevent overselling under concurrent load.
 *
 * Horizontal scaling: Works correctly across multiple app servers
 * because locking happens at the database level, not in PHP memory.
 *
 * This is the ONLY place that decrements / increments available_quantity.
 */
class TicketReservationRepository
{
    private int $holdMinutes;

    public function __construct()
    {
        $this->holdMinutes = (int) config('ticketly.hold_minutes', 10);
    }

    /**
     * Create a reservation with locked ticket hold.
     *
     * @param  int    $eventId
     * @param  string $sessionId
     * @param  array  $items  [['ticket_tier_id' => int, 'quantity' => int], ...]
     * @return Reservation
     * @throws \RuntimeException if insufficient availability
     */
    public function hold(int $eventId, string $sessionId, array $items): Reservation
    {
        return DB::transaction(function () use ($eventId, $sessionId, $items) {
            $reservationItems = [];
            $subtotal         = 0.0;

            foreach ($items as $item) {
                $tierId   = (int) $item['ticket_tier_id'];
                $quantity = (int) $item['quantity'];

                // Lock the tier row for this transaction
                $tier = TicketTier::where('id', $tierId)
                    ->where('event_id', $eventId)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$tier) {
                    throw new \RuntimeException("Ticket tier {$tierId} not found or inactive.");
                }

                if ($tier->available_quantity < $quantity) {
                    throw new \RuntimeException(
                        "Sorry, only {$tier->available_quantity} ticket(s) available for '{$tier->name}'."
                    );
                }

                if ($quantity < $tier->min_per_order || $quantity > $tier->max_per_order) {
                    throw new \RuntimeException(
                        "'{$tier->name}' requires between {$tier->min_per_order} and {$tier->max_per_order} tickets per order."
                    );
                }

                // Decrement availability atomically
                $tier->decrement('available_quantity', $quantity);

                $lineSubtotal        = round((float) $tier->price * $quantity, 2);
                $subtotal           += $lineSubtotal;
                $reservationItems[]  = [
                    'ticket_tier_id' => $tierId,
                    'quantity'       => $quantity,
                    'unit_price'     => (float) $tier->price,
                    'subtotal'       => $lineSubtotal,
                ];
            }

            

            // Create the reservation
            $reservation = Reservation::create([
                'token'      => (string) Str::uuid(),
                'event_id'   => $eventId,
                'session_id' => $sessionId,
                'subtotal'   => $subtotal,
                'expires_at' => now()->addMinutes($this->holdMinutes),
                'status'     => 'pending',
            ]);

            // Create line items
            foreach ($reservationItems as $item) {
                ReservationItem::create(array_merge($item, ['reservation_id' => $reservation->id]));
            }

            Log::info('[TicketReservationRepository] Hold created', [
                'reservation' => $reservation->token,
                'event'       => $eventId,
                'items'       => count($reservationItems),
            ]);

            return $reservation->load('items.ticketTier');
        });
    }

    /**
     * Update an existing active reservation without releasing the holder's current inventory first.
     */
    public function updateHold(Reservation $reservation, array $items): Reservation
    {
        return DB::transaction(function () use ($reservation, $items) {
            $reservation = Reservation::whereKey($reservation->id)
                ->lockForUpdate()
                ->with('items')
                ->firstOrFail();

            if ($reservation->status !== 'pending' || $reservation->isExpired()) {
                throw new \RuntimeException('Your ticket hold is no longer active. Please select tickets again.');
            }

            $requested = collect($items)
                ->groupBy(fn ($item) => (int) $item['ticket_tier_id'])
                ->map(fn ($group) => (int) collect($group)->sum(fn ($item) => (int) $item['quantity']));

            $currentItems = $reservation->items->keyBy('ticket_tier_id');
            $tierIds = $currentItems->keys()->merge($requested->keys())->unique()->values();

            $tiers = TicketTier::whereIn('id', $tierIds)
                ->where('event_id', $reservation->event_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($tierIds as $tierId) {
                $currentQty = (int) optional($currentItems->get($tierId))->quantity;
                $newQty = (int) ($requested->get($tierId, 0));

                if ($newQty === 0) {
                    continue;
                }

                $tier = $tiers->get($tierId);
                if (!$tier) {
                    throw new \RuntimeException("Ticket tier {$tierId} not found or inactive.");
                }

                if ($newQty < $tier->min_per_order || $newQty > $tier->max_per_order) {
                    throw new \RuntimeException(
                        "'{$tier->name}' requires between {$tier->min_per_order} and {$tier->max_per_order} tickets per order."
                    );
                }

                $delta = $newQty - $currentQty;
                if ($delta > 0 && $tier->available_quantity < $delta) {
                    throw new \RuntimeException(
                        "Sorry, only {$tier->available_quantity} additional ticket(s) available for '{$tier->name}'."
                    );
                }
            }

            $subtotal = 0.0;

            foreach ($tierIds as $tierId) {
                $currentItem = $currentItems->get($tierId);
                $currentQty = (int) optional($currentItem)->quantity;
                $newQty = (int) ($requested->get($tierId, 0));
                $delta = $newQty - $currentQty;
                $tier = $tiers->get($tierId);

                if ($delta > 0 && $tier) {
                    $tier->decrement('available_quantity', $delta);
                } elseif ($delta < 0 && $tier) {
                    $tier->increment('available_quantity', abs($delta));
                }

                if ($newQty <= 0) {
                    $currentItem?->delete();
                    continue;
                }

                $lineSubtotal = round((float) $tier->price * $newQty, 2);
                $subtotal += $lineSubtotal;

                if ($currentItem) {
                    $currentItem->update([
                        'quantity' => $newQty,
                        'unit_price' => (float) $tier->price,
                        'subtotal' => $lineSubtotal,
                    ]);
                } else {
                    ReservationItem::create([
                        'reservation_id' => $reservation->id,
                        'ticket_tier_id' => $tierId,
                        'quantity' => $newQty,
                        'unit_price' => (float) $tier->price,
                        'subtotal' => $lineSubtotal,
                    ]);
                }
            }

            $reservation->update([
                'subtotal' => $subtotal,
            ]);

            Log::info('[TicketReservationRepository] Hold updated', [
                'reservation' => $reservation->token,
                'event' => $reservation->event_id,
                'items' => $requested->filter(fn ($qty) => $qty > 0)->count(),
            ]);

            return $reservation->fresh('items.ticketTier');
        });
    }

    /**
     * Release a pending reservation and restore inventory.
     */
    public function release(Reservation $reservation): bool
    {
        if (!in_array($reservation->status, ['pending', 'expired'])) {
            return false; // Already released or completed
        }

        return DB::transaction(function () use ($reservation) {
            $reservation->loadMissing('items');

            foreach ($reservation->items as $item) {
                TicketTier::where('id', $item->ticket_tier_id)
                    ->lockForUpdate()
                    ->increment('available_quantity', $item->quantity);
            }

            $reservation->update(['status' => 'released']);

            Log::info('[TicketReservationRepository] Reservation released', [
                'reservation' => $reservation->token,
            ]);

            return true;
        });
    }

    /**
     * Expire all reservations whose timer has lapsed.
     * Called by the ExpireReservations console command (every minute).
     */
    public function expireStale(): int
    {
        $expired = Reservation::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expired as $reservation) {
            $released = $this->release($reservation);
            if ($released) {
                $reservation->update(['status' => 'expired']);
                $count++;
            }
        }

        if ($count > 0) {
            Log::info("[TicketReservationRepository] Expired {$count} stale reservation(s).");
        }

        return $count;
    }
}
