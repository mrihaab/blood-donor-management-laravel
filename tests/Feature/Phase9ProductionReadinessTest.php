<?php

namespace Tests\Feature;

use App\Models\BloodGroup;
use App\Models\Donor;
use App\Models\DonorDeferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase9ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected User $donorUser;
    protected Donor $donor;
    protected BloodGroup $groupA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupA = BloodGroup::create(['name' => 'A+', 'description' => 'A Positive']);

        $this->donorUser = User::factory()->create([
            'name' => 'Donor Test User',
            'role' => 'donor',
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $this->donor = Donor::create([
            'user_id' => $this->donorUser->id,
            'blood_group_id' => $this->groupA->id,
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'contact_number' => '555-0199',
            'address' => '100 Test St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10001',
            'is_available' => false,
            'status' => 'inactive',
        ]);
    }

    public function test_donor_deferral_expiry_command_processes_temporary_deferrals_and_reactivates_donors()
    {
        // Temporary deferral whose end_date has passed (expired yesterday)
        $expiredDeferral = DonorDeferral::create([
            'donor_id' => $this->donor->id,
            'deferral_type' => 'temporary',
            'reason' => 'Low Hemoglobin Screening',
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(1),
            'created_by' => $this->donorUser->id,
            'status' => 'active',
        ]);

        Artisan::call('donors:check-deferral-expiries');

        $this->assertEquals('expired', $expiredDeferral->fresh()->status);
        $this->assertEquals('active', $this->donor->fresh()->status);
        $this->assertTrue($this->donor->fresh()->is_available);
    }

    public function test_donor_deferral_expiry_does_not_reactivate_donor_with_remaining_active_deferral()
    {
        // Expired temporary deferral
        $expiredDeferral = DonorDeferral::create([
            'donor_id' => $this->donor->id,
            'deferral_type' => 'temporary',
            'reason' => 'Low Hemoglobin Screening',
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(1),
            'created_by' => $this->donorUser->id,
            'status' => 'active',
        ]);

        // Second active permanent deferral
        $permanentDeferral = DonorDeferral::create([
            'donor_id' => $this->donor->id,
            'deferral_type' => 'permanent',
            'reason' => 'Medical History Risk Factor',
            'start_date' => now()->subDays(5),
            'end_date' => null,
            'created_by' => $this->donorUser->id,
            'status' => 'active',
        ]);

        Artisan::call('donors:check-deferral-expiries');

        $this->assertEquals('expired', $expiredDeferral->fresh()->status);
        $this->assertEquals('active', $permanentDeferral->fresh()->status);
        $this->assertEquals('inactive', $this->donor->fresh()->status);
        $this->assertFalse($this->donor->fresh()->is_available);
    }

    public function test_queue_failing_event_listener_logs_exception()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Queue worker job execution failed'
                    && isset($context['job_name'])
                    && $context['exception'] === 'Test Queue Worker Failure Exception';
            });

        $fakeJob = new class {
            public function getName() { return 'TestEmergencyNotificationJob'; }
            public function getJobId() { return 'job-9999'; }
        };

        $event = new JobFailed('database', $fakeJob, new \Exception('Test Queue Worker Failure Exception'));
        event($event);
    }

    public function test_fefo_composite_index_exists()
    {
        $indexes = Schema::getIndexes('blood_units');
        $indexNames = collect($indexes)->pluck('name')->toArray();

        $this->assertContains('idx_blood_units_fefo_lookup', $indexNames);
    }
}
