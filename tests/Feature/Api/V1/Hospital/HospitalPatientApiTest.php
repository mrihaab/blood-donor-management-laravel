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

class HospitalPatientApiTest extends TestCase
{
    use RefreshDatabase;

    protected Hospital $activeHospital;
    protected Hospital $otherHospital;
    protected User $validHospitalUser;
    protected User $otherHospitalUser;
    protected BloodGroup $bloodGroupA;
    protected BloodGroup $bloodGroupB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activeHospital = Hospital::create([
            "name" => "Central Clinical Hospital",
            "license_number" => "HOSP-ACTIVE-01",
            "address" => "100 Health Ave",
            "city" => "Metropolis",
            "state" => "NY",
            "contact_person" => "Dr. Alice",
            "contact_phone" => "555-0100",
            "email" => "contact@centralhosp.org",
            "status" => "active",
        ]);

        $this->otherHospital = Hospital::create([
            "name" => "City General Hospital",
            "license_number" => "HOSP-OTHER-02",
            "address" => "200 Metro Rd",
            "city" => "Metropolis",
            "state" => "NY",
            "contact_person" => "Dr. Bob",
            "contact_phone" => "555-0200",
            "email" => "contact@cityhosp.org",
            "status" => "active",
        ]);

        $this->validHospitalUser = User::factory()->create([
            "name" => "Dr. Sarah Connor",
            "email" => "sarah.connor@centralhosp.org",
            "password" => Hash::make("Password123!"),
            "role" => "hospital",
            "status" => "active",
            "email_verified_at" => now(),
            "hospital_id" => $this->activeHospital->id,
        ]);

        $this->otherHospitalUser = User::factory()->create([
            "name" => "Dr. John Doe",
            "email" => "john.doe@cityhosp.org",
            "password" => Hash::make("Password123!"),
            "role" => "hospital",
            "status" => "active",
            "email_verified_at" => now(),
            "hospital_id" => $this->otherHospital->id,
        ]);

        $this->bloodGroupA = BloodGroup::firstOrCreate(
            ["name" => "A+"],
            ["description" => "Blood type A+"]
        );

        $this->bloodGroupB = BloodGroup::firstOrCreate(
            ["name" => "B+"],
            ["description" => "Blood type B+"]
        );
    }

    #[Test]
    public function index_without_token_returns_401(): void
    {
        $response = $this->getJson("/api/v1/hospital/patients");

        $response->assertStatus(401)
            ->assertJson(["message" => "Unauthenticated."]);
    }

    #[Test]
    public function index_without_hospital_mobile_ability_returns_403(): void
    {
        $wrongAbilityToken = $this->validHospitalUser->createToken("Device", ["donor-mobile"])->plainTextToken;

        $response = $this->withToken($wrongAbilityToken)
            ->getJson("/api/v1/hospital/patients");

        $response->assertStatus(403);
    }

    #[Test]
    public function index_returns_paginated_patients_for_authenticated_hospital_only(): void
    {
        // 2 patients for active hospital
        Patient::factory()->create([
            "hospital_id" => $this->activeHospital->id,
            "name" => "Alpha Patient",
            "mrn" => "MRN-001",
            "blood_group_id" => $this->bloodGroupA->id,
        ]);
        Patient::factory()->create([
            "hospital_id" => $this->activeHospital->id,
            "name" => "Beta Patient",
            "mrn" => "MRN-002",
        ]);

        // 1 patient for other hospital
        Patient::factory()->create([
            "hospital_id" => $this->otherHospital->id,
            "name" => "Gamma Other Patient",
            "mrn" => "MRN-OTHER-003",
        ]);

        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/hospital/patients");

        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => [
                    "*" => ["id", "name", "mrn", "gender", "date_of_birth", "status", "blood_group"],
                ],
                "meta" => ["current_page", "last_page", "per_page", "total"],
                "links" => ["first", "last", "prev", "next"],
            ]);

        $this->assertEquals(2, $response->json("meta.total"));
        $names = array_column($response->json("data"), "name");
        $this->assertContains("Alpha Patient", $names);
        $this->assertContains("Beta Patient", $names);
        $this->assertNotContains("Gamma Other Patient", $names);
    }

    #[Test]
    public function index_search_filters_by_name_and_mrn_using_search_or_q(): void
    {
        Patient::factory()->create([
            "hospital_id" => $this->activeHospital->id,
            "name" => "Johnathan Smith",
            "mrn" => "MRN-1001",
        ]);
        Patient::factory()->create([
            "hospital_id" => $this->activeHospital->id,
            "name" => "Jane Miller",
            "mrn" => "MRN-2002",
        ]);

        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        // Search by name using search parameter
        $response1 = $this->withToken($token)
            ->getJson("/api/v1/hospital/patients?search=Johnathan");
        $response1->assertStatus(200);
        $this->assertEquals(1, $response1->json("meta.total"));
        $this->assertEquals("Johnathan Smith", $response1->json("data.0.name"));

        // Search by MRN using q parameter
        $response2 = $this->withToken($token)
            ->getJson("/api/v1/hospital/patients?q=2002");
        $response2->assertStatus(200);
        $this->assertEquals(1, $response2->json("meta.total"));
        $this->assertEquals("Jane Miller", $response2->json("data.0.name"));
    }

    #[Test]
    public function index_status_and_blood_group_filters(): void
    {
        Patient::factory()->create([
            "hospital_id" => $this->activeHospital->id,
            "status" => "active",
            "blood_group_id" => $this->bloodGroupA->id,
        ]);
        Patient::factory()->create([
            "hospital_id" => $this->activeHospital->id,
            "status" => "discharged",
            "blood_group_id" => $this->bloodGroupB->id,
        ]);

        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        // Status filter
        $response1 = $this->withToken($token)
            ->getJson("/api/v1/hospital/patients?status=discharged");
        $response1->assertStatus(200);
        $this->assertEquals(1, $response1->json("meta.total"));
        $this->assertEquals("discharged", $response1->json("data.0.status"));

        // Blood group filter
        $response2 = $this->withToken($token)
            ->getJson("/api/v1/hospital/patients?blood_group_id={$this->bloodGroupA->id}");
        $response2->assertStatus(200);
        $this->assertEquals(1, $response2->json("meta.total"));
        $this->assertEquals("A+", $response2->json("data.0.blood_group.name"));
    }

    #[Test]
    public function store_creates_new_patient_and_assigns_authenticated_hospital_id(): void
    {
        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        $payload = [
            "name" => "Robert Paulson",
            "mrn" => "MRN-9999",
            "gender" => "male",
            "date_of_birth" => "1980-05-15",
            "blood_group_id" => $this->bloodGroupA->id,
            "contact_number" => "+1555000111",
            "ward_name" => "Cardiology",
            "room_number" => "302",
            "bed_number" => "B",
            "status" => "active",
        ];

        $response = $this->withToken($token)
            ->postJson("/api/v1/hospital/patients", $payload);

        $response->assertStatus(201)
            ->assertJson([
                "data" => [
                    "name" => "Robert Paulson",
                    "mrn" => "MRN-9999",
                    "gender" => "male",
                    "date_of_birth" => "1980-05-15",
                    "ward_name" => "Cardiology",
                    "room_number" => "302",
                    "bed_number" => "B",
                    "blood_group" => [
                        "id" => $this->bloodGroupA->id,
                        "name" => "A+",
                    ],
                ],
            ]);

        $this->assertDatabaseHas("patients", [
            "name" => "Robert Paulson",
            "mrn" => "MRN-9999",
            "hospital_id" => $this->activeHospital->id,
        ]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        $response = $this->withToken($token)
            ->postJson("/api/v1/hospital/patients", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(["name", "mrn", "gender", "date_of_birth"]);
    }

    #[Test]
    public function show_returns_patient_detail_with_blood_group_and_requisitions(): void
    {
        $patient = Patient::factory()->create([
            "hospital_id" => $this->activeHospital->id,
            "name" => "Detailed Patient",
            "mrn" => "MRN-DETAIL-1",
            "blood_group_id" => $this->bloodGroupA->id,
        ]);

        BloodRequest::create([
            "user_id" => $this->validHospitalUser->id,
            "hospital_id" => $this->activeHospital->id,
            "patient_id" => $patient->id,
            "patient_name" => $patient->name,
            "blood_group" => "A+",
            "units_needed" => 2,
            "hospital" => $this->activeHospital->name,
            "city" => "Metropolis",
            "status" => "pending",
            "urgency_level" => "emergency",
        ]);

        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/hospital/patients/{$patient->id}");

        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => $patient->id,
                    "name" => "Detailed Patient",
                    "mrn" => "MRN-DETAIL-1",
                    "blood_group" => [
                        "id" => $this->bloodGroupA->id,
                        "name" => "A+",
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json("data.blood_requests"));
    }

    #[Test]
    public function show_for_another_hospitals_patient_returns_non_enumerating_404(): void
    {
        $otherPatient = Patient::factory()->create([
            "hospital_id" => $this->otherHospital->id,
            "name" => "Secret Other Patient",
            "mrn" => "MRN-SECRET-01",
        ]);

        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/hospital/patients/{$otherPatient->id}");

        // MANDATORY SECURITY CHECK: Must return 404 (NOT 403) to prevent patient ID enumeration
        $response->assertStatus(404);
    }

    #[Test]
    public function show_for_non_existent_patient_returns_404(): void
    {
        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/hospital/patients/9999999");

        $response->assertStatus(404);
    }

    #[Test]
    public function update_modifies_patient_details(): void
    {
        $patient = Patient::factory()->create([
            "hospital_id" => $this->activeHospital->id,
            "name" => "Original Name",
            "mrn" => "MRN-ORIG",
            "gender" => "male",
            "date_of_birth" => "1990-01-01",
        ]);

        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        $payload = [
            "name" => "Updated Name",
            "mrn" => "MRN-UPDATED",
            "gender" => "male",
            "date_of_birth" => "1990-01-01",
            "ward_name" => "ICU",
            "room_number" => "105",
            "bed_number" => "C",
            "status" => "discharged",
        ];

        $response = $this->withToken($token)
            ->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                "data" => [
                    "id" => $patient->id,
                    "name" => "Updated Name",
                    "mrn" => "MRN-UPDATED",
                    "ward_name" => "ICU",
                    "room_number" => "105",
                    "bed_number" => "C",
                    "status" => "discharged",
                ],
            ]);

        $this->assertDatabaseHas("patients", [
            "id" => $patient->id,
            "name" => "Updated Name",
            "status" => "discharged",
        ]);
    }

    #[Test]
    public function update_for_another_hospitals_patient_returns_non_enumerating_404(): void
    {
        $otherPatient = Patient::factory()->create([
            "hospital_id" => $this->otherHospital->id,
            "name" => "Other Hosp Patient",
            "mrn" => "MRN-OTHER-99",
            "gender" => "female",
            "date_of_birth" => "1992-02-02",
        ]);

        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        $payload = [
            "name" => "Attempted Update Name",
            "mrn" => "MRN-ATTEMPT",
            "gender" => "female",
            "date_of_birth" => "1992-02-02",
        ];

        $response = $this->withToken($token)
            ->putJson("/api/v1/hospital/patients/{$otherPatient->id}", $payload);

        // MANDATORY SECURITY CHECK: Must return 404 (NOT 403) to prevent patient ID enumeration
        $response->assertStatus(404);
    }

    #[Test]
    public function update_ignores_hospital_id_in_payload_and_retains_ownership(): void
    {
        $patient = Patient::factory()->create([
            "hospital_id" => $this->activeHospital->id,
            "name" => "Protected Ownership Patient",
            "mrn" => "MRN-PROT-1",
            "gender" => "male",
            "date_of_birth" => "1988-08-08",
        ]);

        $token = $this->validHospitalUser->createToken("Device", ["hospital-mobile"])->plainTextToken;

        $payload = [
            "name" => "Protected Ownership Patient",
            "mrn" => "MRN-PROT-1",
            "gender" => "male",
            "date_of_birth" => "1988-08-08",
            "hospital_id" => $this->otherHospital->id, // Malicious attempt to change hospital ownership
        ];

        $response = $this->withToken($token)
            ->putJson("/api/v1/hospital/patients/{$patient->id}", $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas("patients", [
            "id" => $patient->id,
            "hospital_id" => $this->activeHospital->id,
        ]);
    }
}
