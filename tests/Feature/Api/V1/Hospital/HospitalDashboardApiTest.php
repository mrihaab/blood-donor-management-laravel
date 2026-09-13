<?php

namespace Tests\Feature\Api\V1\Hospital;

use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HospitalDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected Hospital $activeHospital;
    protected Hospital $inactiveHospital;
    protected User $validHospitalUser;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->inactiveHospital = Hospital::create([
            'name' => 'Suspended Health Center',
            'license_number' => 'HOSP-INACTIVE-02',
            'address' => '200 Closed Rd',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Bob',
            'contact_phone' => '555-0200',
            'email' => 'contact@suspendedhosp.org',
            'status' => 'inactive',
        ]);

        $this->validHospitalUser = User::factory()->create([
            'name' => 'Dr. Sarah Connor',
            'email' => 'sarah.connor@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        // Seed canonical blood groups for lookup and requisition tests
        $groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        foreach ($groups as $name) {
            BloodGroup::firstOrCreate(['name' => $name], ['description' => "Blood type {$name}"]);
        }
    }

    /**
     * Private helper to instantiate schema-compliant BloodRequest records.
     */
    private function createBloodRequest(array $overrides = []): BloodRequest
    {
        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at']);

        $hospitalId = $overrides['hospital_id'] ?? $this->activeHospital->id;

        $userId = $overrides['user_id'] ?? null;
        if (!$userId) {
            if ($hospitalId === $this->activeHospital->id) {
                $userId = $this->validHospitalUser->id;
            } else {
                $userId = User::factory()->create([
                    'role' => 'hospital',
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'hospital_id' => $hospitalId,
                ])->id;
            }
        }

        $hospitalName = $overrides['hospital'] ?? ($hospitalId === $this->activeHospital->id ? $this->activeHospital->name : $this->inactiveHospital->name);

        $defaults = [
            'user_id' => $userId,
            'hospital_id' => $hospitalId,
            'patient_name' => 'Default Patient',
            'blood_group' => 'A+',
            'units_needed' => 1,
            'hospital' => $hospitalName,
            'city' => 'Metropolis',
            'status' => 'pending',
            'urgency_level' => 'routine',
        ];

        $request = BloodRequest::create(array_merge($defaults, $overrides));

        if ($createdAt !== null) {
            $request->created_at = $createdAt;
            $request->saveQuietly();
            $request->refresh();
        }

        return $request;
    }

    // 1. /dashboard without token returns JSON 401
    #[Test]
    public function dashboard_without_token_returns_json_401(): void
    {
        $response = $this->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    // 2. Token without hospital-mobile ability returns 403
    #[Test]
    public function token_without_hospital_mobile_ability_returns_403(): void
    {
        $wrongAbilityToken = $this->validHospitalUser->createToken('Device', ['donor-mobile'])->plainTextToken;

        $response = $this->withToken($wrongAbilityToken)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(403);
    }

    // 3. Inactive user is denied
    #[Test]
    public function inactive_user_is_denied(): void
    {
        $inactiveUser = User::factory()->create([
            'email' => 'inactive.doctor@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'inactive',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        $token = $inactiveUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 4. Unverified user is denied
    #[Test]
    public function unverified_user_is_denied(): void
    {
        $unverifiedUser = User::factory()->create([
            'email' => 'unverified.doctor@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => null,
            'hospital_id' => $this->activeHospital->id,
        ]);

        $token = $unverifiedUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 5. User associated with inactive hospital is denied
    #[Test]
    public function user_associated_with_inactive_hospital_is_denied(): void
    {
        $inactiveHospUser = User::factory()->create([
            'email' => 'staff@suspendedhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => $this->inactiveHospital->id,
        ]);

        $token = $inactiveHospUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 6. Active eligible hospital user receives 200
    #[Test]
    public function active_eligible_hospital_user_receives_200(): void
    {
        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
    }

    // 7. Empty hospital receives zero KPIs and empty recent list
    #[Test]
    public function empty_hospital_receives_zero_kpis_and_empty_recent_list(): void
    {
        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'hospital' => [
                        'id' => $this->activeHospital->id,
                        'name' => 'Central Clinical Hospital',
                        'license_number' => 'HOSP-ACTIVE-01',
                        'city' => 'Metropolis',
                        'status' => 'active',
                    ],
                    'kpis' => [
                        'total_patients' => 0,
                        'total_requisitions' => 0,
                        'pending_requisitions' => 0,
                        'approved_requisitions' => 0,
                        'dispensed_requisitions' => 0,
                    ],
                    'recent_requisitions' => [],
                ],
            ]);
    }

    // 8. Dashboard counts only current hospital patients
    #[Test]
    public function dashboard_counts_only_current_hospital_patients(): void
    {
        // 2 patients for active hospital
        Patient::create([
            'hospital_id' => $this->activeHospital->id,
            'name' => 'Patient 1',
            'mrn' => 'MRN-01',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
        ]);
        Patient::create([
            'hospital_id' => $this->activeHospital->id,
            'name' => 'Patient 2',
            'mrn' => 'MRN-02',
            'gender' => 'female',
            'date_of_birth' => '1992-02-02',
        ]);

        // 3 patients for inactive hospital
        Patient::create([
            'hospital_id' => $this->inactiveHospital->id,
            'name' => 'Other Patient 1',
            'mrn' => 'MRN-OTHER-01',
            'gender' => 'male',
            'date_of_birth' => '1985-05-05',
        ]);

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.kpis.total_patients'));
    }

    // 9. Dashboard counts only current hospital requisitions
    #[Test]
    public function dashboard_counts_only_current_hospital_requisitions(): void
    {
        // 3 requisitions for active hospital
        for ($i = 1; $i <= 3; $i++) {
            $this->createBloodRequest([
                'patient_name' => "Patient {$i}",
                'blood_group' => 'A+',
                'units_needed' => 1,
            ]);
        }

        // 2 requisitions for inactive hospital
        $this->createBloodRequest([
            'hospital_id' => $this->inactiveHospital->id,
            'patient_name' => 'Other Hosp Patient',
            'blood_group' => 'B+',
            'units_needed' => 2,
        ]);

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('data.kpis.total_requisitions'));
    }

    // 10. Every supported status KPI is calculated correctly
    #[Test]
    public function every_supported_status_kpi_is_calculated_correctly(): void
    {
        // 2 pending, 3 approved, 4 dispensed, 1 rejected for active hospital
        for ($i = 0; $i < 2; $i++) {
            $this->createBloodRequest([
                'patient_name' => 'Pending Patient',
                'blood_group' => 'A+',
                'status' => 'pending',
            ]);
        }
        for ($i = 0; $i < 3; $i++) {
            $this->createBloodRequest([
                'patient_name' => 'Approved Patient',
                'blood_group' => 'B+',
                'status' => 'approved',
            ]);
        }
        for ($i = 0; $i < 4; $i++) {
            $this->createBloodRequest([
                'patient_name' => 'Dispensed Patient',
                'blood_group' => 'O+',
                'status' => 'dispensed',
            ]);
        }
        $this->createBloodRequest([
            'patient_name' => 'Rejected Patient',
            'blood_group' => 'AB-',
            'status' => 'rejected',
        ]);

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $kpis = $response->json('data.kpis');
        $this->assertEquals(10, $kpis['total_requisitions']);
        $this->assertEquals(2, $kpis['pending_requisitions']);
        $this->assertEquals(3, $kpis['approved_requisitions']);
        $this->assertEquals(4, $kpis['dispensed_requisitions']);
    }

    // 11. Recent list contains maximum five records
    #[Test]
    public function recent_list_contains_maximum_five_records(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->createBloodRequest([
                'patient_name' => "Patient {$i}",
                'blood_group' => 'A+',
                'units_needed' => 1,
            ]);
        }

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.recent_requisitions'));
    }

    // 12. Recent list is ordered newest first
    #[Test]
    public function recent_list_is_ordered_newest_first(): void
    {
        $req1 = $this->createBloodRequest([
            'patient_name' => 'First Old Request',
            'blood_group' => 'A+',
            'created_at' => now()->subHours(5),
        ]);

        $req2 = $this->createBloodRequest([
            'patient_name' => 'Second Newer Request',
            'blood_group' => 'B+',
            'created_at' => now()->subHours(1),
        ]);

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $recent = $response->json('data.recent_requisitions');
        $this->assertEquals($req2->id, $recent[0]['id']);
        $this->assertEquals($req1->id, $recent[1]['id']);
    }

    // 12b. Recent list uses secondary ID descending tie-breaker when created_at timestamps are identical
    #[Test]
    public function recent_list_uses_secondary_id_descending_tie_breaker_when_timestamps_identical(): void
    {
        $sameTime = now()->subHours(2);

        $reqA = $this->createBloodRequest([
            'patient_name' => 'Request A (Older ID)',
            'created_at' => $sameTime,
        ]);

        $reqB = $this->createBloodRequest([
            'patient_name' => 'Request B (Newer ID)',
            'created_at' => $sameTime,
        ]);

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $recent = $response->json('data.recent_requisitions');
        $this->assertEquals($reqB->id, $recent[0]['id']);
        $this->assertEquals($reqA->id, $recent[1]['id']);
    }

    // 13. Another hospital's requisitions never appear
    #[Test]
    public function another_hospitals_requisitions_never_appear(): void
    {
        // Requisition for inactive/other hospital created very recently
        $this->createBloodRequest([
            'hospital_id' => $this->inactiveHospital->id,
            'patient_name' => 'Foreign Patient',
            'blood_group' => 'O-',
            'units_needed' => 4,
            'created_at' => now(),
        ]);

        // Requisition for active hospital
        $myReq = $this->createBloodRequest([
            'hospital_id' => $this->activeHospital->id,
            'patient_name' => 'My Patient',
            'blood_group' => 'A+',
            'units_needed' => 1,
            'created_at' => now()->subMinutes(10),
        ]);

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $recent = $response->json('data.recent_requisitions');
        $this->assertCount(1, $recent);
        $this->assertEquals($myReq->id, $recent[0]['id']);
    }

    // 14. Response has the exact approved top-level structure
    #[Test]
    public function response_has_exact_approved_top_level_structure(): void
    {
        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'hospital',
                    'kpis',
                    'recent_requisitions',
                ],
            ]);
    }

    // 15. Hospital response contains only approved fields
    #[Test]
    public function hospital_response_contains_only_approved_fields(): void
    {
        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $hospData = $response->json('data.hospital');
        $this->assertEquals(['id', 'name', 'license_number', 'city', 'status'], array_keys($hospData));
    }

    // 16. Recent requisition objects contain only approved fields
    #[Test]
    public function recent_requisition_objects_contain_only_approved_fields(): void
    {
        $this->createBloodRequest([
            'patient_name' => 'Test Patient',
            'blood_group' => 'A+',
            'units_needed' => 1,
            'urgency_level' => 'emergency',
            'status' => 'pending',
        ]);

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $reqData = $response->json('data.recent_requisitions.0');
        $this->assertEquals(
            ['id', 'patient_name', 'blood_group', 'units_needed', 'urgency_level', 'status', 'created_at'],
            array_keys($reqData)
        );
    }

    // 17. Sensitive patient/contact fields are absent
    #[Test]
    public function sensitive_patient_contact_fields_are_absent(): void
    {
        $patient = Patient::create([
            'hospital_id' => $this->activeHospital->id,
            'name' => 'Confidential Patient',
            'mrn' => 'MRN-SECRET-999',
            'gender' => 'female',
            'date_of_birth' => '1980-01-01',
            'contact_number' => '+155599988877',
        ]);

        $this->createBloodRequest([
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'blood_group' => 'O-',
            'units_needed' => 2,
            'urgency_level' => 'emergency',
            'status' => 'pending',
            'attendant_name' => 'Secret Attendant',
            'attendant_phone' => '+155511122233',
            'reason' => 'Confidential medical condition',
        ]);

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/dashboard');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringNotContainsString('MRN-SECRET-999', $content);
        $this->assertStringNotContainsString('+155599988877', $content);
        $this->assertStringNotContainsString('+155511122233', $content);
        $this->assertStringNotContainsString('Confidential medical condition', $content);
        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('remember_token', $content);
    }

    // 18. /blood-groups without authentication returns 401
    #[Test]
    public function blood_groups_without_authentication_returns_401(): void
    {
        $response = $this->getJson('/api/v1/hospital/blood-groups');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    // 19. /blood-groups without required token ability returns 403
    #[Test]
    public function blood_groups_without_required_token_ability_returns_403(): void
    {
        $wrongAbilityToken = $this->validHospitalUser->createToken('Device', ['donor-mobile'])->plainTextToken;

        $response = $this->withToken($wrongAbilityToken)
            ->getJson('/api/v1/hospital/blood-groups');

        $response->assertStatus(403);
    }

    // 20. /blood-groups returns canonical ordered values
    #[Test]
    public function blood_groups_returns_canonical_ordered_values(): void
    {
        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/blood-groups');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ]);

        $groups = $response->json('data');
        $this->assertCount(8, $groups);

        $names = array_column($groups, 'name');
        $this->assertEquals(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], $names);
    }

    // 20b. Blood group canonical ordering is independent of database primary key IDs
    #[Test]
    public function blood_group_canonical_ordering_is_independent_of_database_ids(): void
    {
        BloodGroup::query()->delete();

        // Create blood groups in reverse order so IDs do NOT match canonical clinical order
        $reverseGroups = ['O-', 'O+', 'AB-', 'AB+', 'B-', 'B+', 'A-', 'A+'];
        foreach ($reverseGroups as $name) {
            BloodGroup::create(['name' => $name, 'description' => "Type {$name}"]);
        }

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/blood-groups');

        $response->assertStatus(200);
        $names = array_column($response->json('data'), 'name');
        $this->assertEquals(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], $names);
    }

    // 20c. Non-canonical blood groups in database are excluded from lookup response
    #[Test]
    public function non_canonical_blood_groups_are_excluded_from_lookup_response(): void
    {
        BloodGroup::create(['name' => 'INVALID_GROUP', 'description' => 'Test invalid group']);
        BloodGroup::create(['name' => 'Rh-Null', 'description' => 'Test non-standard group']);

        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/blood-groups');

        $response->assertStatus(200);
        $groups = $response->json('data');
        $this->assertCount(8, $groups);

        $names = array_column($groups, 'name');
        $this->assertNotContains('INVALID_GROUP', $names);
        $this->assertNotContains('Rh-Null', $names);
    }

    // 21. Blood-group objects contain only approved fields
    #[Test]
    public function blood_group_objects_contain_only_approved_fields(): void
    {
        $token = $this->validHospitalUser->createToken('Device', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/blood-groups');

        $response->assertStatus(200);
        $groupObj = $response->json('data.0');
        $this->assertEquals(['id', 'name'], array_keys($groupObj));
    }

    // 22. Existing Stage B authentication endpoints remain registered
    #[Test]
    public function existing_stage_b_authentication_endpoints_remain_registered(): void
    {
        $loginResponse = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'sarah.connor@centralhosp.org',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);

        $token = $loginResponse->json('token');

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        $meResponse = $this->withToken($token)
            ->getJson('/api/v1/hospital/auth/me');

        $meResponse->assertStatus(200)
            ->assertJson(['user' => ['email' => 'sarah.connor@centralhosp.org']]);
    }

    // 23. Existing website dashboard behavior remains unaffected
    #[Test]
    public function existing_website_dashboard_behavior_remains_unaffected(): void
    {
        $response = $this->actingAs($this->validHospitalUser)
            ->get('/hospital/dashboard');

        $response->assertStatus(200)
            ->assertViewIs('hospital.dashboard');
    }
}
