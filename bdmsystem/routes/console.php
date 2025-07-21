<?php

use App\Models\Donor;
use App\Models\Appointment;
use App\Models\BloodInventory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Existing inspire command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Blood Donor System Commands
Artisan::command('donors:send-reminders', function () {
    $eligibleDonors = Donor::with('user')
        ->where('is_available', true)
        ->where(function($query) {
            $query->whereNull('last_donation_date')
                  ->orWhere('last_donation_date', '<=', now()->subMonths(3));
        })
        ->get();

    $count = $eligibleDonors->count();
    
    $eligibleDonors->each(function ($donor) {
        // In production, implement actual notification logic here
        $this->info("Would send reminder to: {$donor->user->email}");
        // Example: $donor->user->notify(new DonationReminder());
    });

    $this->info("{$count} donors eligible for reminders.");
})->purpose('Send reminders to eligible blood donors');

Artisan::command('inventory:check-expiry', function () {
    $expired = BloodInventory::where('expiry_date', '<', now())
        ->where('status', '!=', 'expired')
        ->update(['status' => 'expired']);

    $this->info("Marked {$expired} blood units as expired.");
})->purpose('Check and mark expired blood inventory');

Artisan::command('appointments:cleanup', function () {
    $threshold = now()->subYear();
    $deleted = Appointment::where('appointment_date', '<', $threshold)
        ->whereIn('status', ['completed', 'cancelled'])
        ->delete();

    $this->info("Cleaned up {$deleted} old appointments.");
})->purpose('Remove appointments older than 1 year');

// Test Data Command
Artisan::command('donors:create-test-data', function () {
    \App\Models\User::factory(10)->donor()->create()->each(function ($user) {
        \App\Models\Donor::factory()->create(['user_id' => $user->id]);
    });
    
    $this->info("Created 10 test donor accounts with profiles.");
})->purpose('Generate test donor data');

// Scheduling Setup (for Laravel 11)
Artisan::command('schedule:run-custom', function (Schedule $schedule) {
    $schedule->command('donors:send-reminders')->weekly();
    $schedule->command('inventory:check-expiry')->daily();
    $schedule->command('appointments:cleanup')->monthly();
    
    $this->info("Custom schedule commands registered.");
})->purpose('Register custom scheduled commands');

// Helper to setup scheduler in production
Artisan::command('donors:setup-scheduler', function () {
    $this->info("Add this to your server's cron tab:");
    $this->comment("* * * * * cd /path-to-your-project && php artisan schedule:run-custom >> /dev/null 2>&1");
})->purpose('Get instructions for setting up scheduled tasks');