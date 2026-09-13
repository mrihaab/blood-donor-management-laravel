<?php

use App\Http\Controllers\Api\V1\Hospital\HospitalAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1/hospital/auth')->group(function () {
    Route::post('/login', [HospitalAuthController::class, 'login'])
        ->middleware(['throttle:hospital-login']);

    Route::middleware(['auth:sanctum', 'abilities:hospital-mobile', 'throttle:hospital-api'])->group(function () {
        Route::post('/logout', [HospitalAuthController::class, 'logout']);
        Route::post('/logout-all', [HospitalAuthController::class, 'logoutAll']);

        Route::middleware(['hospital.active'])->group(function () {
            Route::get('/me', [HospitalAuthController::class, 'me']);
        });
    });
});
