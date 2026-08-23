<?php

namespace Tests\Feature;

use App\Models\BloodComponent;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\DonorDeferral;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\BloodGroupCompatibilityService;
use App\Services\BloodRequestService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6EmergencyNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $hospitalUserA;
    protected User $hospitalUserB;
    protected User $eligibleDonorUser;
    protected User $deferredDonorUser;
    protected Hospital $hospitalA;
    protected Hospital $hospitalB;
    protected Patient $patientA;
    protected BloodGroup $groupO;
    protected BloodGroup $groupOPlus;
    protected BloodComponent $componentPRBC;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Dr. Carter',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->hospitalA = Hospital::create([
            'name' => 'St. Mary Emergency Hospital',
            'license_number' => 'HOSP-601',
            'address' => '500 ER Way',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Paul',
            'contact_phone' => '555-6001',
            'email' => 'er@stmary.org',
            'status' => 'active',
        ]);

        $this->hospitalB = Hospital::create([
            'name' => 'Metropolis General',
            'license_number' => 'HOSP-602',
            'address' => '600 Main St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Sarah',
            'contact_phone' => '555-6002',
            'email' => 'er@metropolis.org',
            'status' => 'active',
        ]);

        $this->hospitalUserA = User::factory()->create([
            'name' => 'Hospital Admin A',
            'role' => 'hospital',
            'hospital_id' => $this->hospitalA->id,
            'email_verified_at' => now(),
        ]);

        $this->hospitalUserB = User::factory()->create([
            'name' => 'Hospital Admin B',
            'role' => 'hospital',
            'hospital_id' => $this->hospitalB->id,
            'email_verified_at' => now(),
        ]);

        $this->groupO = BloodGroup::create(['name' => 'O-', 'description' => 'O Negative Universal']);
        $this->groupOPlus = BloodGroup::create(['name' => 'O+', 'description' => 'O Positive']);

        $this->patientA = Patient::create([
            'hospital_id' => $this->hospitalA->id,
            'name' => 'Emergency Patient Trauma',
            'mrn' => 'MRN-ER-99',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'blood_group_id' => $this->groupOPlus->id,
            'status' => 'active',
        ]);

        $this->componentPRBC = BloodComponent::where('code', 'PRBC')->first()
            ?? BloodComponent::create([
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
            ]);

        // Eligible Donor (O- Universal Donor)
        $this->eligibleDonorUser = User::factory()->create([
            'name' => 'Eligible Donor John',
            'role' => 'donor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Donor::create([
            'user_id' => $this->eligibleDonorUser->id,
            'blood_group_id' => $this->groupO->id,
            'gender' => 'male',
            'date_of_birth' => '1995-04-12',
            'contact_number' => '555-9000',
            'address' => '100 Donor Lane',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10001',
            'status' => 'active',
        ]);

        // Deferred Donor (O+ Medical Deferral)
        $this->deferredDonorUser = User::factory()->create([
            'name' => 'Deferred Donor Mark',
            'role' => 'donor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $deferredDonor = Donor::create([
            'user_id' => $this->deferredDonorUser->id,
            'blood_group_id' => $this->groupOPlus->id,
            'gender' => 'male',
            'date_of_birth' => '1992-06-20',
            'contact_number' => '555-9001',
            'address' => '200 Deferred Way',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10001',
            'status' => 'active',
        ]);

        DonorDeferral::create([
            'donor_id' => $deferredDonor->id,
            'deferral_type' => 'temporary',
            'reason' => 'Low Hemoglobin',
            'start_date' => now()->subDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(20)->format('Y-m-d'),
            'status' => 'active',
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_1_blood_group_compatibility_matrix()
    {
        $compService = new BloodGroupCompatibilityService();

        $compatibleForOPlus = $compService->getCompatibleDonorGroups('O+');
        $this->assertContains('O-', $compatibleForOPlus);
        $this->assertContains('O+', $compatibleForOPlus);

        $this->assertTrue($compService->isCompatible('O-', 'AB+'));
        $this->assertFalse($compService->isCompatible('AB+', 'O-'));
    }

    public function test_2_emergency_request_notifies_admin_and_eligible_donors_only()
    {
        $requestService = app(BloodRequestService::class);

        $req = $requestService->createRequest([
            'patient_id' => $this->patientA->id,
            'patient_name' => $this->patientA->name,
            'hospital_id' => $this->hospitalA->id,
            'hospital' => $this->hospitalA->name,
            'city' => 'Metropolis',
            'blood_group' => 'O+',
            'units_needed' => 2,
            'urgency' => 'emergency',
            'required_by' => now()->addHours(2)->toDateTimeString(),
            'reason' => 'Trauma Hemorrhage',
        ], $this->hospitalUserA);

        // Admin notification created
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->adminUser->id,
            'type' => 'emergency',
        ]);

        // Eligible donor notified
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->eligibleDonorUser->id,
            'type' => 'emergency',
        ]);

        // Deferred donor NOT notified
        $this->assertDatabaseMissing('user_notifications', [
            'user_id' => $this->deferredDonorUser->id,
            'type' => 'emergency',
        ]);
    }

    public function test_3_notification_idor_protection()
    {
        $notifA = UserNotification::create([
            'user_id' => $this->hospitalUserA->id,
            'type' => 'approved',
            'title' => 'Requisition Approved',
            'message' => 'Approved 2 units',
        ]);

        // Hospital B user attempts to mark Hospital A's notification as read
        $response = $this->actingAs($this->hospitalUserB)
            ->post(route('hospital.notifications.read', $notifA->id));

        $response->assertStatus(403);
        $this->assertNull($notifA->fresh()->read_at);
    }

    public function test_4_admin_emergency_priority_queue_sorting()
    {
        $reqRoutine = BloodRequest::create([
            'user_id' => $this->hospitalUserA->id,
            'hospital_id' => $this->hospitalA->id,
            'patient_id' => $this->patientA->id,
            'patient_name' => $this->patientA->name,
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => $this->hospitalA->name,
            'city' => 'Metropolis',
            'urgency_level' => 'routine',
            'status' => 'pending',
            'created_at' => now()->subHours(5),
        ]);

        $reqEmergency = BloodRequest::create([
            'user_id' => $this->hospitalUserA->id,
            'hospital_id' => $this->hospitalA->id,
            'patient_id' => $this->patientA->id,
            'patient_name' => $this->patientA->name,
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => $this->hospitalA->name,
            'city' => 'Metropolis',
            'urgency_level' => 'emergency',
            'status' => 'pending',
            'created_at' => now()->subHours(1),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.emergency_requests.index'));

        $response->assertStatus(200);
        // Emergency priority request appears first
        $response->assertSeeInOrder(['#REQ-' . $reqEmergency->id, '#REQ-' . $reqRoutine->id]);
    }

    public function test_5_emergency_fefo_allocation_and_status_notification()
    {
        // Create unit expiring in 2 days
        $unitSooner = BloodUnit::create([
            'unit_number' => 'UNIT-EMERGENCY-SOONER',
            'blood_group_id' => $this->groupOPlus->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $req = BloodRequest::create([
            'user_id' => $this->hospitalUserA->id,
            'hospital_id' => $this->hospitalA->id,
            'patient_id' => $this->patientA->id,
            'patient_name' => $this->patientA->name,
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => $this->hospitalA->name,
            'city' => 'Metropolis',
            'urgency_level' => 'emergency',
            'status' => 'pending',
        ]);

        $requestService = app(BloodRequestService::class);
        $requestService->approveRequest($req, $this->adminUser);

        $this->assertEquals('allocated', $unitSooner->fresh()->status);

        // Hospital user receives approval notification
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->hospitalUserA->id,
            'type' => 'approved',
        ]);
    }
}
