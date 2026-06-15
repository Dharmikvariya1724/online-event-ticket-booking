<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Organiser\AuthController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganiserAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $organiser = AuthController::getAuthenticatedOrganiser();

        if (!$organiser) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('organiser.login')
                ->withErrors(['session' => 'Your session has expired. Please log in again.']);
        }

        // Inject organiser into request for easy access in controllers
        $request->attributes->set('organiser', $organiser);

        return $next($request);
    }
}