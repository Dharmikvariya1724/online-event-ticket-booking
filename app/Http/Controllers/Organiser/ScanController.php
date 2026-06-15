<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Support\AdminAuth;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index(Request $request)
    {
        $organiser = AuthController::getAuthenticatedOrganiser();
        $admin = AdminAuth::user();
        $canValidate = $organiser !== null || $admin !== null;
        $scannerRole = $admin ? 'admin' : ($organiser ? 'organiser' : 'viewer');
        $scannerLayout = $organiser ? 'layouts.organiser' : 'layouts.app';

        return view('organiser.scan.index', compact(
            'organiser',
            'admin',
            'canValidate',
            'scannerRole',
            'scannerLayout'
        ));
    }
}
