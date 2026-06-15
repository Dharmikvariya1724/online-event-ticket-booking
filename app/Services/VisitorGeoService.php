<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VisitorGeoService
{
    public function lookup(string $ip): ?array
    {
        if (!$this->isPublicIp($ip)) {
            return null;
        }

        $ttl = (int) config('visitor.geo.cache_ttl', 604800);

        return Cache::remember("visitor-geo:{$ip}", $ttl, function () use ($ip) {
            $provider = config('visitor.geo.provider', 'ipapi');

            return match ($provider) {
                'ipinfo' => $this->lookupIpInfo($ip),
                default => $this->lookupIpApi($ip),
            };
        });
    }

    private function lookupIpApi(string $ip): ?array
    {
        $template = config('visitor.geo.ipapi_url', 'http://ip-api.com/json/{ip}?fields=status,message,country,countryCode,regionName,city,lat,lon,timezone,query');
        $url = str_replace('{ip}', $ip, $template);

        $response = Http::timeout((int) config('visitor.geo.timeout', 2))
            ->retry(1, 100)
            ->get($url);

        if (!$response->ok()) {
            return null;
        }

        $data = $response->json();

        if (!is_array($data) || ($data['status'] ?? null) !== 'success') {
            return null;
        }

        return [
            'city' => $data['city'] ?? null,
            'region' => $data['regionName'] ?? null,
            'country' => $data['country'] ?? null,
            'country_code' => $data['countryCode'] ?? null,
            'latitude' => isset($data['lat']) ? (float) $data['lat'] : null,
            'longitude' => isset($data['lon']) ? (float) $data['lon'] : null,
            'timezone' => $data['timezone'] ?? null,
        ];
    }

    private function lookupIpInfo(string $ip): ?array
    {
        $token = (string) config('visitor.geo.ipinfo_token', '');
        if ($token === '') {
            return null;
        }

        $url = "https://ipinfo.io/{$ip}/json?token={$token}";

        $response = Http::timeout((int) config('visitor.geo.timeout', 2))
            ->retry(1, 100)
            ->get($url);

        if (!$response->ok()) {
            return null;
        }

        $data = $response->json();
        if (!is_array($data)) {
            return null;
        }

        $lat = null;
        $lon = null;
        if (!empty($data['loc']) && is_string($data['loc']) && str_contains($data['loc'], ',')) {
            [$lat, $lon] = array_map('trim', explode(',', $data['loc'], 2));
            $lat = is_numeric($lat) ? (float) $lat : null;
            $lon = is_numeric($lon) ? (float) $lon : null;
        }

        return [
            'city' => $data['city'] ?? null,
            'region' => $data['region'] ?? null,
            'country' => $data['country'] ?? null,
            'country_code' => $data['country'] ?? null,
            'latitude' => $lat,
            'longitude' => $lon,
            'timezone' => $data['timezone'] ?? null,
        ];
    }

    private function isPublicIp(string $ip): bool
    {
        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
