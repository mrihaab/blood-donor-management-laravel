<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BloodComponent;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\BloodInventoryService;
use App\Services\BloodRequestService;
use App\Services\DonationService;
use App\Services\DonorDeferralService;
use App\Services\DonorEligibilityService;
use App\Services\DonorScreeningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase4OperationalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $donorUser;
    protected Donor $donor;
    protected BloodGroup $bloodGroupO;
    protected BloodComponent $componentPRBC;

    protected DonorDeferralService $deferralService;
    protected DonorEligibilityService $eligibilityService;
    protected DonorScreeningService $screeningService;
    protected AppointmentService $appointmentService;
    protected DonationService $donationService;
    protected BloodInventoryService $inventoryService;
    protected BloodRequestService $requestService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Dr. Smith',
            'email_verified_at' => now(),
        ]);
        $this->adminUser->role = 'admin';
        $this->adminUser->save();

        $this->donorUser = User::factory()->create([
            'name' => 'John Doe',
            'email_verified_at' => now(),
            'role' => 'donor',
            'status' => 'active',
        ]);

        $this->bloodGroupO = BloodGroup::create(['name' => 'O+', 'description' => 'O Positive']);

        $this->donor = Donor::create([
            'user_id' => $this->donorUser->id,
            'blood_group_id' => $this->bloodGroupO->id,
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'contact_number' => '555-0199',
            'address' => '123 Main Street',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10001',
            'status' => 'active',
        ]);

        $this->componentPRBC = BloodComponent::where('code', 'PRBC')->first()
            ?? BloodComponent::create([
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
            ]);

        $this->deferralService = app(DonorDeferralService::class);
        $this->eligibilityService = app(DonorEligibilityService::class);
        $this->screeningService = app(DonorScreeningService::class);
        $this->appointmentService = app(AppointmentService::class);
        $this->donationService = app(DonationService::class);
        $this->inventoryService = app(BloodInventoryService::class);
        $this->requestService = app(BloodRequestService::class);
    }

    public function test_1_eligible_donor_can_book_appointment()
    {
        $appointment = $this->appointmentService->bookAppointment($this->donor, [
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
            'appointment_time' => '09:00:00',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'donor_id' => $this->donor->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_2_deferred_donor_cannot_book_appointment()
    {
        $this->deferralService->deferDonor(
            $this->donor,
            'temporary',
            'Low Hemoglobin Count',
            now()->addDays(30),
            $this->adminUser
        );

        $this->expectException(\InvalidArgumentException::class);

        $this->appointmentService->bookAppointment($this->donor, [
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
        ]);
    }

    public function test_3_pre_donation_screening_records_vitals_and_handles_low_hemoglobin_deferral()
    {
        $appointment = $this->appointmentService->bookAppointment($this->donor, [
            'appointment_date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        // Failed screening due to low hemoglobin (11.0 g/dL < 12.5)
        $screening = $this->screeningService->processScreening($appointment, [
            'blood_pressure' => '120/80',
            'pulse' => 72,
            'temperature' => 36.6,
            'weight' => 70,
            'hemoglobin' => 11.0,
            'notes' => 'Slight anemia',
        ], $this->adminUser);

        $this->assertEquals('temporarily_deferred', $screening->status);
        $this->assertEquals('deferred', $appointment->fresh()->status);

        $activeDeferral = $this->deferralService->getActiveDeferral($this->donor);
        $this->assertNotNull($activeDeferral);
        $this->assertEquals('temporary', $activeDeferral->deferral_type);
    }

    public function test_4_appointment_lifecycle_state_machine_transitions()
    {
        $appointment = $this->appointmentService->bookAppointment($this->donor, [
            'appointment_date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        $this->assertEquals('scheduled', $appointment->status);

        $this->appointmentService->transitionState($appointment, 'checked_in', $this->adminUser);
        $this->assertEquals('checked_in', $appointment->fresh()->status);

        $this->appointmentService->transitionState($appointment, 'screening', $this->adminUser);
        $this->assertEquals('screening', $appointment->fresh()->status);

        // Invalid backward transition must throw exception
        $this->expectException(\InvalidArgumentException::class);
        $this->appointmentService->transitionState($appointment, 'scheduled', $this->adminUser);
    }

    public function test_5_fefo_allocation_selects_earliest_expiring_unit_first()
    {
        // Unit 1: Expiring in 10 days
        $unitLater = BloodUnit::create([
            'unit_number' => 'UNIT-LATER-001',
            'blood_group_id' => $this->bloodGroupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'available',
        ]);

        // Unit 2: Expiring in 2 days (Earliest Expiry)
        $unitSooner = BloodUnit::create([
            'unit_number' => 'UNIT-SOONER-001',
            'blood_group_id' => $this->bloodGroupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(10)->format('Y-m-d'),
            'expiry_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $request = BloodRequest::create([
            'user_id' => $this->donorUser->id,
            'patient_name' => 'Jane Patient',
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => 'City Hospital',
            'city' => 'Metropolis',
            'status' => 'pending',
        ]);

        // Approve request — FEFO should pick $unitSooner first
        $this->requestService->approveRequest($request, $this->adminUser);

        $this->assertEquals('allocated', $unitSooner->fresh()->status);
        $this->assertEquals('available', $unitLater->fresh()->status);
    }

    public function test_6_expiry_processing_is_idempotent()
    {
        $expiredUnit = BloodUnit::create([
            'unit_number' => 'UNIT-EXPIRED-999',
            'blood_group_id' => $this->bloodGroupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(50)->format('Y-m-d'),
            'expiry_date' => now()->subDays(2)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $countRun1 = $this->inventoryService->processExpiredUnits();
        $this->assertEquals(1, $countRun1);
        $this->assertEquals('expired', $expiredUnit->fresh()->status);

        // Second run must be idempotent (0 units modified)
        $countRun2 = $this->inventoryService->processExpiredUnits();
        $this->assertEquals(0, $countRun2);
    }
}
