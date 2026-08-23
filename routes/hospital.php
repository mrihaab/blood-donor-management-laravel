<?php

use App\Http\Controllers\Hospital\BloodRequestController;
use App\Http\Controllers\Hospital\DashboardController;
use App\Http\Controllers\Hospital\PatientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('hospital')->name('hospital.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Patients Management
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->name('patients.update');

    // Requisitions Management
    Route::get('/requests', [BloodRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [BloodRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [BloodRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{request}', [BloodRequestController::class, 'show'])->name('requests.show');
});
