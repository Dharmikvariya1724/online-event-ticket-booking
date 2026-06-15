<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;

class TicketQrCodeService
{
    public function payloadForBooking(Booking $booking): string
    {
        return $this->publicEventUrlForBooking($booking);
    }

    public function encodedPayloadForBooking(Booking $booking): string
    {
        $booking->loadMissing('event');

        return $this->encodePayload([
            'version' => 1,
            'booking_id' => $booking->id,
            'event_id' => $booking->event_id,
            'ticket_uuid' => $booking->ticket_uuid,
            'booking_reference' => $booking->reference,
            'event_url' => route('events.show', $booking->event->slug),
        ]);
    }

    public function publicEventUrlForBooking(Booking $booking): string
    {
        $booking->loadMissing('event');

        return route('events.show', [
            'slug' => $booking->event->slug,
            'ticket_uuid' => $booking->ticket_uuid,
            'booking_reference' => $booking->reference,
        ]);
    }

    public function sourceForBooking(Booking $booking, int $size = 220): string
    {
        $payload = $this->payloadForBooking($booking);

        return $this->dataUriForPayload($payload, $size)
            ?? $this->imageUrlForPayload($payload, $size);
    }

    public function imageUrlForPayload(string $payload, int $size = 220): string
    {
        $dimension = max(50, $size) . 'x' . max(50, $size);

        return 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
            'size' => $dimension,
            'data' => $payload,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function dataUriForPayload(string $payload, int $size = 220): ?string
    {
        try {
            $response = Http::accept('image/png')
                ->timeout(10)
                ->retry(2, 200)
                ->get($this->imageUrlForPayload($payload, $size));
        } catch (\Throwable) {
            return null;
        }

        $body = $response->body();

        if (!$response->successful() || $body === '') {
            return null;
        }

        $contentType = trim((string) $response->header('Content-Type', 'image/png'));
        if (str_contains($contentType, ';')) {
            $contentType = strtok($contentType, ';') ?: 'image/png';
        }

        return 'data:' . ($contentType ?: 'image/png') . ';base64,' . base64_encode($body);
    }

    public function encodePayload(array $payload): string
    {
        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException) {
            return '';
        }

        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    public function decodePayload(string $payload): ?array
    {
        $value = trim($payload);
        if ($value === '') {
            return null;
        }

        $normalized = strtr($value, '-_', '+/');
        $padding = strlen($normalized) % 4;
        if ($padding !== 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($normalized, true);
        if ($decoded === false) {
            return null;
        }

        $data = json_decode($decoded, true);

        return is_array($data) ? $data : null;
    }
}
