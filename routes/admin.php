<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\BloodInventoryController;
use App\Http\Controllers\Admin\BloodRequestAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonorController;
use App\Http\Controllers\Admin\HospitalAdminController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PatientAdminController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TwoFactorAuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\NotificationCenterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Portal Routes
|--------------------------------------------------------------------------
|
| Secured routes for Central Blood Bank Administrators. Includes 2FA
| enforcement middleware for security-critical actions.
|
*/

Route::middleware(['auth', 'verified', 'admin', '2fa'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard & Overview
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/emergency-requests', [DashboardController::class, 'emergencyRequests'])->name('emergency_requests');
    Route::get('/emergency-requests-index', [DashboardController::class, 'emergencyRequests'])->name('emergency_requests.index');

    // Two-Factor Authentication Management
    Route::get('/2fa', [TwoFactorAuthController::class, 'show'])->name('2fa.show');
    Route::post('/2fa/enable', [TwoFactorAuthController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [TwoFactorAuthController::class, 'disable'])->name('2fa.disable');
    Route::post('/2fa/confirm', [TwoFactorAuthController::class, 'confirm'])->name('2fa.confirm');

    // User & Role Administration
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle_status');

    // Donor Management
    Route::resource('donors', DonorController::class);
    Route::post('/donors/{donor}/toggle-status', [DonorController::class, 'toggleStatus'])->name('donors.toggle_status');

    // Hospital & Patient Entities
    Route::get('/hospitals', [HospitalAdminController::class, 'index'])->name('hospitals.index');
    Route::get('/hospitals/create', [HospitalAdminController::class, 'create'])->name('hospitals.create');
    Route::post('/hospitals', [HospitalAdminController::class, 'store'])->name('hospitals.store');
    Route::get('/hospitals/{hospital}', [HospitalAdminController::class, 'show'])->name('hospitals.show');
    Route::get('/hospitals/{hospital}/edit', [HospitalAdminController::class, 'edit'])->name('hospitals.edit');
    Route::put('/hospitals/{hospital}', [HospitalAdminController::class, 'update'])->name('hospitals.update');
    Route::delete('/hospitals/{hospital}', [HospitalAdminController::class, 'destroy'])->name('hospitals.destroy');
    Route::get('/patients', [PatientAdminController::class, 'index'])->name('patients.index');
    Route::get('/patients/{patient}', [PatientAdminController::class, 'show'])->name('patients.show');
    
    // Appointments Management
    Route::resource('appointments', AppointmentController::class);
    Route::post('/appointments/{id}/intake', [AppointmentController::class, 'processIntake'])->name('appointments.intake');
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
    Route::delete('/blood-requests/{id}', [BloodRequestAdminController::class, 'destroy'])->name('blood_requests.destroy');

    // System Notifications Feed (Live Center)
    Route::get('/notifications-feed', [NotificationCenterController::class, 'index'])->name('notifications_feed.index');
    Route::post('/notifications-feed/{notification}/mark-read', [NotificationCenterController::class, 'markAsRead'])->name('notifications_feed.mark_read');
    Route::post('/notifications-feed/{notification}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications_feed.read');
    Route::post('/notifications-feed/mark-all-read', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications_feed.mark_all_read');
    Route::post('/notifications-feed/read-all', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications_feed.read_all');

    // System Broadcast Notifications Management
    Route::resource('notifications', NotificationController::class);
    
    // Analytics & Operational Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/donors', [ReportController::class, 'donorReport'])->name('reports.donors');
    Route::get('/reports/donations', [ReportController::class, 'donationReport'])->name('reports.donations');
    Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('/reports/monthly-stats', [ReportController::class, 'monthlyStats'])->name('reports.monthly-stats');
    Route::get('/reports/monthly_stats', [ReportController::class, 'monthlyStats'])->name('reports.monthly_stats');
    
    // Immutable Security & Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs-alt', [ActivityLogController::class, 'index'])->name('activity_logs.index');
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity_logs.show');
    
    // Platform Configuration & Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/blood-groups', [SettingsController::class, 'manageBloodGroups'])->name('settings.blood_groups');
    Route::post('/settings/blood-groups', [SettingsController::class, 'storeBloodGroup'])->name('settings.blood_groups.store');
    Route::put('/settings/blood-groups/{bloodGroup}', [SettingsController::class, 'updateBloodGroup'])->name('settings.blood_groups.update');
    Route::delete('/settings/blood-groups/{bloodGroup}', [SettingsController::class, 'destroyBloodGroup'])->name('settings.blood_groups.destroy');
    Route::get('/settings/cities', [SettingsController::class, 'manageCities'])->name('settings.cities');
    Route::post('/settings/cities', [SettingsController::class, 'storeCity'])->name('settings.cities.store');
    Route::put('/settings/cities/{index}', [SettingsController::class, 'updateCity'])->name('settings.cities.update');
    Route::delete('/settings/cities/{index}', [SettingsController::class, 'destroyCity'])->name('settings.cities.destroy');
    Route::post('/settings/cities-legacy', [SettingsController::class, 'updateCities'])->name('settings.cities.update_legacy');
});
