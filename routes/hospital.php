<?php

use App\Http\Controllers\Hospital\BloodRequestController;
use App\Http\Controllers\Hospital\DashboardController;
use App\Http\Controllers\Hospital\PatientController;
use App\Http\Controllers\Hospital\TransfusionController;
use App\Http\Controllers\NotificationCenterController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('hospital')->name('hospital.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notification Feed
    Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.read_all');

    // Patients Management
    Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientController::class, 'store'])->middleware('throttle:60,1')->name('patients.store');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{patient}/edit', [PatientController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{patient}', [PatientController::class, 'update'])->middleware('throttle:60,1')->name('patients.update');

    // Requisitions Management
    Route::get('/requests', [BloodRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [BloodRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [BloodRequestController::class, 'store'])->middleware('throttle:60,1')->name('requests.store');
    Route::get('/requests/{request}', [BloodRequestController::class, 'show'])->name('requests.show');

    // Clinical Transfusions Management
    Route::get('/transfusions', [TransfusionController::class, 'index'])->name('transfusions.index');
    Route::get('/transfusions/create', [TransfusionController::class, 'create'])->name('transfusions.create');
    Route::post('/transfusions', [TransfusionController::class, 'store'])->middleware('throttle:60,1')->name('transfusions.store');
    Route::get('/transfusions/{transfusion}', [TransfusionController::class, 'show'])->name('transfusions.show');
    Route::post('/transfusions/{transfusion}/issue', [TransfusionController::class, 'issue'])->middleware('throttle:60,1')->name('transfusions.issue');
    Route::post('/transfusions/{transfusion}/start', [TransfusionController::class, 'start'])->middleware('throttle:60,1')->name('transfusions.start');
    Route::post('/transfusions/{transfusion}/complete', [TransfusionController::class, 'complete'])->middleware('throttle:60,1')->name('transfusions.complete');
    Route::post('/transfusions/{transfusion}/stop', [TransfusionController::class, 'stop'])->middleware('throttle:60,1')->name('transfusions.stop');
    Route::post('/transfusions/{transfusion}/reaction', [TransfusionController::class, 'recordReaction'])->middleware('throttle:60,1')->name('transfusions.reaction');
});
