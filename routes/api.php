<?php

use App\Http\Controllers\Api\V1\Hospital\HospitalAuthController;
use App\Http\Controllers\Api\V1\Hospital\HospitalDashboardController;
use App\Http\Controllers\Api\V1\Hospital\HospitalLookupController;
use App\Http\Controllers\Api\V1\Hospital\HospitalPatientController;
use App\Http\Controllers\Api\V1\Hospital\HospitalRequisitionController;
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

Route::prefix('v1/hospital')->middleware([
    'auth:sanctum',
    'abilities:hospital-mobile',
    'hospital.active',
    'throttle:hospital-api',
])->group(function () {
    Route::get('/blood-groups', [HospitalLookupController::class, 'bloodGroups']);
    Route::get('/dashboard', [HospitalDashboardController::class, 'index']);

    Route::get('/patients', [HospitalPatientController::class, 'index']);
    Route::post('/patients', [HospitalPatientController::class, 'store']);
    Route::get('/patients/{id}', [HospitalPatientController::class, 'show']);
    Route::put('/patients/{id}', [HospitalPatientController::class, 'update']);

    Route::get('/requisitions', [HospitalRequisitionController::class, 'index']);
    Route::post('/requisitions', [HospitalRequisitionController::class, 'store']);
    Route::get('/requisitions/{id}', [HospitalRequisitionController::class, 'show']);
});
