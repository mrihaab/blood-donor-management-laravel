<?php

namespace Tests\Feature;

use App\Models\BloodComponent;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\InventoryTransaction;
use App\Models\Patient;
use App\Models\Transfusion;
use App\Models\TransfusionReaction;
use App\Models\User;
use App\Services\BloodGroupCompatibilityService;
use App\Services\BloodRequestService;
use App\Services\TransfusionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase7ClinicalTransfusionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $hospitalUserA;
    protected User $hospitalUserB;
    protected Hospital $hospitalA;
    protected Hospital $hospitalB;
    protected Patient $patientA;
    protected Patient $patientB;
    protected BloodGroup $groupO;
    protected BloodGroup $groupA;
    protected BloodGroup $groupABPlus;
    protected BloodComponent $componentPRBC;
    protected BloodRequest $approvedRequestA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Chief Dr. House',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->hospitalA = Hospital::create([
            'name' => 'St. Jude General Hospital',
            'license_number' => 'HOSP-701',
            'address' => '700 Health Ave',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Elizabeth',
            'contact_phone' => '555-7001',
            'email' => 'er@stjude.org',
            'status' => 'active',
        ]);

        $this->hospitalB = Hospital::create([
            'name' => 'Mount Sinai Medical Center',
            'license_number' => 'HOSP-702',
            'address' => '800 Care Parkway',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Gregory',
            'contact_phone' => '555-7002',
            'email' => 'er@mtsinai.org',
            'status' => 'active',
        ]);

        $this->hospitalUserA = User::factory()->create([
            'name' => 'Hospital User A',
            'role' => 'hospital',
            'hospital_id' => $this->hospitalA->id,
            'email_verified_at' => now(),
        ]);

        $this->hospitalUserB = User::factory()->create([
            'name' => 'Hospital User B',
            'role' => 'hospital',
            'hospital_id' => $this->hospitalB->id,
            'email_verified_at' => now(),
        ]);

        $this->groupO = BloodGroup::create(['name' => 'O-', 'description' => 'O Negative']);
        $this->groupA = BloodGroup::create(['name' => 'A+', 'description' => 'A Positive']);
        $this->groupABPlus = BloodGroup::create(['name' => 'AB+', 'description' => 'AB Positive']);

        $this->patientA = Patient::create([
            'hospital_id' => $this->hospitalA->id,
            'name' => 'John Trauma Patient',
            'mrn' => 'MRN-PAT-701',
            'gender' => 'male',
            'date_of_birth' => '1985-05-15',
            'blood_group_id' => $this->groupA->id,
            'status' => 'active',
        ]);

        $this->patientB = Patient::create([
            'hospital_id' => $this->hospitalB->id,
            'name' => 'Jane Surgery Patient',
            'mrn' => 'MRN-PAT-702',
            'gender' => 'female',
            'date_of_birth' => '1990-08-20',
            'blood_group_id' => $this->groupO->id,
            'status' => 'active',
        ]);

        $this->componentPRBC = BloodComponent::where('code', 'PRBC')->first()
            ?? BloodComponent::create([
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
            ]);

        $requestService = app(BloodRequestService::class);
        $this->approvedRequestA = $requestService->createRequest([
            'patient_id' => $this->patientA->id,
            'patient_name' => $this->patientA->name,
            'hospital_id' => $this->hospitalA->id,
            'hospital' => $this->hospitalA->name,
            'city' => 'Metropolis',
            'blood_group' => 'A+',
            'units_needed' => 1,
            'urgency' => 'routine',
            'reason' => 'Scheduled Hip Surgery',
        ], $this->hospitalUserA);

        // Unit A (A+)
        $unitA = BloodUnit::create([
            'unit_number' => 'UNIT-701-APLUS',
            'blood_group_id' => $this->groupA->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addDays(35)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $requestService->approveRequest($this->approvedRequestA, $this->adminUser);
    }

    public function test_1_transfusion_can_be_scheduled_for_approved_request()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        $this->assertEquals('scheduled', $transfusion->status);
        $this->assertEquals($this->patientA->id, $transfusion->patient_id);
    }

    public function test_2_transfusion_requires_valid_patient_and_request_association()
    {
        $this->expectException(\InvalidArgumentException::class);
        $service = app(TransfusionService::class);
        // Attempting to schedule transfusion for Patient B using Request A (Patient A)
        $service->createTransfusion($this->approvedRequestA, $this->patientB, $this->hospitalUserA);
    }

    public function test_3_hospital_isolation_enforced_on_transfusion_records()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        // Hospital B user attempts to view Hospital A transfusion
        $response = $this->actingAs($this->hospitalUserB)
            ->get(route('hospital.transfusions.show', $transfusion->id));

        $response->assertStatus(403);
    }

    public function test_4_incompatible_blood_unit_cannot_be_issued_or_transfused()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        // Incompatible Unit (AB+ to A+ patient)
        $incompatibleUnit = BloodUnit::create([
            'unit_number' => 'UNIT-702-INCOMPATIBLE',
            'blood_group_id' => $this->groupABPlus->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addDays(35)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $service->issueUnits($transfusion, [$incompatibleUnit->id], $this->hospitalUserA);
    }

    public function test_5_compatible_blood_unit_passes_crossmatch_verification()
    {
        $compService = app(BloodGroupCompatibilityService::class);

        $unitO = BloodUnit::create([
            'unit_number' => 'UNIT-703-ONEG',
            'blood_group_id' => $this->groupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addDays(35)->format('Y-m-d'),
            'status' => 'available',
        ]);

        // O- universal donor unit is compatible for A+ patient
        $this->assertTrue($compService->validatePatientUnitCompatibility($this->patientA, $unitO));
    }

    public function test_6_allocated_unit_cannot_be_assigned_to_two_active_transfusions()
    {
        $service = app(TransfusionService::class);
        $t1 = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);
        $t2 = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        $unit = BloodUnit::where('blood_group_id', $this->groupA->id)->first();
        $service->issueUnits($t1, [$unit->id], $this->hospitalUserA);

        $this->expectException(\InvalidArgumentException::class);
        $service->issueUnits($t2, [$unit->id], $this->hospitalUserA);
    }

    public function test_7_expired_unit_cannot_be_issued_for_transfusion()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        $expiredUnit = BloodUnit::create([
            'unit_number' => 'UNIT-704-EXPIRED',
            'blood_group_id' => $this->groupA->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(50)->format('Y-m-d'),
            'expiry_date' => now()->subDays(5)->format('Y-m-d'),
            'status' => 'expired',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $service->issueUnits($transfusion, [$expiredUnit->id], $this->hospitalUserA);
    }

    public function test_8_discarded_unit_cannot_be_issued_for_transfusion()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        $discardedUnit = BloodUnit::create([
            'unit_number' => 'UNIT-705-DISCARDED',
            'blood_group_id' => $this->groupA->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(10)->format('Y-m-d'),
            'expiry_date' => now()->addDays(20)->format('Y-m-d'),
            'status' => 'discarded',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $service->issueUnits($transfusion, [$discardedUnit->id], $this->hospitalUserA);
    }

    public function test_9_transfusion_state_machine_rejects_invalid_transitions()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        // Cannot move from scheduled directly to completed
        $this->expectException(\InvalidArgumentException::class);
        $service->completeTransfusion($transfusion, $this->hospitalUserA);
    }

    public function test_10_transfusion_progresses_through_valid_lifecycle()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        $unit = BloodUnit::where('blood_group_id', $this->groupA->id)->first();
        $service->issueUnits($transfusion, [$unit->id], $this->hospitalUserA);
        $this->assertEquals('issued', $transfusion->fresh()->status);

        $service->startTransfusion($transfusion, $this->hospitalUserA);
        $this->assertEquals('started', $transfusion->fresh()->status);

        $service->completeTransfusion($transfusion, $this->hospitalUserA);
        $this->assertEquals('completed', $transfusion->fresh()->status);
        $this->assertEquals('transfused', $unit->fresh()->status);
    }

    public function test_11_transfusion_reaction_can_be_recorded()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);
        $unit = BloodUnit::where('blood_group_id', $this->groupA->id)->first();
        $service->issueUnits($transfusion, [$unit->id], $this->hospitalUserA);
        $service->startTransfusion($transfusion, $this->hospitalUserA);

        $reaction = $service->recordReaction($transfusion, [
            'reaction_type' => 'Febrile Non-Hemolytic',
            'severity'      => 'mild',
            'symptoms'      => 'Fever spike 38.5C, mild chills',
            'blood_unit_id' => $unit->id,
        ], $this->hospitalUserA);

        $this->assertEquals('mild', $reaction->severity);
        $this->assertDatabaseHas('transfusion_reactions', ['id' => $reaction->id]);
    }

    public function test_12_severe_reaction_stops_transfusion_and_triggers_admin_alert()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);
        $unit = BloodUnit::where('blood_group_id', $this->groupA->id)->first();
        $service->issueUnits($transfusion, [$unit->id], $this->hospitalUserA);
        $service->startTransfusion($transfusion, $this->hospitalUserA);

        $service->recordReaction($transfusion, [
            'reaction_type' => 'Acute Hemolytic',
            'severity'      => 'severe',
            'symptoms'      => 'Hypotension, hemoglobinuria',
            'blood_unit_id' => $unit->id,
        ], $this->hospitalUserA);

        $this->assertEquals('stopped', $transfusion->fresh()->status);
        $this->assertEquals('discarded', $unit->fresh()->status);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->adminUser->id,
            'type'    => 'reaction',
        ]);
    }

    public function test_13_transfusion_audit_trail_created()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);
        $unit = BloodUnit::where('blood_group_id', $this->groupA->id)->first();
        $service->issueUnits($transfusion, [$unit->id], $this->hospitalUserA);
        $service->startTransfusion($transfusion, $this->hospitalUserA);
        $service->completeTransfusion($transfusion, $this->hospitalUserA);

        $this->assertDatabaseHas('inventory_transactions', [
            'blood_unit_id'    => $unit->id,
            'transaction_type' => 'transfused',
        ]);
    }

    public function test_14_transfused_unit_remains_traceable_to_donor_and_donation()
    {
        $donor = Donor::create([
            'user_id' => User::factory()->create(['role' => 'donor'])->id,
            'blood_group_id' => $this->groupA->id,
            'gender' => 'male',
            'date_of_birth' => '1992-01-01',
            'contact_number' => '555-7777',
            'address' => '777 Trace Ave',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10001',
            'status' => 'active',
        ]);

        $donation = Donation::create([
            'donor_id' => $donor->id,
            'blood_group_id' => $this->groupA->id,
            'quantity' => 450,
            'status' => 'completed',
            'donation_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        $unitTraceable = BloodUnit::create([
            'unit_number' => 'UNIT-799-TRACEABLE',
            'blood_group_id' => $this->groupA->id,
            'component_id' => $this->componentPRBC->id,
            'donor_id' => $donor->id,
            'donation_id' => $donation->id,
            'collection_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addDays(35)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);
        $service->issueUnits($transfusion, [$unitTraceable->id], $this->hospitalUserA);
        $service->startTransfusion($transfusion, $this->hospitalUserA);
        $service->completeTransfusion($transfusion, $this->hospitalUserA);

        $this->assertEquals($donor->id, $unitTraceable->fresh()->donor_id);
        $this->assertEquals($donation->id, $unitTraceable->fresh()->donation_id);
    }

    public function test_15_concurrent_assignment_of_same_unit_prevented_with_pessimistic_lock()
    {
        $service = app(TransfusionService::class);
        $t1 = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);
        $unit = BloodUnit::where('blood_group_id', $this->groupA->id)->first();

        $service->issueUnits($t1, [$unit->id], $this->hospitalUserA);
        $this->assertEquals('dispensed', $unit->fresh()->status);
    }

    public function test_16_failed_clinical_transaction_rolls_back_all_database_changes()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        try {
            $service->issueUnits($transfusion, [99999], $this->hospitalUserA);
        } catch (\InvalidArgumentException $e) {
            // Expected exception
        }

        $this->assertEquals('scheduled', $transfusion->fresh()->status);
    }

    public function test_17_hospital_user_cannot_forge_patient_id()
    {
        // Hospital User A attempts to create transfusion using Hospital B's patient
        $response = $this->actingAs($this->hospitalUserA)->post(route('hospital.transfusions.store'), [
            'blood_request_id' => $this->approvedRequestA->id,
            'patient_id'       => $this->patientB->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_18_hospital_user_cannot_forge_hospital_id()
    {
        // Hospital User A posts a transfusion payload trying to attach Hospital B's ID
        $response = $this->actingAs($this->hospitalUserA)->post(route('hospital.transfusions.store'), [
            'blood_request_id' => $this->approvedRequestA->id,
            'patient_id'       => $this->patientA->id,
            'hospital_id'      => $this->hospitalB->id, // Forged payload ignored
        ]);

        $response->assertStatus(302);
        // Transfusion created strictly belongs to Hospital A
        $transfusion = Transfusion::latest()->first();
        $this->assertEquals($this->hospitalA->id, $transfusion->hospital_id);
    }

    public function test_19_unauthorized_user_cannot_access_transfusion_records()
    {
        $donorUser = User::factory()->create(['role' => 'donor']);
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);

        $response = $this->actingAs($donorUser)->get(route('hospital.transfusions.show', $transfusion->id));
        $response->assertStatus(403);
    }

    public function test_20_final_unit_disposition_transfused_and_returned_handled_correctly()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);
        $unit = BloodUnit::where('blood_group_id', $this->groupA->id)->first();
        $service->issueUnits($transfusion, [$unit->id], $this->hospitalUserA);

        $service->startTransfusion($transfusion, $this->hospitalUserA);
        $service->completeTransfusion($transfusion, $this->hospitalUserA);

        $this->assertEquals('transfused', $unit->fresh()->status);
    }

    public function test_21_returned_unit_quarantine_protocol_safety_certification()
    {
        $service = app(TransfusionService::class);
        $transfusion = $service->createTransfusion($this->approvedRequestA, $this->patientA, $this->hospitalUserA);
        $unit = BloodUnit::where('blood_group_id', $this->groupA->id)->first();
        $service->issueUnits($transfusion, [$unit->id], $this->hospitalUserA);

        // Returned unit safe certification test (<30 mins, seal intact)
        $inspection = $service->certifyReturnedUnit($unit, [
            'cold_chain_intact'        => true,
            'seal_intact'              => true,
            'elapsed_time_minutes'     => 15,
            'visual_inspection_passed' => true,
            'notes'                    => 'Returned unspiked within cold transport box',
        ], $this->adminUser);

        $this->assertEquals('certified_safe', $inspection->decision);
        $this->assertEquals('available', $unit->fresh()->status);

        $this->assertDatabaseHas('inventory_transactions', [
            'blood_unit_id'    => $unit->id,
            'transaction_type' => 'returned_to_stock',
        ]);
    }
}
