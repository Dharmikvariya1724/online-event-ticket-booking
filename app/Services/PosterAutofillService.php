<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PosterAutofillService
{
    private const DEFAULT_CITY = 'Surat';
    private const DEFAULT_POSTCODE = '395007';

    private const OUTPUT_KEYS = [
        'event_title',
        'short_description',
        'full_description',
        'start_datetime',
        'end_datetime',
        'venue_name',
        'city',
        'address',
        'postcode',
    ];

    private const CITY_POSTCODE_MAP = [
        'ahmedabad' => '380001',
        'bangalore' => '560001',
        'bengaluru' => '560001',
        'delhi' => '110001',
        'kolkata' => '700001',
        'london' => 'EC1A 1BB',
        'manchester' => 'M1 1AE',
        'mumbai' => '400001',
        'new delhi' => '110001',
        'pune' => '411001',
        'surat' => '395007',
    ];

    public function __construct(
        private readonly PosterAIService $posterAIService
    ) {}

    public function extractDetails(UploadedFile $poster): array
    {
        $fallback = $this->buildFallbackPayload($poster);

        try {
            $candidate = $this->fetchPosterDetails($poster);

            return $this->normalizePayload($candidate ?? [], $fallback);
        } catch (\Throwable $exception) {
            Log::warning('[PosterAutofillService] Poster parsing failed, falling back to generated values.', [
                'message' => $exception->getMessage(),
                'poster_name' => $poster->getClientOriginalName(),
            ]);

            return $fallback;
        }
    }

    private function fetchPosterDetails(UploadedFile $poster): ?array
    {
        return $this->posterAIService->scanPoster($this->buildPosterDataUri($poster));
    }

    private function buildPosterDataUri(UploadedFile $poster): string
    {
        $mimeType = $poster->getMimeType() ?: 'image/jpeg';
        $contents = base64_encode((string) file_get_contents($poster->getRealPath()));

        return "data:{$mimeType};base64,{$contents}";
    }

    private function normalizePayload(array $candidate, array $fallback): array
    {
        $fallbackStartAt = Carbon::createFromFormat('Y-m-d\TH:i', $fallback['start_datetime']);
        $fallbackEndAt = Carbon::createFromFormat('Y-m-d\TH:i', $fallback['end_datetime']);

        $candidateStart = $this->firstFilled($candidate, ['start_datetime', 'start_date_time', 'start_date', 'date_time']);
        $candidateEnd = $this->firstFilled($candidate, ['end_datetime', 'end_date_time', 'end_date']);

        $title = $this->cleanText(
            $this->firstFilled($candidate, ['event_title', 'title', 'name']) ?: $fallback['event_title'],
            50
        );

        $startAt = $this->normalizeStartDateTime(
            $candidateStart,
            $candidateEnd,
            $fallbackStartAt
        );

        $endAt = $this->normalizeEndDateTime(
            $candidateEnd,
            $startAt,
            $fallbackEndAt
        );

        $venueName = $this->cleanText(
            $this->firstFilled($candidate, ['venue_name', 'venue', 'location_name']),
            50
        );
        $city = $this->cleanText(
            $this->firstFilled($candidate, ['city', 'town', 'location_city']),
            50
        );
        $address = $this->cleanText(
            $this->firstFilled($candidate, ['address', 'venue_address', 'location', 'street_address']),
            300
        );
        $postcode = $this->cleanPostcode(
            $this->firstFilled($candidate, ['postcode', 'postal_code', 'zip_code'])
        );

        if ($address === '' && $venueName !== '' && $city !== '') {
            $address = $this->cleanText("{$venueName}, {$city}", 300);
        }

        if ($venueName === '' && $address !== '') {
            $venueName = $this->extractVenueFromAddress($address);
        }

        if ($city === '' && $address !== '') {
            $city = $this->extractCityFromAddress($address);
        }

        if ($postcode === '' && $address !== '') {
            $postcode = $this->extractPostcodeFromText($address);
        }

        $venueName = $venueName !== '' ? $venueName : $fallback['venue_name'];
        $city = $city !== '' ? $city : $fallback['city'];
        $address = $address !== '' ? $address : $this->cleanText("{$venueName}, {$city}", 300);
        $postcode = $postcode !== '' ? $postcode : $this->inferPostcodeFromCity($city, $fallback['postcode']);

        $shortDescription = $this->cleanText(
            $this->firstFilled($candidate, ['short_description', 'summary', 'short_summary'])
                ?: $this->buildShortDescription($title, $city, $venueName),
            255
        );

        $fullDescription = $this->cleanText(
            $this->firstFilled($candidate, ['full_description', 'description', 'details', 'long_description'])
                ?: $this->buildFullDescription($title, $city, $venueName, $startAt),
            5000
        );

        $category = $this->cleanText(
            $this->firstFilled($candidate, ['category', 'event_category']),
            50
        );

        $parkingTransportInfo = $this->cleanText(
            $this->firstFilled($candidate, ['parking_transport_info', 'parking_info', 'parking_transport', 'parking', 'transport_info', 'transport']),
            1000
        );

        $refundPolicy = $this->cleanText(
            $this->firstFilled($candidate, ['refund_policy', 'refunds', 'cancellation_policy', 'cancellation', 'refund']),
            1000
        );

        $payload = $this->ensureFilledPayload([
            'event_title' => $title !== '' ? $title : $fallback['event_title'],
            'short_description' => $shortDescription !== '' ? $shortDescription : $fallback['short_description'],
            'full_description' => $fullDescription !== '' ? $fullDescription : $fallback['full_description'],
            'start_datetime' => $startAt->format('Y-m-d\TH:i'),
            'end_datetime' => $endAt->format('Y-m-d\TH:i'),
            'venue_name' => $venueName,
            'city' => $city,
            'address' => $address,
            'postcode' => $postcode,
        ], $fallback);

        $payload['category'] = $category !== '' ? $category : null;
        $payload['parking_transport_info'] = $parkingTransportInfo !== '' ? $parkingTransportInfo : null;
        $payload['refund_policy'] = $refundPolicy !== '' ? $refundPolicy : null;

        return $payload;
    }

    private function buildFallbackPayload(UploadedFile $poster): array
    {
        $title = $this->deriveTitleFromFilename($poster->getClientOriginalName());
        $locationDefaults = $this->defaultLocationForTitle($title);
        $startAt = $this->defaultStartDateTime();
        $endAt = $startAt->copy()->addHours(2);

        return [
            'event_title' => $title,
            'short_description' => $this->buildShortDescription($title, $locationDefaults['city'], $locationDefaults['venue_name']),
            'full_description' => $this->buildFullDescription($title, $locationDefaults['city'], $locationDefaults['venue_name'], $startAt),
            'start_datetime' => $startAt->format('Y-m-d\TH:i'),
            'end_datetime' => $endAt->format('Y-m-d\TH:i'),
            'venue_name' => $locationDefaults['venue_name'],
            'city' => $locationDefaults['city'],
            'address' => $locationDefaults['address'],
            'postcode' => $locationDefaults['postcode'],
        ];
    }

    private function deriveTitleFromFilename(string $filename): string
    {
        $baseName = pathinfo($filename, PATHINFO_FILENAME);

        $cleaned = Str::of($baseName)
            ->replaceMatches('/[_-]+/', ' ')
            ->replaceMatches('/\b\d{2,}\b/', ' ')
            ->squish()
            ->trim()
            ->toString();

        if ($cleaned === '' || preg_match('/^(image|photo|poster|banner|img)$/i', $cleaned) === 1) {
            return 'Live Event Night';
        }

        return $this->cleanText(Str::title($cleaned), 50);
    }

    private function defaultLocationForTitle(string $title): array
    {
        $title = Str::lower($title);

        if (Str::contains($title, ['garba', 'kirtan', 'bhajan', 'navratri', 'satsang', 'bollywood'])) {
            return [
                'venue_name' => 'Satsang Hall',
                'city' => self::DEFAULT_CITY,
                'address' => 'Satsang Hall, Adajan Main Road, Surat',
                'postcode' => self::DEFAULT_POSTCODE,
            ];
        }

        if (Str::contains($title, ['summit', 'conference', 'expo', 'business', 'tech'])) {
            return [
                'venue_name' => 'Convention Centre',
                'city' => self::DEFAULT_CITY,
                'address' => 'Convention Centre, Vesu Main Road, Surat',
                'postcode' => self::DEFAULT_POSTCODE,
            ];
        }

        return [
            'venue_name' => 'Main Hall',
            'city' => self::DEFAULT_CITY,
            'address' => 'Main Hall, City Light Road, Surat',
            'postcode' => self::DEFAULT_POSTCODE,
        ];
    }

    private function defaultStartDateTime(): Carbon
    {
        return $this->now()->copy()->addDays(14)->setTime(19, 0, 0);
    }

    private function normalizeStartDateTime(?string $value, ?string $endValue, Carbon $fallback): Carbon
    {
        $parsed = $this->parseDateTime($value);

        if ($parsed && $parsed->greaterThan($this->now())) {
            return $parsed;
        }

        $parsedEnd = $this->parseDateTime($endValue);
        if ($parsedEnd) {
            $generatedStart = $parsedEnd->copy()->subHours(2);
            if ($generatedStart->greaterThan($this->now())) {
                return $generatedStart;
            }
        }

        return $fallback;
    }

    private function normalizeEndDateTime(?string $value, Carbon $startAt, Carbon $fallback): Carbon
    {
        $parsed = $this->parseDateTime($value);

        if (!$parsed || $parsed->lessThanOrEqualTo($startAt)) {
            $parsed = $startAt->copy()->addHours(2);
        }

        if ($parsed->lessThanOrEqualTo($startAt)) {
            return $fallback->greaterThan($startAt) ? $fallback : $startAt->copy()->addHours(2);
        }

        return $parsed;
    }

    private function parseDateTime(?string $value): ?Carbon
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildShortDescription(string $title, string $city, string $venueName): string
    {
        return $this->cleanText("Join {$title} for a lively experience at {$venueName} in {$city}.", 255);
    }

    private function buildFullDescription(string $title, string $city, string $venueName, Carbon $startAt): string
    {
        $dateLabel = $startAt->format('j M Y');

        return $this->cleanText(
            "{$title} is a thoughtfully curated event experience taking place on {$dateLabel} at {$venueName} in {$city}. Expect a welcoming atmosphere, a strong audience turnout, and a polished program shaped around the poster theme so organisers can publish a complete, realistic listing right away.",
            5000
        );
    }

    private function cleanText(mixed $value, int $maxLength): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        $text = Str::of((string) $value)
            ->replace(["\r\n", "\r"], "\n")
            ->replace("\t", ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->trim('"')
            ->trim("'")
            ->toString();

        if ($text === '') {
            return '';
        }

        return Str::limit($text, $maxLength, '');
    }

    private function cleanPostcode(mixed $value): string
    {
        $postcode = Str::upper($this->cleanText($value, 10));

        return Str::limit($postcode, 10, '');
    }

    private function firstFilled(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $payload[$key] ?? null;

            if (is_scalar($value) && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    private function extractVenueFromAddress(string $address): string
    {
        $firstSegment = Str::of($address)->before(',')->trim()->toString();

        return $this->cleanText($firstSegment, 50);
    }

    private function extractCityFromAddress(string $address): string
    {
        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));

        if (count($parts) >= 2) {
            $city = $parts[count($parts) - 1];
            $postcode = $this->extractPostcodeFromText($city);
            if ($postcode !== '') {
                $city = trim(str_replace($postcode, '', $city));
            }

            if ($city !== '') {
                return $this->cleanText($city, 50);
            }

            return $this->cleanText($parts[count($parts) - 2], 50);
        }

        foreach (array_keys(self::CITY_POSTCODE_MAP) as $knownCity) {
            if (Str::contains(Str::lower($address), $knownCity)) {
                return $this->cleanText(Str::title($knownCity), 50);
            }
        }

        return '';
    }

    private function extractPostcodeFromText(string $text): string
    {
        if (preg_match('/\b[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}\b/i', $text, $matches) === 1) {
            return $this->cleanPostcode($matches[0]);
        }

        if (preg_match('/\b\d{6}\b/', $text, $matches) === 1) {
            return $this->cleanPostcode($matches[0]);
        }

        return '';
    }

    private function inferPostcodeFromCity(string $city, string $fallback): string
    {
        $cityKey = Str::lower(trim($city));

        return self::CITY_POSTCODE_MAP[$cityKey] ?? $fallback;
    }

    private function ensureFilledPayload(array $payload, array $fallback): array
    {
        $normalized = [];

        foreach (self::OUTPUT_KEYS as $key) {
            $value = isset($payload[$key]) ? trim((string) $payload[$key]) : '';
            if ($value === '') {
                $value = trim((string) ($fallback[$key] ?? ''));
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function now(): Carbon
    {
        return now();
    }
}
