<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScannerAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('services.scanner.token');

        if ($expectedToken === '') {
            return response()->json(['error' => 'Scanner API key not configured.'], 500);
        }

        $providedToken = $this->extractToken($request);

        if (!$providedToken || !hash_equals($expectedToken, $providedToken)) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $headerToken = $request->header('X-Scanner-Token');
        if ($headerToken) {
            return $headerToken;
        }

        $authorization = $request->header('Authorization');
        if ($authorization && str_starts_with($authorization, 'Bearer ')) {
            return substr($authorization, 7);
        }

        return null;
    }
}
