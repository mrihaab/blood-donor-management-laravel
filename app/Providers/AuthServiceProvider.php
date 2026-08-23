<?php

namespace App\Providers;

use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Transfusion;
use App\Policies\BloodRequestPolicy;
use App\Policies\HospitalPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\PatientPolicy;
use App\Policies\TransfusionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        BloodUnit::class => InventoryPolicy::class,
        Hospital::class => HospitalPolicy::class,
        Patient::class => PatientPolicy::class,
        BloodRequest::class => BloodRequestPolicy::class,
        Transfusion::class => TransfusionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
