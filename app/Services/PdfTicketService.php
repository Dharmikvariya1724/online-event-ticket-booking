<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfTicketService
{
    public function __construct(
        private readonly TicketQrCodeService $ticketQrCodeService
    ) {}

    public function generate(Booking $booking): string
    {
        $booking->loadMissing(['event', 'items.ticketTier', 'promoCode']);
        $qrImageSrc = $this->ticketQrCodeService->sourceForBooking($booking);

        return Pdf::loadView('emails.ticket-pdf', compact('booking', 'qrImageSrc'))
            ->setOption('isRemoteEnabled', true)
            ->setPaper('a4', 'portrait')
            ->output();
    }

    public function getMimeType(): string
    {
        return 'application/pdf';
    }

    public function getExtension(): string
    {
        return 'pdf';
    }
}
