<?php

namespace Tests\Feature;

use App\Models\BloodComponent;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use App\Services\BloodRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5HospitalPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $hospitalUserA;
    protected User $hospitalUserB;
    protected Hospital $hospitalA;
    protected Hospital $hospitalB;
    protected Patient $patientA;
    protected Patient $patientB;
    protected BloodGroup $bloodGroupO;
    protected BloodComponent $componentPRBC;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Dr. Smith',
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->hospitalA = Hospital::create([
            'name' => 'St. Jude General Hospital',
            'license_number' => 'HOSP-001',
            'address' => '100 Medical Plaza',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Alice',
            'contact_phone' => '555-0111',
            'email' => 'contact@stjude.org',
            'status' => 'active',
        ]);

        $this->hospitalB = Hospital::create([
            'name' => 'Mercy City Hospital',
            'license_number' => 'HOSP-002',
            'address' => '200 Health Way',
            'city' => 'Gotham',
            'state' => 'NY',
            'contact_person' => 'Dr. Bob',
            'contact_phone' => '555-0222',
            'email' => 'contact@mercy.org',
            'status' => 'active',
        ]);

        $this->hospitalUserA = User::factory()->create([
            'name' => 'Hospital Admin A',
            'email_verified_at' => now(),
            'role' => 'hospital',
            'hospital_id' => $this->hospitalA->id,
            'status' => 'active',
        ]);

        $this->hospitalUserB = User::factory()->create([
            'name' => 'Hospital Admin B',
            'email_verified_at' => now(),
            'role' => 'hospital',
            'hospital_id' => $this->hospitalB->id,
            'status' => 'active',
        ]);

        $this->bloodGroupO = BloodGroup::create(['name' => 'O+', 'description' => 'O Positive']);

        $this->patientA = Patient::create([
            'hospital_id' => $this->hospitalA->id,
            'name' => 'Patient Alice Smith',
            'mrn' => 'MRN-A-001',
            'gender' => 'female',
            'date_of_birth' => '1985-05-15',
            'blood_group_id' => $this->bloodGroupO->id,
            'status' => 'active',
        ]);

        $this->patientB = Patient::create([
            'hospital_id' => $this->hospitalB->id,
            'name' => 'Patient Bob Jones',
            'mrn' => 'MRN-B-002',
            'gender' => 'male',
            'date_of_birth' => '1992-08-20',
            'blood_group_id' => $this->bloodGroupO->id,
            'status' => 'active',
        ]);

        $this->componentPRBC = BloodComponent::where('code', 'PRBC')->first()
            ?? BloodComponent::create([
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
            ]);
    }

    public function test_1_hospital_user_a_can_view_own_dashboard()
    {
        $response = $this->actingAs($this->hospitalUserA)->get(route('hospital.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('St. Jude General Hospital');
    }

    public function test_2_hospital_user_a_cannot_view_hospital_b_patient()
    {
        $response = $this->actingAs($this->hospitalUserA)->get(route('hospital.patients.show', $this->patientB->id));

        $response->assertStatus(403);
    }

    public function test_3_hospital_user_a_cannot_view_hospital_b_requisition()
    {
        $requestB = BloodRequest::create([
            'user_id' => $this->hospitalUserB->id,
            'hospital_id' => $this->hospitalB->id,
            'patient_id' => $this->patientB->id,
            'patient_name' => $this->patientB->name,
            'blood_group' => 'O+',
            'units_needed' => 2,
            'hospital' => $this->hospitalB->name,
            'city' => 'Gotham',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->hospitalUserA)->get(route('hospital.requests.show', $requestB->id));

        $response->assertStatus(403);
    }

    public function test_4_requisition_creation_forces_authenticated_server_hospital_id()
    {
        $response = $this->actingAs($this->hospitalUserA)->post(route('hospital.requests.store'), [
            'patient_id' => $this->patientA->id,
            'blood_group' => 'O+',
            'units_needed' => 2,
            'urgency' => 'urgent',
            'hospital_id' => $this->hospitalB->id, // Forged hospital ID
        ]);

        $response->assertRedirect();
        
        $req = BloodRequest::where('patient_id', $this->patientA->id)->first();
        $this->assertNotNull($req);
        // Must match Hospital A, forged Hospital B ID ignored
        $this->assertEquals($this->hospitalA->id, $req->hospital_id);
    }

    public function test_5_requisition_creation_fails_if_selecting_patient_from_another_hospital()
    {
        $response = $this->actingAs($this->hospitalUserA)->post(route('hospital.requests.store'), [
            'patient_id' => $this->patientB->id, // Patient belonging to Hospital B
            'blood_group' => 'O+',
            'units_needed' => 1,
            'urgency' => 'routine',
        ]);

        $response->assertSessionHasErrors(['patient_id']);
    }

    public function test_6_hospital_users_cannot_approve_or_dispense_requisitions()
    {
        $requestA = BloodRequest::create([
            'user_id' => $this->hospitalUserA->id,
            'hospital_id' => $this->hospitalA->id,
            'patient_id' => $this->patientA->id,
            'patient_name' => $this->patientA->name,
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => $this->hospitalA->name,
            'city' => 'Metropolis',
            'status' => 'pending',
        ]);

        $policy = new \App\Policies\BloodRequestPolicy();

        $this->assertFalse($policy->approve($this->hospitalUserA, $requestA));
        $this->assertFalse($policy->dispense($this->hospitalUserA, $requestA));
        $this->assertTrue($policy->approve($this->adminUser, $requestA));
    }

    public function test_7_admin_approval_allocates_units_via_fefo_and_logs_transaction()
    {
        // Unit 1: Expires in 15 days
        $unitLater = BloodUnit::create([
            'unit_number' => 'UNIT-FEFO-LATER',
            'blood_group_id' => $this->bloodGroupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addDays(15)->format('Y-m-d'),
            'status' => 'available',
        ]);

        // Unit 2: Expires in 3 days (Earliest Expiry)
        $unitSooner = BloodUnit::create([
            'unit_number' => 'UNIT-FEFO-SOONER',
            'blood_group_id' => $this->bloodGroupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(10)->format('Y-m-d'),
            'expiry_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $request = BloodRequest::create([
            'user_id' => $this->hospitalUserA->id,
            'hospital_id' => $this->hospitalA->id,
            'patient_id' => $this->patientA->id,
            'patient_name' => $this->patientA->name,
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => $this->hospitalA->name,
            'city' => 'Metropolis',
            'status' => 'pending',
        ]);

        $requestService = app(BloodRequestService::class);
        $requestService->approveRequest($request, $this->adminUser);

        $this->assertEquals('allocated', $unitSooner->fresh()->status);
        $this->assertEquals('available', $unitLater->fresh()->status);

        $this->assertDatabaseHas('inventory_transactions', [
            'blood_unit_id' => $unitSooner->id,
            'transaction_type' => 'allocated',
            'user_id' => $this->adminUser->id,
        ]);
    }
}
