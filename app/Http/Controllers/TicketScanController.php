<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\TicketQrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketScanController extends Controller
{
    public function show(Request $request, TicketQrCodeService $ticketQrCodeService): RedirectResponse
    {
        $booking = $this->findBooking($request, $ticketQrCodeService);

        abort_if(!$booking || !$booking->event, 404, 'Invalid ticket QR code.');

        return redirect()->route('events.show', $booking->event->slug);
    }

    private function findBooking(Request $request, TicketQrCodeService $ticketQrCodeService): ?Booking
    {
        $payload = $ticketQrCodeService->decodePayload((string) $request->query('data', '')) ?? [];

        $ticketUuid = trim((string) ($request->query('ticket_uuid') ?? $payload['ticket_uuid'] ?? ''));
        if ($ticketUuid !== '') {
            return Booking::with('event')
                ->where('ticket_uuid', $ticketUuid)
                ->first();
        }

        $bookingReference = trim((string) ($request->query('booking_reference') ?? $request->query('reference') ?? $payload['booking_reference'] ?? ''));
        if ($bookingReference === '') {
            return null;
        }

        return Booking::with('event')
            ->where('reference', strtoupper($bookingReference))
            ->first();
    }
}
