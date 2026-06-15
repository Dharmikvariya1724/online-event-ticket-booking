<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TicketValidationService
{
    public function attempt(int $bookingId, ?int $organiserId = null, bool $allowAdmin = false): array
    {
        return DB::transaction(function () use ($bookingId, $organiserId, $allowAdmin) {
            $booking = Booking::with(['event', 'items'])->lockForUpdate()->find($bookingId);

            if (!$booking || !$booking->event) {
                return $this->result(
                    status: 'error',
                    type: 'red',
                    code: 'invalid_ticket',
                    message: 'Invalid Ticket',
                    statusCode: 404,
                    booking: null
                );
            }

            $event = $booking->event;

            if (!$allowAdmin && $organiserId !== null && (int) $event->organiser_id !== (int) $organiserId) {
                return $this->result(
                    status: 'error',
                    type: 'red',
                    code: 'invalid_ticket',
                    message: 'Invalid Organizer Ticket',
                    statusCode: 403,
                    booking: $booking,
                    exposeBooking: false
                );
            }

            if (in_array($booking->status, ['cancelled', 'refunded', 'partially_refunded'], true) || $booking->isRefunded()) {
                return $this->result(
                    status: 'error',
                    type: 'red',
                    code: 'cancelled_or_refunded',
                    message: 'Ticket Not Valid',
                    statusCode: 422,
                    booking: $booking
                );
            }

            $validationStartsAt = $event->ticketValidationStartsAt();
            $validationEndsAt = $event->ticketValidationEndsAt();
            $now = now();

            if ($validationStartsAt && $now->lt($validationStartsAt)) {
                return $this->result(
                    status: 'error',
                    type: 'red',
                    code: 'entry_not_started',
                    message: 'Ticket Scanned Too Early',
                    statusCode: 422,
                    booking: $booking
                );
            }

            if ($validationEndsAt && $now->gt($validationEndsAt)) {
                return $this->result(
                    status: 'error',
                    type: 'red',
                    code: 'entry_closed',
                    message: 'Ticket Expired',
                    statusCode: 422,
                    booking: $booking
                );
            }

            if (!$event->isPublished() || !$event->isApproved() || $event->isCancelled()) {
                return $this->result(
                    status: 'error',
                    type: 'red',
                    code: 'invalid_ticket',
                    message: 'Invalid Ticket',
                    statusCode: 422,
                    booking: $booking
                );
            }

            if ($booking->isUsed()) {
                return $this->result(
                    status: 'error',
                    type: 'orange',
                    code: 'already_used',
                    message: 'Ticket Already Used',
                    statusCode: 409,
                    booking: $booking,
                    scannedAt: $booking->scanned_at
                );
            }

            $scannedAt = Carbon::now();
            $booking->forceFill([
                'is_used' => true,
                'scanned_at' => $scannedAt,
                'scanned_quantity' => max(1, $booking->ticketQuantity()),
            ])->save();

            return $this->result(
                status: 'success',
                type: 'green',
                code: 'verified',
                message: 'Ticket Verified',
                statusCode: 200,
                booking: $booking,
                scannedAt: $scannedAt
            );
        });
    }

    private function result(
        string $status,
        string $type,
        string $code,
        string $message,
        int $statusCode,
        ?Booking $booking,
        ?Carbon $scannedAt = null,
        bool $exposeBooking = true
    ): array {
        return [
            'status' => $status,
            'type' => $type,
            'code' => $code,
            'message' => $message,
            'status_code' => $statusCode,
            'booking' => $exposeBooking ? $booking : null,
            'scanned_at' => $scannedAt,
            'include_base_payload' => $exposeBooking && $booking !== null,
        ];
    }
}
