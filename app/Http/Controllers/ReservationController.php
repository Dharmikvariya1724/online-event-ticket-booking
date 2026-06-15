<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Repositories\TicketReservationRepository;
use App\Services\ServiceFeeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    public function __construct(private readonly TicketReservationRepository $repo)
    {}

    /**
     * Create a ticket hold.
     * Route: POST /reserve
     */
    public function store(Request $request)
    {
        // The form posts all tiers with default quantity 0.
        // Keep only selected tiers so validation doesn't fail on zero-quantity rows.
        $selectedItems = collect($request->input('items', []))
            ->filter(function ($item) {
                return (int) data_get($item, 'quantity', 0) > 0;
            })
            ->values()
            ->all();

        $request->merge(['items' => $selectedItems]);

        $validated = $request->validate([
            'event_id'            => 'required|integer|exists:events,id',
            'items'               => 'required|array|min:1|max:20',
            'items.*.ticket_tier_id' => 'required|integer|exists:ticket_tiers,id',
            'items.*.quantity'    => 'required|integer|min:1|max:20',
            'replace_reservation_token' => 'nullable|string',
        ]);

        try {
            $reservation = null;
            $replaceToken = $validated['replace_reservation_token'] ?? null;

            if ($replaceToken) {
                $existingReservation = Reservation::where('token', $replaceToken)
                    ->where('session_id', session()->getId())
                    ->where('event_id', (int) $validated['event_id'])
                    ->first();

                if ($existingReservation && $existingReservation->status === 'pending' && !$existingReservation->isExpired()) {
                    $reservation = $this->repo->updateHold($existingReservation, $validated['items']);
                }
            }

            if (!$reservation) {
                $reservation = $this->repo->hold(
                    (int) $validated['event_id'],
                    session()->getId(),
                    $validated['items']
                );
            }

            // Pre-calculate pricing for checkout display
            $pricing = ServiceFeeCalculator::total((float) $reservation->subtotal);
            $reservation->update([
                'portal_fee'  => $pricing['portal_fee'],
                'service_fee' => $pricing['service_fee'],
                'total'       => $pricing['total'],
            ]);

            return redirect()->route('checkout.show', $reservation->token);

        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->withErrors(['availability' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('[ReservationController] Unexpected error: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withErrors(['availability' => 'An error occurred. Please try again.']);
        }
    }

    /**
     * Release a reservation.
     * Route: DELETE /reserve/{token}
     */
    public function release(Request $request, string $token)
    {
        $reservation = \App\Models\Reservation::where('token', $token)
            ->where('session_id', session()->getId())
            ->firstOrFail();

        $this->repo->release($reservation);

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json(['released' => true]);
        }

        return redirect()
            ->route('events.show', $reservation->event->slug)
            ->with('info', 'Your ticket hold has been released.');
    }

    /**
     * Used by CheckoutController::releaseReservation() (backward compat).
     */
    public static function releaseReservation(\App\Models\Reservation $reservation): void
    {
        app(TicketReservationRepository::class)->release($reservation);
    }
}
