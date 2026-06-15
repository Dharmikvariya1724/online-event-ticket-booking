<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PerformanceHeaders
 *
 * Adds:
 * - Security headers (PCI-DSS, OWASP)
 * - Cache-Control for static vs dynamic pages
 * - Response time header (for monitoring)
 * - HSTS (HTTPS enforcement)
 * - X-Frame-Options (clickjacking protection)
 * - CSP that allows Stripe.js (required for PCI-DSS SAQ A)
 */
class PerformanceHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $start    = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $start) * 1000, 2);

        // ── Response time tracking ──────────────────────────────────
        $response->headers->set('X-Response-Time', $duration . 'ms');

        // ── Security headers ────────────────────────────────────────
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // ── HSTS (HTTPS only) ────────────────────────────────────────
        if ($request->isSecure() || app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // ── Content Security Policy ─────────────────────────────────
        // Allows Stripe.js (required for PCI-DSS SAQ A compliance)
        // No inline scripts other than those needed by Stripe Payment Element
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://js.stripe.com https://cdn.tailwindcss.com https://cdn.ckeditor.com https://cdn.jsdelivr.net 'unsafe-inline'",
            "style-src 'self' https://fonts.googleapis.com https://cdn.tailwindcss.com 'unsafe-inline'",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data: https:",
            "connect-src 'self' https://api.stripe.com",
            "frame-src https://js.stripe.com https://hooks.stripe.com",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // ── Cache control ────────────────────────────────────────────
        $noCacheRoutes = ['/checkout', '/admin', '/organiser/dashboard', '/bookings'];
        $isNoCacheRoute = collect($noCacheRoutes)->contains(
            fn($p) => str_starts_with($request->getPathInfo(), $p)
        );

        if ($isNoCacheRoute || $request->method() !== 'GET') {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        } else {
            $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=30');
        }

        return $response;
    }
}