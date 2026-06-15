<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SessionActivity
 *
 * Tracks organiser session inactivity.
 * If organiser has been idle > INACTIVITY_MINUTES,
 * their session is invalidated and they are redirected to login.
 *
 * This middleware is applied globally to all web routes.
 * The organiser session check is skipped for public/auth routes.
 */
class SessionActivity
{
    private const INACTIVITY_MINUTES = 60;

    // Routes that should NOT trigger the inactivity check
    private const EXEMPT_PREFIXES = [
        '/organiser/login',
        '/organiser/register',
        '/organiser/pending',
        '/organiser/forgot',
        '/organiser/reset',
        '/webhooks',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        $isExempt = collect(self::EXEMPT_PREFIXES)
            ->contains(fn($p) => str_starts_with($path, $p));

        if (!$isExempt && session()->has('organiser_id')) {
            $lastActive = session('organiser_last_active', 0);
            $elapsed    = now()->timestamp - (int) $lastActive;

            if ($elapsed > (self::INACTIVITY_MINUTES * 60)) {
                session()->forget(['organiser_id', 'organiser_last_active']);

                if ($request->expectsJson()) {
                    return response()->json([
                        'error'   => 'Session expired due to inactivity.',
                        'expired' => true,
                    ], 401);
                }

                return redirect()
                    ->route('organiser.login')
                    ->withErrors(['session' => 'Your session expired due to inactivity. Please log in again.']);
            }

            // Refresh timestamp
            session(['organiser_last_active' => now()->timestamp]);
        }

        return $next($request);
    }
}