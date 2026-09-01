<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationCenterController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Breeze auth routes
require __DIR__.'/auth.php';

// Protected central routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user && $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user && $user->role === 'hospital') {
            return redirect()->route('hospital.dashboard');
        }
        return redirect()->route('donor.dashboard');
    })->name('dashboard');

    Route::get('/notifications', [NotificationCenterController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/click', [NotificationCenterController::class, 'clickAndRedirect'])->name('notifications.click');
    Route::post('/notifications/{notification}/click', [NotificationCenterController::class, 'clickAndRedirect'])->name('notifications.click_post');
    Route::post('/notifications/{notification}/read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/{notification}/mark-read', [NotificationCenterController::class, 'markAsRead'])->name('notifications.mark_read');
    Route::post('/notifications/read-all', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.read_all');
    Route::post('/notifications/mark-all-read', [NotificationCenterController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
    Route::get('/notifications/unread-feed', [NotificationCenterController::class, 'unreadFeed'])->name('notifications.unread_feed');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Blood Donor System Routes
require __DIR__.'/admin.php';
require __DIR__.'/hospital.php';
require __DIR__.'/donor.php';
