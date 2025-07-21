<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonorController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\BloodInventoryController;
use App\Http\Controllers\Admin\BloodRequestAdminController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ReportController;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Donor Management
    Route::resource('donors', DonorController::class);
    Route::post('/donors/{donor}/toggle-status', [DonorController::class, 'toggleStatus'])->name('donors.toggle_status');
    
    // Appointments Management
    Route::resource('appointments', AppointmentController::class);
    Route::post('/appointments/{id}/mark-completed', [AppointmentController::class, 'markCompleted'])->name('appointments.mark_completed');
    Route::post('/appointments/{id}/mark-cancelled', [AppointmentController::class, 'markCancelled'])->name('appointments.mark_cancelled');
    Route::post('/appointments/{id}/mark-no-show', [AppointmentController::class, 'markNoShow'])->name('appointments.mark_no_show');
    
    // Blood Inventory
    Route::resource('inventory', BloodInventoryController::class);
    Route::get('/inventory/low-stock-alerts', [BloodInventoryController::class, 'lowStockAlerts'])->name('inventory.low_stock_alerts');
    
    // Donations Management
    Route::resource('donations', \App\Http\Controllers\Admin\DonationController::class);
    Route::post('/donations/check-eligibility', [\App\Http\Controllers\Admin\DonationController::class, 'checkEligibility'])->name('donations.check_eligibility');
    
    // Blood Requests Management
    Route::get('/blood-requests', [BloodRequestAdminController::class, 'index'])->name('blood_requests.index');
    Route::post('/blood-requests/{id}/approve', [BloodRequestAdminController::class, 'approve'])->name('blood_requests.approve');
    Route::post('/blood-requests/{id}/reject', [BloodRequestAdminController::class, 'reject'])->name('blood_requests.reject');
    Route::post('/blood-requests/{id}/assign-donor', [BloodRequestAdminController::class, 'assignDonor'])->name('blood_requests.assign_donor');
    Route::post('/blood-requests/{id}/notify-donors', [BloodRequestAdminController::class, 'notifyDonors'])->name('blood_requests.notify_donors');
    Route::post('/blood-requests/{id}/fulfill', [BloodRequestAdminController::class, 'fulfill'])->name('blood_requests.fulfill');
    Route::post('/blood-requests/{id}/dispense', [BloodRequestAdminController::class, 'dispenseBlood'])->name('blood_requests.dispense');

    // Notifications Management
    Route::resource('notifications', NotificationController::class);
    Route::post('/notifications/send-bulk', [NotificationController::class, 'sendBulk'])->name('notifications.send_bulk');
    
    // Reports and Export
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/donors', [ReportController::class, 'donorReport'])->name('donors');
        Route::get('/donations', [ReportController::class, 'donationReport'])->name('donations');
        Route::get('/inventory', [ReportController::class, 'inventoryReport'])->name('inventory');
        Route::get('/monthly-stats', [ReportController::class, 'monthlyStats'])->name('monthly-stats');
    });
    
    // Settings Management
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
        
        // Blood Groups Management
        Route::get('/blood-groups', [SettingsController::class, 'manageBloodGroups'])->name('blood_groups');
        Route::post('/blood-groups', [SettingsController::class, 'storeBloodGroup'])->name('blood_groups.store');
        Route::put('/blood-groups/{bloodGroup}', [SettingsController::class, 'updateBloodGroup'])->name('blood_groups.update');
        Route::delete('/blood-groups/{bloodGroup}', [SettingsController::class, 'destroyBloodGroup'])->name('blood_groups.destroy');
        
        // Cities Management
        Route::get('/cities', [SettingsController::class, 'manageCities'])->name('cities');
        Route::put('/cities', [SettingsController::class, 'updateCities'])->name('cities.update');
    });
    
    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
        Route::put('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('toggle_status');
    });
});
