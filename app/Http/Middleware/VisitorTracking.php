<?php

namespace App\Http\Middleware;

use App\Jobs\EnrichVisitorLocation;
use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class VisitorTracking
{
    private const DEFAULT_METHODS = ['GET', 'HEAD'];

    private const DEFAULT_SKIP_PATHS = [
        'api/*',
        'webhooks/*',
        'storage/*',
        'build/*',
        'css/*',
        'js/*',
        'images/*',
        'fonts/*',
        'favicon.ico',
        'up',
    ];

    private const DEFAULT_SKIP_EXTENSIONS = [
        'css',
        'js',
        'map',
        'png',
        'jpg',
        'jpeg',
        'gif',
        'svg',
        'webp',
        'ico',
        'woff',
        'woff2',
        'ttf',
        'eot',
        'otf',
        'mp4',
        'mp3',
        'wav',
        'webm',
        'pdf',
        'zip',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        if ($this->shouldSkip($request)) {
            return;
        }

        static $tableReady = null;
        if ($tableReady === null) {
            $tableReady = Schema::hasTable('visitor_logs');
        }

        if (!$tableReady) {
            return;
        }

        try {
            $ip = $request->ip();
            if (!$ip) {
                return;
            }

            $route = $request->route();
            $location = $this->extractLocationFromHeaders($request);

            $log = VisitorLog::create([
                'ip_address' => $ip,
                'city' => $location['city'] ?? null,
                'region' => $location['region'] ?? null,
                'country' => $location['country'] ?? null,
                'country_code' => $location['country_code'] ?? null,
                'latitude' => $location['latitude'] ?? null,
                'longitude' => $location['longitude'] ?? null,
                'timezone' => $location['timezone'] ?? null,
                'method' => $request->method(),
                'host' => $request->getHost(),
                'path' => '/' . ltrim($request->path(), '/'),
                'full_url' => $request->fullUrl(),
                'query' => $request->getQueryString(),
                'route_name' => $route?->getName(),
                'route_uri' => $route?->uri(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->headers->get('referer'),
                'accept_language' => $request->headers->get('accept-language'),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'user_id' => auth()->id(),
                'organiser_id' => session('organiser_id'),
                'admin_id' => session('admin_id'),
                'is_secure' => $request->isSecure(),
                'response_status' => $response->getStatusCode(),
            ]);

            if ($this->shouldEnrich($location)) {
                EnrichVisitorLocation::dispatchAfterResponse($log->id, $ip);
            }
        } catch (\Throwable $e) {
            Log::warning('[VisitorTracking] Failed to log visitor', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function isEnabled(): bool
    {
        return (bool) config('visitor.enabled', true);
    }

    private function shouldSkip(Request $request): bool
    {
        $methods = config('visitor.capture_methods', self::DEFAULT_METHODS);
        if (!in_array($request->method(), $methods, true)) {
            return true;
        }

        $path = ltrim($request->path(), '/');
        foreach (config('visitor.skip_paths', self::DEFAULT_SKIP_PATHS) as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension) {
            $extension = strtolower($extension);
            $extensions = config('visitor.skip_extensions', self::DEFAULT_SKIP_EXTENSIONS);
            if (in_array($extension, $extensions, true)) {
                return true;
            }
        }

        return false;
    }

    private function shouldEnrich(array $location): bool
    {
        if (!config('visitor.geo.enabled', true)) {
            return false;
        }

        return empty($location['city']) || empty($location['region']) || empty($location['country']);
    }

    private function extractLocationFromHeaders(Request $request): array
    {
        $headers = $request->headers;

        $city = $headers->get('cf-ipcity')
            ?? $headers->get('x-geo-city')
            ?? $headers->get('x-appengine-city');

        $region = $headers->get('cf-region')
            ?? $headers->get('x-geo-region')
            ?? $headers->get('x-appengine-region');

        $country = $headers->get('x-geo-country-name');

        $countryCode = $headers->get('cf-ipcountry')
            ?? $headers->get('x-geo-country')
            ?? $headers->get('x-appengine-country');

        $lat = $headers->get('cf-latitude') ?? $headers->get('x-geo-lat');
        $lon = $headers->get('cf-longitude') ?? $headers->get('x-geo-lon');

        $timezone = $headers->get('cf-timezone')
            ?? $headers->get('x-geo-timezone');

        return [
            'city' => $city ?: null,
            'region' => $region ?: null,
            'country' => $country ?: null,
            'country_code' => $countryCode ?: null,
            'latitude' => is_numeric($lat) ? (float) $lat : null,
            'longitude' => is_numeric($lon) ? (float) $lon : null,
            'timezone' => $timezone ?: null,
        ];
    }
}
