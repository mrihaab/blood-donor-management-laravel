<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;

// Public route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Login As Interface
Route::get('/login-as', function () {
    return view('login-as');
})->name('login.as');

Route::post('/login-as', function (Request $request) {
    $request->validate([
        'role' => 'required|in:admin,donor',
        'email' => 'required|email|exists:users,email',
    ]);

    $user = User::where('email', $request->email)->where('role', $request->role)->first();

    if (!$user) {
        return back()->withErrors(['email' => 'No user found with that role and email.']);
    }

    Auth::login($user);

    return redirect()->route($user->role . '.dashboard');
});

// Breeze auth routes
require __DIR__.'/auth.php';

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Blood Donor System Routes
require __DIR__.'/admin.php';
require __DIR__.'/donor.php';
