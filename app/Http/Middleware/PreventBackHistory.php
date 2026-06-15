<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldApply($request->route())) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');

        return $response;
    }

    private function shouldApply(mixed $route): bool
    {
        if (!$route instanceof Route) {
            return false;
        }

        $protectedMiddleware = [
            'is_admin',
            'organiser.auth',
            IsAdmin::class,
            OrganiserAuth::class,
        ];

        foreach ($route->gatherMiddleware() as $middleware) {
            if (in_array($middleware, $protectedMiddleware, true)) {
                return true;
            }
        }

        return false;
    }
}
