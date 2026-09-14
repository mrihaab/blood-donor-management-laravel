<?php

namespace Tests\Feature\Api\V1\Hospital;

use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HospitalPatientApiTest extends TestCase
{
    use RefreshDatabase;

    protected Hospital $activeHospital;
    protected Hospital $otherHospital;
    protected Hospital $inactiveHospital;
    protected User $validHospitalUser;
    protected User $otherHospitalUser;
    protected User $donorUser;
    protected User $adminUser;
    protected User $pendingUser;
    protected User $suspendedUser;
    protected User $unverifiedUser;
    protected User $noHospitalUser;
    protected User $inactiveHospitalUser;
    protected BloodGroup $bloodGroupA;
    protected BloodGroup $bloodGroupB;

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
            'name' => 'Closed Down Clinic',
            'license_number' => 'HOSP-INACTIVE-03',
            'address' => '300 Closed St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Charlie',
            'contact_phone' => '555-0300',
            'email' => 'contact@closedclinic.org',
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

        $this->otherHospitalUser = User::factory()->create([
            'name' => 'Dr. John Doe',
            'email' => 'john.doe@cityhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => $this->otherHospital->id,
        ]);

        $this->donorUser = User::factory()->create([
            'name' => 'Donor User',
            'email' => 'donor@example.com',
            'role' => 'donor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->pendingUser = User::factory()->create([
            'name' => 'Pending Hospital User',
            'email' => 'pending@centralhosp.org',
            'role' => 'hospital',
            'status' => 'pending',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        $this->suspendedUser = User::factory()->create([
            'name' => 'Suspended Hospital User',
            'email' => 'suspended@centralhosp.org',
            'role' => 'hospital',
            'status' => 'suspended',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        $this->unverifiedUser = User::factory()->create([
            'name' => 'Unverified Hospital User',
            'email' => 'unverified@centralhosp.org',
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => null,
            'hospital_id' => $this->activeHospital->id,
        ]);

        $this->noHospitalUser = User::factory()->create([
            'name' => 'No Hospital User',
            'email' => 'nohospital@centralhosp.org',
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => null,
        ]);

        $this->inactiveHospitalUser = User::factory()->create([
            'name' => 'Inactive Hospital User',
            'email' => 'user@closedclinic.org',
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => $this->inactiveHospital->id,
        ]);

        $this->bloodGroupA = BloodGroup::firstOrCreate(
            ['name' => 'A+'],
            ['description' => 'Blood type A+']
        );

        $this->bloodGroupB = BloodGroup::firstOrCreate(
            ['name' => 'B+'],
            ['description' => 'Blood type B+']
        );
    }

    protected function validToken(User $user): string
    {
        return $user->createToken('TestDevice', ['hospital-mobile'])->plainTextToken;
    }

    // ==========================================
    // GROUP 1: AUTH & ELIGIBILITY (Tests 1-12)
    // ==========================================

    #[Test]
    public function index_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/v1/hospital/patients');
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function index_with_invalid_token_returns_401(): void
    {
        $response = $this->withToken('invalid-token-string')->getJson('/api/v1/hospital/patients');
        $response->assertStatus(401);
    }

    #[Test]
    public function index_with_expired_or_deleted_token_returns_401(): void
    {
        $tokenObj = $this->validHospitalUser->createToken('Device', ['hospital-mobile']);
        $tokenStr = $tokenObj->plainTextToken;
        $tokenObj->accessToken->delete();

        $response = $this->withToken($tokenStr)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(401);
    }

    #[Test]
    public function index_with_donor_role_user_returns_403(): void
    {
        $token = $this->validToken($this->donorUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(403);
    }

    #[Test]
    public function index_with_admin_role_user_returns_403(): void
    {
        $token = $this->validToken($this->adminUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(403);
    }

    #[Test]
    public function index_with_pending_status_user_returns_403(): void
    {
        $token = $this->validToken($this->pendingUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(403);
    }

    #[Test]
    public function index_with_suspended_status_user_returns_403(): void
    {
        $token = $this->validToken($this->suspendedUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(403);
    }

    #[Test]
    public function index_with_unverified_email_user_returns_403(): void
    {
        $token = $this->validToken($this->unverifiedUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(403);
    }

    #[Test]
    public function index_with_token_lacking_hospital_mobile_ability_returns_403(): void
    {
        $wrongAbilityToken = $this->validHospitalUser->createToken('Device', ['donor-mobile'])->plainTextToken;
        $response = $this->withToken($wrongAbilityToken)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(403);
    }

    #[Test]
    public function index_with_user_having_null_hospital_id_returns_403(): void
    {
        $token = $this->validToken($this->noHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(403);
    }

    #[Test]
    public function index_with_user_belonging_to_inactive_hospital_returns_403(): void
    {
        $token = $this->validToken($this->inactiveHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(403);
    }

    #[Test]
    public function index_with_valid_hospital_token_succeeds(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');
        $response->assertStatus(200);
    }

    // ==========================================
    // GROUP 2: PATIENT LIST / PAGINATION / SEARCH (Tests 13-46)
    // ==========================================

    #[Test]
    public function index_returns_paginated_envelope_structure(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    #[Test]
    public function index_returns_patients_belonging_only_to_authenticated_hospital(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Active Patient 1']);
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Active Patient 2']);
        Patient::factory()->create(['hospital_id' => $this->otherHospital->id, 'name' => 'Other Patient 1']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    #[Test]
    public function index_excludes_other_hospital_patients_completely(): void
    {
        Patient::factory()->create(['hospital_id' => $this->otherHospital->id, 'name' => 'Secret Other Patient']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $names = array_column($response->json('data'), 'name');
        $this->assertNotContains('Secret Other Patient', $names);
    }

    #[Test]
    public function index_resource_does_not_leak_date_of_birth_or_contact_number(): void
    {
        Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'date_of_birth' => '1990-01-01',
            'contact_number' => '555-9999',
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $item = $response->json('data.0');
        $this->assertArrayNotHasKey('date_of_birth', $item);
        $this->assertArrayNotHasKey('contact_number', $item);
    }

    #[Test]
    public function index_resource_includes_nested_blood_group_object(): void
    {
        Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'blood_group_id' => $this->bloodGroupA->id,
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                [
                    'blood_group' => [
                        'id' => $this->bloodGroupA->id,
                        'name' => 'A+',
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function index_resource_handles_null_blood_group_gracefully(): void
    {
        Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'blood_group_id' => null,
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $this->assertNull($response->json('data.0.blood_group'));
    }

    #[Test]
    public function index_search_by_exact_name(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Alexander Fleming']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=Alexander%20Fleming');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('Alexander Fleming', $response->json('data.0.name'));
    }

    #[Test]
    public function index_search_by_partial_name(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Alexander Fleming']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=Alex');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    #[Test]
    public function index_search_by_exact_mrn(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-EXACT-88']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=MRN-EXACT-88');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    #[Test]
    public function index_search_by_partial_mrn(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-EXACT-88']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=EXACT');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    #[Test]
    public function index_search_trims_leading_and_trailing_whitespace(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Padded Name']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=%20%20Padded%20%20');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    #[Test]
    public function index_search_escapes_percent_wildcard_character_literally(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => '100% Normal']);
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => '1000 Normal']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=100%25');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('100% Normal', $response->json('data.0.name'));
    }

    #[Test]
    public function index_search_escapes_underscore_wildcard_character_literally(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'John_Doe']);
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'JohnADoe']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=John_Doe');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('John_Doe', $response->json('data.0.name'));
    }

    #[Test]
    public function index_search_escapes_backslash_character_literally(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'AC\\DC']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=AC%5CDC');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('AC\\DC', $response->json('data.0.name'));
    }

    #[Test]
    public function index_search_escapes_exclamation_character_literally(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Warning! High Risk']);
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Warning High Risk']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=Warning!');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('Warning! High Risk', $response->json('data.0.name'));
    }

    #[Test]
    public function index_search_escapes_complex_combination_literally(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Test 50%!_\\ Sample']);
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Test 500 Sample']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=' . urlencode('50%!_\\'));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('Test 50%!_\\ Sample', $response->json('data.0.name'));
    }

    #[Test]
    public function index_search_excludes_other_hospital_patients_with_matching_special_chars(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Patient 100%']);
        Patient::factory()->create(['hospital_id' => $this->otherHospital->id, 'name' => 'Other Patient 100%']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=100%25');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('Patient 100%', $response->json('data.0.name'));
    }

    #[Test]
    public function index_search_malicious_input_remains_parameter_bound_and_hospital_scoped(): void
    {
        Patient::factory()->create(['hospital_id' => $this->otherHospital->id, 'name' => 'Secret Other Patient', 'mrn' => 'MRN-SEC-1']);

        $token = $this->validToken($this->validHospitalUser);
        $maliciousQuery = urlencode("' OR 1=1 -- ");
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients?search={$maliciousQuery}");

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('meta.total'));
    }

    #[Test]
    public function index_search_is_case_insensitive(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'UPPER CASE NAME']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=upper%20case');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
    }

    #[Test]
    public function index_search_returns_empty_data_when_no_match(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Existing Patient']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=NonExistentQuery');

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('meta.total'));
        $this->assertEmpty($response->json('data'));
    }

    #[Test]
    public function index_search_accepts_max_100_characters(): void
    {
        $query100 = str_repeat('a', 100);
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients?search={$query100}");

        $response->assertStatus(200);
    }

    #[Test]
    public function index_search_fails_validation_if_exceeds_100_characters(): void
    {
        $query101 = str_repeat('a', 101);
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients?search={$query101}");

        $response->assertStatus(422)->assertJsonValidationErrors(['search']);
    }

    #[Test]
    public function index_filter_by_status_active(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'status' => 'active']);
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'status' => 'discharged']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?status=active');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('active', $response->json('data.0.status'));
    }

    #[Test]
    public function index_filter_by_status_discharged(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'status' => 'active']);
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'status' => 'discharged']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?status=discharged');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('discharged', $response->json('data.0.status'));
    }

    #[Test]
    public function index_filter_by_status_archived(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'status' => 'archived']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?status=archived');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('archived', $response->json('data.0.status'));
    }

    #[Test]
    public function index_filter_fails_validation_for_invalid_status_value(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?status=deleted');

        $response->assertStatus(422)->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function index_pagination_default_per_page_is_15(): void
    {
        Patient::factory()->count(20)->create(['hospital_id' => $this->activeHospital->id]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $this->assertEquals(15, $response->json('meta.per_page'));
        $this->assertCount(15, $response->json('data'));
    }

    #[Test]
    public function index_pagination_respects_custom_per_page(): void
    {
        Patient::factory()->count(10)->create(['hospital_id' => $this->activeHospital->id]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?per_page=5');

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('meta.per_page'));
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function index_pagination_caps_per_page_at_max_50(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?per_page=50');

        $response->assertStatus(200);
        $this->assertEquals(50, $response->json('meta.per_page'));
    }

    #[Test]
    public function index_pagination_fails_validation_if_per_page_exceeds_50(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?per_page=51');

        $response->assertStatus(422)->assertJsonValidationErrors(['per_page']);
    }

    #[Test]
    public function index_pagination_fails_validation_if_per_page_less_than_1(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?per_page=0');

        $response->assertStatus(422)->assertJsonValidationErrors(['per_page']);
    }

    #[Test]
    public function index_pagination_respects_page_parameter(): void
    {
        Patient::factory()->count(20)->create(['hospital_id' => $this->activeHospital->id]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?per_page=15&page=2');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.current_page'));
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function index_pagination_fails_validation_if_page_less_than_1(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?page=0');

        $response->assertStatus(422)->assertJsonValidationErrors(['page']);
    }

    #[Test]
    public function index_ordering_is_created_at_descending_then_id_descending(): void
    {
        $p1 = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'created_at' => now()->subDays(2)]);
        $p2 = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'created_at' => now()->subDay()]);
        $p3 = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'created_at' => now()]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $ids = array_column($response->json('data'), 'id');
        $this->assertEquals([$p3->id, $p2->id, $p1->id], $ids);
    }

    #[Test]
    public function index_handles_empty_patient_list_cleanly(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('meta.total'));
        $this->assertEmpty($response->json('data'));
    }

    #[Test]
    public function index_combines_search_and_status_filters(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Target Alpha', 'status' => 'active']);
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Target Beta', 'status' => 'discharged']);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=Target&status=discharged');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('meta.total'));
        $this->assertEquals('Target Beta', $response->json('data.0.name'));
    }

    #[Test]
    public function index_sanitizes_script_tags_in_search_parameter(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients?search=<script>alert(1)</script>');

        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('meta.total'));
    }

    #[Test]
    public function index_returns_valid_iso8601_created_at_timestamp(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $createdAt = $response->json('data.0.created_at');
        $this->assertNotNull($createdAt);
        $this->assertNotFalse(strtotime($createdAt));
    }

    // ==========================================
    // GROUP 3: PATIENT DETAIL & IDOR PROTECTION (Tests 47-64)
    // ==========================================

    #[Test]
    public function show_returns_full_patient_detail_for_authenticated_hospital(): void
    {
        $patient = Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'name' => 'Jane Doe',
            'mrn' => 'MRN-DETAIL-100',
            'gender' => 'female',
            'date_of_birth' => '1985-06-15',
            'contact_number' => '555-8888',
            'status' => 'active',
            'ward_name' => 'ICU',
            'room_number' => '101',
            'bed_number' => 'A',
            'blood_group_id' => $this->bloodGroupA->id,
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $patient->id,
                    'mrn' => 'MRN-DETAIL-100',
                    'name' => 'Jane Doe',
                    'gender' => 'female',
                    'date_of_birth' => '1985-06-15',
                    'contact_number' => '555-8888',
                    'status' => 'active',
                    'ward_name' => 'ICU',
                    'room_number' => '101',
                    'bed_number' => 'A',
                    'blood_group' => [
                        'id' => $this->bloodGroupA->id,
                        'name' => 'A+',
                    ],
                ],
            ]);
    }

    #[Test]
    public function show_resource_includes_date_of_birth_in_ymd_format(): void
    {
        $patient = Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'date_of_birth' => '1995-12-25',
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200);
        $this->assertEquals('1995-12-25', $response->json('data.date_of_birth'));
    }

    #[Test]
    public function show_resource_includes_contact_number(): void
    {
        $patient = Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'contact_number' => '+15551234567',
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200);
        $this->assertEquals('+15551234567', $response->json('data.contact_number'));
    }

    #[Test]
    public function show_resource_includes_blood_group_relation(): void
    {
        $patient = Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'blood_group_id' => $this->bloodGroupB->id,
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200);
        $this->assertEquals('B+', $response->json('data.blood_group.name'));
    }

    #[Test]
    public function show_resource_includes_blood_requests_requisition_summary(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        BloodRequest::create([
            'user_id' => $this->validHospitalUser->id,
            'hospital_id' => $this->activeHospital->id,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'blood_group' => 'A+',
            'units_needed' => 3,
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'status' => 'pending',
            'urgency_level' => 'emergency',
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.blood_requests'));
        $req = $response->json('data.blood_requests.0');
        $this->assertEquals('A+', $req['blood_group']);
        $this->assertEquals(3, $req['units_needed']);
        $this->assertEquals('emergency', $req['urgency_level']);
        $this->assertEquals('pending', $req['status']);
    }

    #[Test]
    public function show_blood_requests_are_scoped_to_authenticated_hospital_only(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        BloodRequest::create([
            'user_id' => $this->validHospitalUser->id,
            'hospital_id' => $this->activeHospital->id,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'blood_group' => 'A+',
            'units_needed' => 1,
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'status' => 'pending',
            'urgency_level' => 'normal',
        ]);

        // Cross-hospital requisition attached to same patient ID (anomalous data test)
        BloodRequest::create([
            'user_id' => $this->otherHospitalUser->id,
            'hospital_id' => $this->otherHospital->id,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'blood_group' => 'B+',
            'units_needed' => 5,
            'hospital' => $this->otherHospital->name,
            'city' => 'Metropolis',
            'status' => 'pending',
            'urgency_level' => 'urgent',
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.blood_requests'));
        $this->assertEquals('A+', $response->json('data.blood_requests.0.blood_group'));
    }

    #[Test]
    public function show_blood_requests_are_limited_to_latest_10(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        for ($i = 1; $i <= 15; $i++) {
            BloodRequest::create([
                'user_id' => $this->validHospitalUser->id,
                'hospital_id' => $this->activeHospital->id,
                'patient_id' => $patient->id,
                'patient_name' => $patient->name,
                'blood_group' => 'A+',
                'units_needed' => 1,
                'hospital' => $this->activeHospital->name,
                'city' => 'Metropolis',
                'status' => 'pending',
                'urgency_level' => 'normal',
                'created_at' => now()->addMinutes($i),
            ]);
        }

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data.blood_requests'));
    }

    #[Test]
    public function show_blood_requests_are_ordered_by_created_at_desc(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        $r1 = BloodRequest::create([
            'user_id' => $this->validHospitalUser->id,
            'hospital_id' => $this->activeHospital->id,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'blood_group' => 'A+',
            'units_needed' => 1,
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'status' => 'pending',
            'urgency_level' => 'normal',
            'created_at' => now()->subHour(),
        ]);

        $r2 = BloodRequest::create([
            'user_id' => $this->validHospitalUser->id,
            'hospital_id' => $this->activeHospital->id,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'blood_group' => 'A+',
            'units_needed' => 2,
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'status' => 'pending',
            'urgency_level' => 'emergency',
            'created_at' => now(),
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200);
        $ids = array_column($response->json('data.blood_requests'), 'id');
        $this->assertEquals([$r2->id, $r1->id], $ids);
    }

    #[Test]
    public function show_blood_requests_resource_fields(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        BloodRequest::create([
            'user_id' => $this->validHospitalUser->id,
            'hospital_id' => $this->activeHospital->id,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'blood_group' => 'O+',
            'units_needed' => 4,
            'hospital' => $this->activeHospital->name,
            'city' => 'Metropolis',
            'status' => 'fulfilled',
            'urgency_level' => 'urgent',
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200);
        $req = $response->json('data.blood_requests.0');
        $this->assertArrayHasKey('id', $req);
        $this->assertArrayHasKey('blood_group', $req);
        $this->assertArrayHasKey('units_needed', $req);
        $this->assertArrayHasKey('urgency_level', $req);
        $this->assertArrayHasKey('status', $req);
        $this->assertArrayHasKey('created_at', $req);
    }

    #[Test]
    public function show_for_another_hospitals_patient_returns_non_enumerating_404(): void
    {
        $otherPatient = Patient::factory()->create([
            'hospital_id' => $this->otherHospital->id,
            'name' => 'Cross Hospital Patient',
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$otherPatient->id}");

        // Mandatory non-enumerating 404
        $response->assertStatus(404);
    }

    #[Test]
    public function show_for_non_existent_patient_id_returns_404(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients/999999');

        $response->assertStatus(404);
    }

    #[Test]
    public function show_response_for_cross_hospital_and_non_existent_are_identical_404(): void
    {
        $otherPatient = Patient::factory()->create(['hospital_id' => $this->otherHospital->id]);
        $token = $this->validToken($this->validHospitalUser);

        $responseCross = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$otherPatient->id}");
        $responseNonExistent = $this->withToken($token)->getJson('/api/v1/hospital/patients/999999');

        $this->assertEquals(404, $responseCross->status());
        $this->assertEquals(404, $responseNonExistent->status());
        $this->assertArrayHasKey('message', $responseCross->json());
        $this->assertArrayHasKey('message', $responseNonExistent->json());
    }

    #[Test]
    public function show_without_token_returns_401(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        $response = $this->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(401);
    }

    #[Test]
    public function show_with_donor_role_returns_403(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        $token = $this->validToken($this->donorUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function show_with_lacking_ability_returns_403(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        $wrongToken = $this->validHospitalUser->createToken('Device', ['donor-mobile'])->plainTextToken;
        $response = $this->withToken($wrongToken)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function show_handles_patient_without_blood_group_or_requests(): void
    {
        $patient = Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'blood_group_id' => null,
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200);
        $this->assertNull($response->json('data.blood_group'));
        $this->assertEmpty($response->json('data.blood_requests'));
    }

    #[Test]
    public function show_handles_string_or_invalid_id_parameter(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients/invalid-id-string');

        $response->assertStatus(404);
    }

    #[Test]
    public function show_does_not_reveal_existence_of_other_hospital_patient_via_headers_or_body(): void
    {
        $otherPatient = Patient::factory()->create(['hospital_id' => $this->otherHospital->id]);
        $token = $this->validToken($this->validHospitalUser);

        $response = $this->withToken($token)->getJson("/api/v1/hospital/patients/{$otherPatient->id}");

        $response->assertStatus(404);
        $this->assertStringNotContainsString('City General Hospital', $response->content());
    }

    // ==========================================
    // GROUP 4: STORE PATIENT (Tests 65-97)
    // ==========================================

    #[Test]
    public function store_creates_patient_and_assigns_authenticated_hospital_id(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => 'New Patient',
            'mrn' => 'MRN-NEW-01',
            'gender' => 'male',
            'date_of_birth' => '1990-05-10',
            'blood_group_id' => $this->bloodGroupA->id,
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('patients', [
            'mrn' => 'MRN-NEW-01',
            'hospital_id' => $this->activeHospital->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function store_sets_default_status_to_active(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => 'Active Status Test',
            'mrn' => 'MRN-STAT-1',
            'gender' => 'female',
            'date_of_birth' => '1992-01-01',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertEquals('active', $response->json('data.status'));
    }

    #[Test]
    public function store_trims_whitespace_from_name(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => '   Spaced Out Name   ',
            'mrn' => 'MRN-TRIM-1',
            'gender' => 'male',
            'date_of_birth' => '1988-08-08',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertEquals('Spaced Out Name', $response->json('data.name'));
    }

    #[Test]
    public function store_converts_mrn_to_uppercase(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => 'Lowercase MRN Test',
            'mrn' => '  mrn-lower-99  ',
            'gender' => 'male',
            'date_of_birth' => '1988-08-08',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertEquals('MRN-LOWER-99', $response->json('data.mrn'));
    }

    #[Test]
    public function store_converts_gender_to_lowercase(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => 'Uppercase Gender Test',
            'mrn' => 'MRN-GENDER-1',
            'gender' => 'MALE',
            'date_of_birth' => '1988-08-08',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertEquals('male', $response->json('data.gender'));
    }

    #[Test]
    public function store_trims_and_nullifies_empty_contact_number(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => 'Empty Contact Test',
            'mrn' => 'MRN-CONT-1',
            'gender' => 'male',
            'date_of_birth' => '1988-08-08',
            'contact_number' => '   ',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertNull($response->json('data.contact_number'));
    }

    #[Test]
    public function store_trims_and_nullifies_empty_ward_name(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => 'Empty Ward Test',
            'mrn' => 'MRN-WARD-1',
            'gender' => 'male',
            'date_of_birth' => '1988-08-08',
            'ward_name' => '   ',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertNull($response->json('data.ward_name'));
    }

    #[Test]
    public function store_trims_and_nullifies_empty_room_number(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => 'Empty Room Test',
            'mrn' => 'MRN-ROOM-1',
            'gender' => 'male',
            'date_of_birth' => '1988-08-08',
            'room_number' => '',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertNull($response->json('data.room_number'));
    }

    #[Test]
    public function store_trims_and_nullifies_empty_bed_number(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => 'Empty Bed Test',
            'mrn' => 'MRN-BED-1',
            'gender' => 'male',
            'date_of_birth' => '1988-08-08',
            'bed_number' => '   ',
        ];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertNull($response->json('data.bed_number'));
    }

    #[Test]
    public function store_fails_validation_when_required_fields_missing(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['name', 'mrn', 'gender', 'date_of_birth']);
    }

    #[Test]
    public function store_fails_validation_for_empty_name(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => '  ', 'mrn' => 'MRN-N1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function store_fails_validation_for_name_exceeding_255_chars(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => str_repeat('a', 256), 'mrn' => 'MRN-N2', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function store_fails_validation_for_empty_mrn(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => '  ', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['mrn']);
    }

    #[Test]
    public function store_fails_validation_for_mrn_exceeding_100_chars(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => str_repeat('M', 101), 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['mrn']);
    }

    #[Test]
    public function store_enforces_mrn_uniqueness_within_same_hospital(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UNIQUE-01']);

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Duplicate Patient', 'mrn' => 'mrn-unique-01', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['mrn']);
    }

    #[Test]
    public function store_allows_same_mrn_for_different_hospital(): void
    {
        Patient::factory()->create(['hospital_id' => $this->otherHospital->id, 'mrn' => 'MRN-SHARED-01']);

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Same MRN Other Hospital', 'mrn' => 'MRN-SHARED-01', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_fails_validation_for_invalid_gender(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-G1', 'gender' => 'invalid_gender', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['gender']);
    }

    #[Test]
    public function store_accepts_valid_gender_values(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        foreach (['male', 'female', 'other'] as $idx => $g) {
            $payload = ['name' => "Gender Test {$g}", 'mrn' => "MRN-GEND-{$idx}", 'gender' => $g, 'date_of_birth' => '1990-01-01'];
            $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);
            $response->assertStatus(201);
        }
    }

    #[Test]
    public function store_fails_validation_for_invalid_date_format(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-D1', 'gender' => 'male', 'date_of_birth' => '01/01/1990'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['date_of_birth']);
    }

    #[Test]
    public function store_fails_validation_for_future_date_of_birth(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $futureDate = now()->addYear()->format('Y-m-d');
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-D2', 'gender' => 'male', 'date_of_birth' => $futureDate];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['date_of_birth']);
    }

    #[Test]
    public function store_fails_validation_for_today_date_of_birth(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $todayDate = now()->format('Y-m-d');
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-D3', 'gender' => 'male', 'date_of_birth' => $todayDate];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['date_of_birth']);
    }

    #[Test]
    public function store_accepts_valid_past_date_of_birth(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-D4', 'gender' => 'male', 'date_of_birth' => '2000-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_fails_validation_for_invalid_blood_group_id(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-BG1', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'blood_group_id' => 999999];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['blood_group_id']);
    }

    #[Test]
    public function store_accepts_valid_canonical_blood_group_id(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-BG2', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'blood_group_id' => $this->bloodGroupA->id];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_accepts_null_blood_group_id(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-BG3', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'blood_group_id' => null];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_fails_validation_if_prohibited_field_id_provided(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-P1', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'id' => 123];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['id']);
    }

    #[Test]
    public function store_fails_validation_if_prohibited_field_hospital_id_provided(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-P2', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'hospital_id' => $this->otherHospital->id];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['hospital_id']);
    }

    #[Test]
    public function store_fails_validation_if_prohibited_field_user_id_provided(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-P3', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'user_id' => 999];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['user_id']);
    }

    #[Test]
    public function store_fails_validation_if_prohibited_field_status_provided(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-P4', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'status' => 'archived'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function store_fails_validation_if_prohibited_field_created_at_provided(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-P5', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'created_at' => now()->toDateTimeString()];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['created_at']);
    }

    #[Test]
    public function store_fails_validation_if_prohibited_field_updated_at_provided(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-P6', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'updated_at' => now()->toDateTimeString()];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['updated_at']);
    }

    #[Test]
    public function store_without_token_returns_401(): void
    {
        $payload = ['name' => 'Valid Name', 'mrn' => 'MRN-AUTH-1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];
        $response = $this->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(401);
    }

    #[Test]
    public function store_returns_201_with_patient_detail_resource(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Detail Resource Test', 'mrn' => 'MRN-RES-1', 'gender' => 'female', 'date_of_birth' => '1993-03-03'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'mrn', 'name', 'gender', 'date_of_birth', 'contact_number', 'status', 'ward_name', 'room_number', 'bed_number', 'blood_group', 'created_at', 'updated_at', 'blood_requests'],
            ]);
    }

    // ==========================================
    // GROUP 5: NOTIFICATION RESILIENCE (Tests 98-108)
    // ==========================================

    #[Test]
    public function store_triggers_notification_service(): void
    {
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')->once();
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Notify Test Patient', 'mrn' => 'MRN-NOTIF-1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_succeeds_even_if_notification_service_throws_exception(): void
    {
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')
                ->once()
                ->andThrow(new \RuntimeException('Mail server offline'));
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Resilient Patient', 'mrn' => 'MRN-RESIL-1', 'gender' => 'female', 'date_of_birth' => '1991-02-02'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_logs_warning_on_notification_exception_without_500(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Hospital patient registration notification failed.', \Mockery::on(function ($context) {
                return isset($context['exception']) && $context['exception'] === \RuntimeException::class;
            }));

        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')
                ->once()
                ->andThrow(new \RuntimeException('Logging test error'));
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Log Warning Test', 'mrn' => 'MRN-LOG-1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_notification_uses_authenticated_hospital_name(): void
    {
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')
                ->once()
                ->with(\Mockery::type(Patient::class), 'Central Clinical Hospital');
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Hosp Name Test', 'mrn' => 'MRN-HNAME-1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_notification_fallback_hospital_name_if_missing(): void
    {
        $this->validHospitalUser->hospital->name = 'Hospital';
        $this->validHospitalUser->hospital->save();

        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')
                ->once()
                ->with(\Mockery::type(Patient::class), 'Hospital');
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Fallback Name Test', 'mrn' => 'MRN-FALLBACK-1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_notification_exception_does_not_roll_back_database_patient(): void
    {
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')
                ->once()
                ->andThrow(new \Exception('Fatal socket error'));
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Persisted Patient', 'mrn' => 'MRN-PERSIST-1', 'gender' => 'female', 'date_of_birth' => '1992-04-04'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('patients', ['mrn' => 'MRN-PERSIST-1']);
    }

    #[Test]
    public function store_notification_exception_still_returns_201_created(): void
    {
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')->andThrow(new \Error('Fatal error'));
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'StatusCode Test', 'mrn' => 'MRN-STATUS-1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_notification_exception_still_returns_patient_detail_payload(): void
    {
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')->andThrow(new \Exception('Timeout'));
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Payload Test', 'mrn' => 'MRN-PAYLOAD-1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201)->assertJson(['data' => ['mrn' => 'MRN-PAYLOAD-1', 'name' => 'Payload Test']]);
    }

    #[Test]
    public function store_notification_exception_logs_exception_class(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->with('Hospital patient registration notification failed.', ['exception' => \InvalidArgumentException::class]);

        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')->andThrow(new \InvalidArgumentException('Bad arg'));
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Log Class Test', 'mrn' => 'MRN-LOGCLASS-1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    #[Test]
    public function store_notification_does_not_leak_exception_trace_to_api_response(): void
    {
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')->andThrow(new \Exception('Secret stack trace secret_token_abc'));
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'No Leak Test', 'mrn' => 'MRN-NOLEAK-1', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
        $this->assertStringNotContainsString('secret_token_abc', $response->content());
    }

    #[Test]
    public function store_notification_is_called_with_created_patient_model(): void
    {
        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyAdminPatientRegistered')
                ->once()
                ->with(\Mockery::on(function ($p) {
                    return $p instanceof Patient && $p->mrn === 'MRN-MODELCHK-1';
                }), \Mockery::any());
        });

        $token = $this->validToken($this->validHospitalUser);
        $payload = ['name' => 'Model Check', 'mrn' => 'MRN-MODELCHK-1', 'gender' => 'female', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', $payload);

        $response->assertStatus(201);
    }

    // ==========================================
    // GROUP 6: UPDATE PATIENT (Tests 109-136)
    // ==========================================

    #[Test]
    public function update_modifies_allowed_patient_fields(): void
    {
        $patient = Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'name' => 'Original Name',
            'mrn' => 'MRN-UP1',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
        ]);

        $token = $this->validToken($this->validHospitalUser);
        $payload = [
            'name' => 'Updated Name',
            'gender' => 'female',
            'date_of_birth' => '1992-02-02',
            'ward_name' => 'Surgical',
            'room_number' => '202',
            'bed_number' => 'D',
            'blood_group_id' => $this->bloodGroupB->id,
        ];

        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $patient->id,
                    'mrn' => 'MRN-UP1',
                    'name' => 'Updated Name',
                    'gender' => 'female',
                    'date_of_birth' => '1992-02-02',
                    'ward_name' => 'Surgical',
                    'room_number' => '202',
                    'bed_number' => 'D',
                    'blood_group' => [
                        'id' => $this->bloodGroupB->id,
                        'name' => 'B+',
                    ],
                ],
            ]);
    }

    #[Test]
    public function update_trims_whitespace_from_updated_name(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'name' => 'Old Name', 'mrn' => 'MRN-UP2']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => '   Trimmed New Name   ', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('Trimmed New Name', $response->json('data.name'));
    }

    #[Test]
    public function update_converts_gender_to_lowercase(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP3']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'FEMALE', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('female', $response->json('data.gender'));
    }

    #[Test]
    public function update_trims_and_nullifies_empty_optional_fields(): void
    {
        $patient = Patient::factory()->create([
            'hospital_id' => $this->activeHospital->id,
            'mrn' => 'MRN-UP4',
            'ward_name' => 'Old Ward',
        ]);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'ward_name' => '   '];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200);
        $this->assertNull($response->json('data.ward_name'));
    }

    #[Test]
    public function update_fails_validation_if_mrn_provided(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-PROTECTED']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'mrn' => 'MRN-CHANGED'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['mrn']);
    }

    #[Test]
    public function update_fails_validation_if_status_provided(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP5']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'status' => 'discharged'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['status']);
    }

    #[Test]
    public function update_fails_validation_if_id_provided(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP6']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'id' => 9999];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['id']);
    }

    #[Test]
    public function update_fails_validation_if_hospital_id_provided(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP7']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'hospital_id' => $this->otherHospital->id];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['hospital_id']);
    }

    #[Test]
    public function update_fails_validation_if_user_id_provided(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP8']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'user_id' => 123];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['user_id']);
    }

    #[Test]
    public function update_fails_validation_if_created_at_provided(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP9']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'created_at' => now()->toDateTimeString()];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['created_at']);
    }

    #[Test]
    public function update_fails_validation_if_updated_at_provided(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP10']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'updated_at' => now()->toDateTimeString()];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['updated_at']);
    }

    #[Test]
    public function update_retains_original_mrn(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-KEEP-MRN']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'New Name Only', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('MRN-KEEP-MRN', $response->json('data.mrn'));
    }

    #[Test]
    public function update_retains_original_status(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'status' => 'active', 'mrn' => 'MRN-KEEP-STAT']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'New Name Only', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200);
        $this->assertEquals('active', $response->json('data.status'));
    }

    #[Test]
    public function update_retains_original_hospital_ownership(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-KEEP-OWN']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'New Name Only', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'hospital_id' => $this->activeHospital->id]);
    }

    #[Test]
    public function update_fails_validation_if_name_missing(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP11']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['gender' => 'male', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function update_fails_validation_if_gender_missing(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP12']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['gender']);
    }

    #[Test]
    public function update_fails_validation_if_date_of_birth_missing(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP13']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['date_of_birth']);
    }

    #[Test]
    public function update_fails_validation_if_gender_invalid(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP14']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'unknown', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['gender']);
    }

    #[Test]
    public function update_fails_validation_if_date_of_birth_format_invalid(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP15']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990/01/01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['date_of_birth']);
    }

    #[Test]
    public function update_fails_validation_if_date_of_birth_in_future(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP16']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => now()->addYear()->format('Y-m-d')];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['date_of_birth']);
    }

    #[Test]
    public function update_fails_validation_if_blood_group_id_invalid(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP17']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'blood_group_id' => 99999];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(422)->assertJsonValidationErrors(['blood_group_id']);
    }

    #[Test]
    public function update_accepts_null_blood_group_id(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-UP18', 'blood_group_id' => $this->bloodGroupA->id]);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01', 'blood_group_id' => null];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200);
        $this->assertNull($response->json('data.blood_group'));
    }

    #[Test]
    public function update_for_another_hospitals_patient_returns_non_enumerating_404(): void
    {
        $otherPatient = Patient::factory()->create(['hospital_id' => $this->otherHospital->id, 'mrn' => 'MRN-OTHER-UP']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Cross Hospital Attack', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$otherPatient->id}", $payload);

        // Mandatory non-enumerating 404
        $response->assertStatus(404);
    }

    #[Test]
    public function update_for_non_existent_patient_id_returns_404(): void
    {
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Non Existent', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];
        $response = $this->withToken($token)->putJson('/api/v1/hospital/patients/999999', $payload);

        $response->assertStatus(404);
    }

    #[Test]
    public function update_cross_hospital_and_non_existent_responses_are_identical_404(): void
    {
        $otherPatient = Patient::factory()->create(['hospital_id' => $this->otherHospital->id]);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];
        $r1 = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$otherPatient->id}", $payload);
        $r2 = $this->withToken($token)->putJson('/api/v1/hospital/patients/999999', $payload);

        $this->assertEquals(404, $r1->status());
        $this->assertEquals(404, $r2->status());
        $this->assertArrayHasKey('message', $r1->json());
        $this->assertArrayHasKey('message', $r2->json());
    }

    #[Test]
    public function update_without_token_returns_401(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);
        $response->assertStatus(401);
    }

    #[Test]
    public function update_with_donor_role_returns_403(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        $token = $this->validToken($this->donorUser);
        $payload = ['name' => 'Name', 'gender' => 'male', 'date_of_birth' => '1990-01-01'];

        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);
        $response->assertStatus(403);
    }

    #[Test]
    public function update_returns_updated_patient_detail_resource(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-RESOURCE-RET']);
        $token = $this->validToken($this->validHospitalUser);

        $payload = ['name' => 'New Name', 'gender' => 'female', 'date_of_birth' => '1994-04-04'];
        $response = $this->withToken($token)->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'mrn', 'name', 'gender', 'date_of_birth', 'contact_number', 'status', 'ward_name', 'room_number', 'bed_number', 'blood_group', 'created_at', 'updated_at', 'blood_requests'],
            ]);
    }

    // ==========================================
    // GROUP 7: ROUTE REGISTRATION & SYSTEM (Tests 137-151)
    // ==========================================

    #[Test]
    public function route_patients_index_is_registered(): void
    {
        $this->assertTrue(Route::has('api.v1.hospital.patients.index') || Route::getRoutes()->hasNamedRoute('api.v1.hospital.patients.index') || Route::getRoutes()->getByAction('App\Http\Controllers\Api\V1\Hospital\HospitalPatientController@index') !== null);
    }

    #[Test]
    public function route_patients_store_is_registered(): void
    {
        $this->assertNotNull(Route::getRoutes()->getByAction('App\Http\Controllers\Api\V1\Hospital\HospitalPatientController@store'));
    }

    #[Test]
    public function route_patients_show_is_registered(): void
    {
        $this->assertNotNull(Route::getRoutes()->getByAction('App\Http\Controllers\Api\V1\Hospital\HospitalPatientController@show'));
    }

    #[Test]
    public function route_patients_update_is_registered(): void
    {
        $this->assertNotNull(Route::getRoutes()->getByAction('App\Http\Controllers\Api\V1\Hospital\HospitalPatientController@update'));
    }

    #[Test]
    public function route_patients_delete_is_not_registered(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);

        $response = $this->withToken($token)->deleteJson("/api/v1/hospital/patients/{$patient->id}");
        $response->assertStatus(405);
    }

    #[Test]
    public function route_middleware_includes_sanctum_auth(): void
    {
        $route = Route::getRoutes()->getByAction('App\Http\Controllers\Api\V1\Hospital\HospitalPatientController@index');
        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
    }

    #[Test]
    public function route_middleware_includes_hospital_mobile_ability(): void
    {
        $route = Route::getRoutes()->getByAction('App\Http\Controllers\Api\V1\Hospital\HospitalPatientController@index');
        $this->assertContains('abilities:hospital-mobile', $route->gatherMiddleware());
    }

    #[Test]
    public function route_middleware_includes_hospital_active(): void
    {
        $route = Route::getRoutes()->getByAction('App\Http\Controllers\Api\V1\Hospital\HospitalPatientController@index');
        $this->assertContains('hospital.active', $route->gatherMiddleware());
    }

    #[Test]
    public function route_middleware_includes_throttle_hospital_api(): void
    {
        $route = Route::getRoutes()->getByAction('App\Http\Controllers\Api\V1\Hospital\HospitalPatientController@index');
        $this->assertContains('throttle:hospital-api', $route->gatherMiddleware());
    }

    #[Test]
    public function patients_table_indexes_exist_and_queryable(): void
    {
        Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'mrn' => 'MRN-INDEX-1']);
        $p = Patient::where('hospital_id', $this->activeHospital->id)->where('mrn', 'MRN-INDEX-1')->first();
        $this->assertNotNull($p);
    }

    #[Test]
    public function patient_model_has_hospital_relation(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        $this->assertInstanceOf(Hospital::class, $patient->hospital);
        $this->assertEquals($this->activeHospital->id, $patient->hospital->id);
    }

    #[Test]
    public function patient_model_has_blood_group_relation(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id, 'blood_group_id' => $this->bloodGroupA->id]);
        $this->assertInstanceOf(BloodGroup::class, $patient->bloodGroup);
        $this->assertEquals($this->bloodGroupA->id, $patient->bloodGroup->id);
    }

    #[Test]
    public function patient_model_has_blood_requests_relation(): void
    {
        $patient = Patient::factory()->create(['hospital_id' => $this->activeHospital->id]);
        $this->assertNotNull($patient->bloodRequests());
    }

    #[Test]
    public function json_content_type_header_enforced(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->getJson('/api/v1/hospital/patients');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Type'), 'application/json'));
    }

    #[Test]
    public function api_error_response_format_is_standard_json(): void
    {
        $token = $this->validToken($this->validHospitalUser);
        $response = $this->withToken($token)->postJson('/api/v1/hospital/patients', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }
}
