<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScanValidationController;

Route::middleware(['web'])->group(function () {
    Route::post('/scan-ticket', [ScanValidationController::class, 'scanTicket'])
        ->name('api.scan.ticket');

    Route::post('/scan/validate', [ScanValidationController::class, 'scanTicket'])
        ->name('api.scan.validate');
});
