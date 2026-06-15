<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminAuth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (AdminAuth::user()) {
            return redirect()->route('admin.dashboard');
        }

        return view('organiser.auth.login', [
            'loginContext' => 'admin',
            'loginAction' => route('organiser.login'),
        ]);
    }

    public function logout(Request $request)
    {
        AdminAuth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect()->route('admin.login')->with('info', 'You have been logged out.');
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
