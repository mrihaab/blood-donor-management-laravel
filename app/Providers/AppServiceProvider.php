<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\BloodRequest;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\User;
use App\Policies\AppointmentPolicy;
use App\Policies\BloodRequestPolicy;
use App\Policies\DonationPolicy;
use App\Policies\DonorPolicy;
use App\Policies\UserPolicy;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        if (config('app.env') === 'production' || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        Gate::policy(Donor::class, DonorPolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(BloodRequest::class, BloodRequestPolicy::class);
        Gate::policy(Donation::class, DonationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        // Server-side audit log immutability protection
        Activity::updating(function () {
            throw new \LogicException('Activity log records are immutable and cannot be modified.');
        });

        Activity::deleting(function () {
            throw new \LogicException('Activity log records are immutable and cannot be deleted.');
        });

        // Queue Worker Failure Observability Listener
        Queue::failing(function (JobFailed $event) {
            Log::error('Queue worker job execution failed', [
                'connection' => $event->connectionName,
                'job_name'   => $event->job->getName(),
                'job_id'     => $event->job->getJobId(),
                'exception'  => $event->exception->getMessage(),
                'failed_at'  => now()->toIso8601String(),
            ]);
        });
    }
}
