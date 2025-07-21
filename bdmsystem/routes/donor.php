<?php

use App\Http\Controllers\Donor\DashboardController;
use App\Http\Controllers\Donor\ProfileController as DonorProfileController;
use App\Http\Controllers\Donor\AppointmentController as DonorAppointmentController;
use App\Http\Controllers\BloodRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('donor')->name('donor.')->middleware(['auth', 'verified', 'donor'])->group(function () {
    // Donor Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Donor Profile
    Route::get('/profile', [DonorProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [DonorProfileController::class, 'update'])->name('profile.update');
    
    // Appointment Management
    Route::resource('appointments', DonorAppointmentController::class)->except(['destroy']);
    
    // Donation History
    Route::get('/history', [DashboardController::class, 'history'])->name('history');

    // ✅ Blood Requests
    Route::prefix('blood-requests')->name('blood_requests.')->group(function () {
        Route::get('/', [BloodRequestController::class, 'index'])->name('index');
        Route::get('/create', [BloodRequestController::class, 'create'])->name('create');
        Route::post('/', [BloodRequestController::class, 'store'])->name('store');
    });
});
