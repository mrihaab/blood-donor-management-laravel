<?php

namespace Tests\Feature\Api\V1\Hospital;

use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\NewBloodRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HospitalRequisitionApiTest extends TestCase
{
    use RefreshDatabase;

    protected Hospital $activeHospital;
    protected Hospital $otherHospital;
    protected Hospital $inactiveHospital;
    protected User $validHospitalUser;
    protected User $otherHospitalUser;
    protected User $adminUser;
    protected User $unverifiedUser;
    protected User $inactiveHospitalUser;
    protected Patient $hospitalAPatient;
    protected Patient $hospitalBPatient;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure canonical blood groups exist
        BloodGroup::firstOrCreate(['name' => 'A+'], ['description' => 'A Positive']);
        BloodGroup::firstOrCreate(['name' => 'O-'], ['description' => 'O Negative']);

        $this->activeHospital = Hospital::create([
            'name' => 'Central Clinical Hospital',
            'license_number' => 'HOSP-ACTIVE-01',
            'address' => '100 Health Ave',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Alice',
            'contact_phone' => '555-0100',
            'email' => 'contact@centralhosp.org',
            'status' => 'active',
        ]);

        $this->otherHospital = Hospital::create([
            'name' => 'City General Hospital',
            'license_number' => 'HOSP-OTHER-02',
            'address' => '200 Metro Rd',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Bob',
            'contact_phone' => '555-0200',
            'email' => 'contact@cityhosp.org',
            'status' => 'active',
        ]);

        $this->inactiveHospital = Hospital::create([
            'name' => 'Closed Facility',
            'license_number' => 'HOSP-INACTIVE-03',
            'address' => '300 Closed St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Charlie',
            'contact_phone' => '555-0300',
            'email' => 'contact@closedhosp.org',
            'status' => 'inactive',
        ]);

        $this->validHospitalUser = User::factory()->create([
            'name' => 'Dr. Valid User',
            'email' => 'valid.hospital@example.test',
            'password' => Hash::make('Secret123!'),
            'role' => 'hospital',
            'hospital_id' => $this->activeHospital->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->otherHospitalUser = User::factory()->create([
            'name' => 'Dr. Other User',
            'email' => 'other.hospital@example.test',
            'password' => Hash::make('Secret123!'),
            'role' => 'hospital',
            'hospital_id' => $this->otherHospital->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->adminUser = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('Secret123!'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->unverifiedUser = User::factory()->create([
            'name' => 'Dr. Unverified User',
            'email' => 'unverified.hospital@example.test',
            'password' => Hash::make('Secret123!'),
            'role' => 'hospital',
            'hospital_id' => $this->activeHospital->id,
            'status' => 'active',
            'email_verified_at' => null,
        ]);

        $this->inactiveHospitalUser = User::factory()->create([
            'name' => 'Dr. Inactive Hosp User',
            'email' => 'inactive.hosp.user@example.test',
            'password' => Hash::make('Secret123!'),
            'role' => 'hospital',
            'hospital_id' => $this->inactiveHospital->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->hospitalAPatient = Patient::create([
            'hospital_id' => $this->activeHospital->id,
            'name' => 'Patient Alpha',
            'mrn' => 'MRN-HOSP-A-001',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'status' => 'active',
        ]);

        $this->hospitalBPatient = Patient::create([
            'hospital_id' => $this->otherHospital->id,
            'name' => 'Patient Beta',
            'mrn' => 'MRN-HOSP-B-001',
            'gender' => 'female',
            'date_of_birth' => '1992-05-15',
            'status' => 'active',
        ]);
    }

    protected function issueToken(User $user, array $abilities = ['hospital-mobile']): string
    {
        return $user->createToken('test-device', $abilities)->plainTextToken;
    }

    #[Test]
    public function unauthenticated_user_cannot_access_requisitions(): void
    {
        $response = $this->getJson('/api/v1/hospital/requisitions');
        $response->assertStatus(401);
    }

    #[Test]
    public function token_missing_hospital_mobile_ability_is_rejected(): void
    {
        $token = $this->issueToken($this->validHospitalUser, ['other-ability']);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/requisitions');
        $response->assertStatus(403);
    }

    #[Test]
    public function unverified_hospital_user_is_rejected(): void
    {
        $token = $this->issueToken($this->unverifiedUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/requisitions');
        $response->assertStatus(403);
    }

    #[Test]
    public function inactive_hospital_user_is_rejected(): void
    {
        $token = $this->issueToken($this->inactiveHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/requisitions');
        $response->assertStatus(403);
    }

    #[Test]
    public function eligible_hospital_can_fetch_requisitions_list(): void
    {
        $token = $this->issueToken($this->validHospitalUser);

        BloodRequest::create([
            'hospital_id' => $this->activeHospital->id,
            'user_id' => $this->validHospitalUser->id,
            'patient_id' => $this->hospitalAPatient->id,
            'patient_name' => $this->hospitalAPatient->name,
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'blood_group' => 'A+',
            'units_needed' => 2,
            'urgency_level' => 'urgent',
            'status' => 'pending',
        ]);

        $response = $this->withToken($token)->getJson('/api/v1/hospital/requisitions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'patient' => ['id', 'mrn', 'name'],
                        'blood_group',
                        'units_needed',
                        'urgency_level',
                        'status',
                        'required_by',
                        'created_at',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    #[Test]
    public function empty_requisitions_list_returns_valid_structure(): void
    {
        $token = $this->issueToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/requisitions');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                ],
            ]);
    }

    #[Test]
    public function requisitions_list_only_returns_authenticated_hospital_records(): void
    {
        $tokenA = $this->issueToken($this->validHospitalUser);

        BloodRequest::create([
            'hospital_id' => $this->activeHospital->id,
            'user_id' => $this->validHospitalUser->id,
            'patient_id' => $this->hospitalAPatient->id,
            'patient_name' => $this->hospitalAPatient->name,
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'blood_group' => 'A+',
            'units_needed' => 2,
            'urgency_level' => 'urgent',
            'status' => 'pending',
        ]);

        BloodRequest::create([
            'hospital_id' => $this->otherHospital->id,
            'user_id' => $this->otherHospitalUser->id,
            'patient_id' => $this->hospitalBPatient->id,
            'patient_name' => $this->hospitalBPatient->name,
            'hospital' => $this->otherHospital->name,
            'city' => 'Metropolis',
            'blood_group' => 'O-',
            'units_needed' => 1,
            'urgency_level' => 'emergency',
            'status' => 'pending',
        ]);

        $response = $this->withToken($tokenA)->getJson('/api/v1/hospital/requisitions');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->hospitalAPatient->name, $response->json('data.0.patient.name'));
    }

    #[Test]
    public function requisitions_list_supports_all_authoritative_status_filters(): void
    {
        $token = $this->issueToken($this->validHospitalUser);

        foreach (['pending', 'approved', 'dispensed', 'rejected'] as $st) {
            BloodRequest::create([
                'hospital_id' => $this->activeHospital->id,
                'user_id' => $this->validHospitalUser->id,
                'patient_id' => $this->hospitalAPatient->id,
                'patient_name' => $this->hospitalAPatient->name,
                'hospital' => $this->activeHospital->name,
                'city' => 'Metropolis',
                'blood_group' => 'A+',
                'units_needed' => 1,
                'urgency_level' => 'routine',
                'status' => $st,
            ]);
        }

        foreach (['pending', 'approved', 'dispensed', 'rejected'] as $st) {
            $res = $this->withToken($token)->getJson("/api/v1/hospital/requisitions?status={$st}");
            $res->assertStatus(200);
            $this->assertCount(1, $res->json('data'));
            $this->assertEquals($st, $res->json('data.0.status'));
        }
    }

    #[Test]
    public function invalid_status_filter_returns_empty_data(): void
    {
        $token = $this->issueToken($this->validHospitalUser);

        BloodRequest::create([
            'hospital_id' => $this->activeHospital->id,
            'user_id' => $this->validHospitalUser->id,
            'patient_id' => $this->hospitalAPatient->id,
            'patient_name' => $this->hospitalAPatient->name,
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'blood_group' => 'A+',
            'units_needed' => 1,
            'urgency_level' => 'routine',
            'status' => 'pending',
        ]);

        $res = $this->withToken($token)->getJson('/api/v1/hospital/requisitions?status=cancelled');
        $res->assertStatus(200);
        $this->assertCount(0, $res->json('data'));
    }

    #[Test]
    public function requisitions_list_pagination_bounds_and_ordering(): void
    {
        $token = $this->issueToken($this->validHospitalUser);

        for ($i = 1; $i <= 5; $i++) {
            BloodRequest::create([
                'hospital_id' => $this->activeHospital->id,
                'user_id' => $this->validHospitalUser->id,
                'patient_id' => $this->hospitalAPatient->id,
                'patient_name' => "Patient Alpha {$i}",
                'hospital' => $this->activeHospital->name,
                'city' => 'Metropolis',
                'blood_group' => 'A+',
                'units_needed' => 1,
                'urgency_level' => 'routine',
                'status' => 'pending',
                'created_at' => now()->subMinutes(10 - $i),
            ]);
        }

        $resPage1 = $this->withToken($token)->getJson('/api/v1/hospital/requisitions?per_page=2&page=1');
        $resPage1->assertStatus(200);
        $this->assertCount(2, $resPage1->json('data'));
        $this->assertEquals(5, $resPage1->json('meta.total'));
        $this->assertEquals(3, $resPage1->json('meta.last_page'));

        // Test ordering created_at DESC (most recent first)
        $firstItemName = $resPage1->json('data.0.patient_name');
        $this->assertEquals('Patient Alpha 5', $firstItemName);
    }

    #[Test]
    public function requisitions_list_search_by_name_and_mrn_with_escaping(): void
    {
        $token = $this->issueToken($this->validHospitalUser);

        $req1 = BloodRequest::create([
            'hospital_id' => $this->activeHospital->id,
            'user_id' => $this->validHospitalUser->id,
            'patient_id' => $this->hospitalAPatient->id,
            'patient_name' => 'John % Special',
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'blood_group' => 'A+',
            'units_needed' => 1,
            'urgency_level' => 'routine',
            'status' => 'pending',
        ]);

        $req2 = BloodRequest::create([
            'hospital_id' => $this->activeHospital->id,
            'user_id' => $this->validHospitalUser->id,
            'patient_id' => $this->hospitalAPatient->id,
            'patient_name' => 'Jane Normal',
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'blood_group' => 'O-',
            'units_needed' => 1,
            'urgency_level' => 'routine',
            'status' => 'pending',
        ]);

        // Search for % escaping
        $resName = $this->withToken($token)->getJson('/api/v1/hospital/requisitions?search=%25');
        $resName->assertStatus(200);
        $this->assertCount(1, $resName->json('data'));
        $this->assertEquals($req1->id, $resName->json('data.0.id'));

        // Search by MRN
        $resMrn = $this->withToken($token)->getJson('/api/v1/hospital/requisitions?search=MRN-HOSP-A');
        $resMrn->assertStatus(200);
        $this->assertCount(2, $resMrn->json('data'));
    }

    #[Test]
    public function requisition_detail_returns_complete_allowlisted_resource_with_reason(): void
    {
        $token = $this->issueToken($this->validHospitalUser);

        $req = BloodRequest::create([
            'hospital_id' => $this->activeHospital->id,
            'user_id' => $this->validHospitalUser->id,
            'patient_id' => $this->hospitalAPatient->id,
            'patient_name' => $this->hospitalAPatient->name,
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'blood_group' => 'A+',
            'units_needed' => 3,
            'urgency_level' => 'urgent',
            'reason' => 'Surgical support required for emergency trauma',
            'status' => 'pending',
            'ward_name' => 'Ward 4',
            'room_number' => '402',
            'bed_number' => 'B',
            'attendant_name' => 'Attendant Bob',
            'attendant_phone' => '555-4321',
        ]);

        $response = $this->withToken($token)->getJson("/api/v1/hospital/requisitions/{$req->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $req->id,
                    'patient' => [
                        'id' => $this->hospitalAPatient->id,
                        'mrn' => $this->hospitalAPatient->mrn,
                        'name' => $this->hospitalAPatient->name,
                    ],
                    'blood_group' => 'A+',
                    'units_needed' => 3,
                    'urgency_level' => 'urgent',
                    'reason' => 'Surgical support required for emergency trauma',
                    'status' => 'pending',
                    'ward_name' => 'Ward 4',
                    'room_number' => '402',
                    'bed_number' => 'B',
                    'attendant_name' => 'Attendant Bob',
                    'attendant_phone' => '555-4321',
                ],
            ]);

        // Assert internal ownership IDs are excluded from detail resource
        $this->assertArrayNotHasKey('user_id', $response->json('data'));
        $this->assertArrayNotHasKey('hospital_id', $response->json('data'));
        $this->assertArrayNotHasKey('dispensed_at', $response->json('data'));
        $this->assertArrayNotHasKey('cancelled_at', $response->json('data'));
        $this->assertArrayNotHasKey('completed_at', $response->json('data'));
    }

    #[Test]
    public function cross_hospital_requisition_detail_returns_non_enumerating_404(): void
    {
        $tokenA = $this->issueToken($this->validHospitalUser);

        $reqB = BloodRequest::create([
            'hospital_id' => $this->otherHospital->id,
            'user_id' => $this->otherHospitalUser->id,
            'patient_id' => $this->hospitalBPatient->id,
            'patient_name' => $this->hospitalBPatient->name,
            'hospital' => $this->otherHospital->name,
            'city' => 'Metropolis',
            'blood_group' => 'O-',
            'units_needed' => 1,
            'urgency_level' => 'emergency',
            'status' => 'pending',
        ]);

        $response = $this->withToken($tokenA)->getJson("/api/v1/hospital/requisitions/{$reqB->id}");
        $response->assertStatus(404)
            ->assertJson(['message' => 'The requested resource was not found.']);
    }

    #[Test]
    public function nonexistent_requisition_detail_returns_404(): void
    {
        $token = $this->issueToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/requisitions/999999');
        $response->assertStatus(404)
            ->assertJson(['message' => 'The requested resource was not found.']);
    }

    #[Test]
    public function valid_requisition_can_be_created_with_server_controlled_identity_and_exact_reason(): void
    {
        Notification::fake();
        $token = $this->issueToken($this->validHospitalUser);

        $payload = [
            'patient_id' => $this->hospitalAPatient->id,
            'blood_group' => 'A+',
            'units_needed' => 2,
            'urgency_level' => 'urgent',
            'reason' => 'Scheduled elective procedure',
            'ward_name' => 'ICU',
            'room_number' => '101',
            'bed_number' => 'A1',
            'attendant_name' => 'John Doe',
            'attendant_phone' => '555-9876',
            'required_by' => now()->addDays(2)->format('Y-m-d H:i:s'),
            // Attempted client injection of protected fields
            'hospital_id' => 9999,
            'user_id' => 9999,
            'status' => 'approved',
            'patient_name' => 'Hacked Name',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/requisitions', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'blood_group' => 'A+',
                    'units_needed' => 2,
                    'urgency_level' => 'urgent',
                    'status' => 'pending', // Server controlled
                    'reason' => 'Scheduled elective procedure',
                    'ward_name' => 'ICU',
                    'room_number' => '101',
                    'bed_number' => 'A1',
                    'attendant_name' => 'John Doe',
                    'attendant_phone' => '555-9876',
                ],
            ]);

        $createdId = $response->json('data.id');
        $dbRecord = BloodRequest::find($createdId);

        $this->assertEquals($this->activeHospital->id, $dbRecord->hospital_id);
        $this->assertEquals($this->validHospitalUser->id, $dbRecord->user_id);
        $this->assertEquals($this->hospitalAPatient->id, $dbRecord->patient_id);
        $this->assertEquals($this->hospitalAPatient->name, $dbRecord->patient_name);
        $this->assertEquals('Scheduled elective procedure', $dbRecord->reason);
        $this->assertEquals('pending', $dbRecord->status);
    }

    #[Test]
    public function foreign_patient_id_is_rejected_on_creation(): void
    {
        $tokenA = $this->issueToken($this->validHospitalUser);

        $payload = [
            'patient_id' => $this->hospitalBPatient->id, // Patient from Hospital B
            'blood_group' => 'A+',
            'units_needed' => 2,
            'urgency_level' => 'urgent',
        ];

        $response = $this->withToken($tokenA)->postJson('/api/v1/hospital/requisitions', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['patient_id']);
    }

    #[Test]
    public function invalid_blood_group_units_bounds_or_urgency_fail_validation(): void
    {
        $token = $this->issueToken($this->validHospitalUser);

        $payload = [
            'patient_id' => $this->hospitalAPatient->id,
            'blood_group' => 'INVALID_GROUP',
            'units_needed' => 0, // Out of bounds min
            'urgency_level' => 'invalid_urgency',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/requisitions', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['blood_group', 'units_needed', 'urgency_level']);
    }

    #[Test]
    public function past_required_by_date_fails_validation(): void
    {
        $token = $this->issueToken($this->validHospitalUser);

        $payload = [
            'patient_id' => $this->hospitalAPatient->id,
            'blood_group' => 'A+',
            'units_needed' => 1,
            'urgency_level' => 'routine',
            'required_by' => now()->subDay()->format('Y-m-d H:i:s'), // Past date
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/requisitions', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['required_by']);
    }

    #[Test]
    public function each_requisition_field_remains_strictly_independent_and_unconcatenated(): void
    {
        Notification::fake();
        $token = $this->issueToken($this->validHospitalUser);

        $payload = [
            'patient_id' => $this->hospitalAPatient->id,
            'blood_group' => 'A+',
            'units_needed' => 2,
            'urgency_level' => 'urgent',
            'reason' => 'REASON-ONLY-TEST',
            'attendant_name' => 'ATTENDANT-ONLY-TEST',
            'attendant_phone' => 'PHONE-ONLY-TEST',
            'ward_name' => 'WARD-ONLY-TEST',
            'room_number' => 'ROOM-ONLY-TEST',
            'bed_number' => 'BED-ONLY-TEST',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/requisitions', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'reason' => 'REASON-ONLY-TEST',
                    'attendant_name' => 'ATTENDANT-ONLY-TEST',
                    'attendant_phone' => 'PHONE-ONLY-TEST',
                    'ward_name' => 'WARD-ONLY-TEST',
                    'room_number' => 'ROOM-ONLY-TEST',
                    'bed_number' => 'BED-ONLY-TEST',
                ],
            ]);

        $createdId = $response->json('data.id');
        $dbRecord = BloodRequest::findOrFail($createdId);

        $this->assertSame('REASON-ONLY-TEST', $dbRecord->reason);
        $this->assertSame('ATTENDANT-ONLY-TEST', $dbRecord->attendant_name);
        $this->assertSame('PHONE-ONLY-TEST', $dbRecord->attendant_phone);
        $this->assertSame('WARD-ONLY-TEST', $dbRecord->ward_name);
        $this->assertSame('ROOM-ONLY-TEST', $dbRecord->room_number);
        $this->assertSame('BED-ONLY-TEST', $dbRecord->bed_number);
    }
}
