<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PdfTicketService;
use App\Services\TicketQrCodeService;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    public function show(string $reference, TicketQrCodeService $ticketQrCodeService)
    {
        $booking = $this->findPaidBooking($reference);
        $qrPayload = $ticketQrCodeService->payloadForBooking($booking);
        $qrImageSrc = $ticketQrCodeService->imageUrlForPayload($qrPayload);

        return view('booking.show', compact('booking', 'qrImageSrc'));
    }

    public function ticketPdf(string $reference, PdfTicketService $pdf): Response
    {
        $booking  = $this->findPaidBooking($reference);
        $content  = $pdf->generate($booking);
        $filename = 'ticket-' . $booking->reference . '.pdf';

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function findPaidBooking(string $reference): Booking
    {
        return Booking::with([
            'event',
            'items.ticketTier',
            'promoCode',
        ])
            ->where('reference', strtoupper(trim($reference)))
            ->where('status', 'paid')
            ->firstOrFail();
    }
}
