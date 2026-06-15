<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Organiser\AuthController as OrganiserAuthController;
use App\Models\Booking;
use App\Services\TicketQrCodeService;
use App\Services\TicketValidationService;
use App\Support\AdminAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScanValidationController extends Controller
{
    public function __construct(
        private readonly TicketValidationService $ticketValidationService,
        private readonly TicketQrCodeService $ticketQrCodeService
    ) {}

    public function scanTicket(Request $request): JsonResponse
    {
        $organiser = OrganiserAuthController::getAuthenticatedOrganiser();
        $admin = AdminAuth::user();

        if (!$organiser && !$admin) {
            return response()->json([
                'status' => 'error',
                'type' => 'red',
                'code' => 'unauthenticated',
                'message' => 'Only organisers or admins can validate tickets.',
            ], 401);
        }

        $data = $request->validate([
            'qr_code' => ['nullable', 'string'],
            'ticket_uuid' => ['nullable', 'string'],
            'booking_reference' => ['nullable', 'string'],
        ]);

        $identifiers = $this->resolveIdentifiers($data);

        if (!$identifiers) {
            return response()->json([
                'status' => 'error',
                'type' => 'red',
                'code' => 'invalid_ticket',
                'message' => 'Invalid Ticket',
            ], 422);
        }

        $booking = $this->findBooking($identifiers);

        if (!$booking || !$booking->event) {
            return response()->json([
                'status' => 'error',
                'type' => 'red',
                'code' => 'invalid_ticket',
                'message' => 'Invalid Ticket',
            ], 404);
        }

        $result = $this->ticketValidationService->attempt(
            $booking->id,
            organiserId: $organiser?->id,
            allowAdmin: $admin !== null
        );

        $payload = [
            'status' => $result['status'],
            'type' => $result['type'],
            'code' => $result['code'],
            'message' => $result['message'],
        ];

        if (($result['include_base_payload'] ?? false) === true && $result['booking'] instanceof Booking) {
            $payload += $this->bookingPayload($result['booking'], $result['scanned_at']);
        }

        return response()->json($payload, $result['status_code']);
    }

    private function bookingPayload(Booking $booking, mixed $scannedAt): array
    {
        $booking->loadMissing('event');
        $event = $booking->event;
        $validationStartsAt = $event?->ticketValidationStartsAt();
        $validationEndsAt = $event?->ticketValidationEndsAt();

        return [
            'ticket_uuid' => $booking->ticket_uuid,
            'booking_reference' => $booking->reference,
            'event_id' => $event?->id,
            'event_title' => $event?->title,
            'event_url' => $event ? route('events.show', $event->slug) : null,
            'validation_starts_at' => $validationStartsAt?->toIso8601String(),
            'validation_ends_at' => $validationEndsAt?->toIso8601String(),
            'scanned_at' => $scannedAt?->toIso8601String(),
        ];
    }

    private function findBooking(array $identifiers): ?Booking
    {
        $query = Booking::with('event');

        if (!empty($identifiers['ticket_uuid'])) {
            return $query->where('ticket_uuid', $identifiers['ticket_uuid'])->first();
        }

        if (!empty($identifiers['booking_reference'])) {
            return $query->where('reference', strtoupper(trim($identifiers['booking_reference'])))->first();
        }

        return null;
    }

    private function resolveIdentifiers(array $data): ?array
    {
        $ticketUuid = trim((string) ($data['ticket_uuid'] ?? ''));
        if ($ticketUuid !== '') {
            return ['ticket_uuid' => $ticketUuid];
        }

        $bookingReference = trim((string) ($data['booking_reference'] ?? ''));
        if ($bookingReference !== '') {
            return ['booking_reference' => strtoupper($bookingReference)];
        }

        $qrCode = trim((string) ($data['qr_code'] ?? ''));
        if ($qrCode === '') {
            return null;
        }

        $decodedPayload = $this->identifiersFromDecodedPayload(
            $this->ticketQrCodeService->decodePayload($qrCode)
        );
        if ($decodedPayload) {
            return $decodedPayload;
        }

        $parsedUrl = $this->parseScanUrl($qrCode);
        if ($parsedUrl) {
            return $parsedUrl;
        }

        if (preg_match('/^[0-9a-fA-F-]{36}$/', $qrCode) === 1) {
            return ['ticket_uuid' => $qrCode];
        }

        if (preg_match('/(TKT-[A-Z0-9]+)/i', $qrCode, $matches) === 1) {
            return ['booking_reference' => strtoupper($matches[1])];
        }

        if (preg_match('/BOOKING\s*REFERENCE\s*[:#]?\s*([A-Z0-9-]+)/i', $qrCode, $matches) === 1) {
            return ['booking_reference' => strtoupper($matches[1])];
        }

        return null;
    }

    private function parseScanUrl(string $rawValue): ?array
    {
        if (!filter_var($rawValue, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($rawValue);
        if (!is_array($parts)) {
            return null;
        }

        $query = [];
        parse_str($parts['query'] ?? '', $query);

        $decodedPayload = $this->identifiersFromDecodedPayload(
            $this->ticketQrCodeService->decodePayload((string) ($query['data'] ?? ''))
        );
        if ($decodedPayload) {
            return $decodedPayload;
        }

        $ticketUuid = trim((string) ($query['ticket_uuid'] ?? ''));
        if ($ticketUuid !== '') {
            return ['ticket_uuid' => $ticketUuid];
        }

        $bookingReference = trim((string) ($query['booking_reference'] ?? $query['reference'] ?? ''));
        if ($bookingReference !== '') {
            return ['booking_reference' => strtoupper($bookingReference)];
        }

        if (!empty($parts['path']) && preg_match('#/bookings/([A-Z0-9-]+)#i', $parts['path'], $matches) === 1) {
            return ['booking_reference' => strtoupper($matches[1])];
        }

        return null;
    }

    private function identifiersFromDecodedPayload(?array $payload): ?array
    {
        if (!is_array($payload) || $payload === []) {
            return null;
        }

        $ticketUuid = trim((string) ($payload['ticket_uuid'] ?? ''));
        if ($ticketUuid !== '') {
            return ['ticket_uuid' => $ticketUuid];
        }

        $bookingReference = trim((string) ($payload['booking_reference'] ?? ''));
        if ($bookingReference !== '') {
            return ['booking_reference' => strtoupper($bookingReference)];
        }

        return null;
    }
}
