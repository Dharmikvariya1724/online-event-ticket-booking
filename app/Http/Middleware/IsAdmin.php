<?php

namespace App\Http\Middleware;

use App\Support\AdminAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = AdminAuth::user();

        if (!$admin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('admin.login')
                ->withErrors(['session' => 'Your session has expired. Please log in again.']);
        }

        $request->attributes->set('admin', $admin);

        return $next($request);
    }
}
