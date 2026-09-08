<?php

namespace Tests\Feature;

use App\Models\BloodGroup;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthSecurityPhase1Test extends TestCase
{
    use RefreshDatabase;

    protected BloodGroup $bloodGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bloodGroup = BloodGroup::firstOrCreate(['name' => 'O+'], ['description' => 'O Positive']);
    }

    #[Test]
    public function new_registered_user_remains_unverified_and_triggers_event()
    {
        Event::fake();

        $response = $this->post('/register', [
            'name' => 'John Donor',
            'email' => 'johndonor@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'johndonor@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        $this->assertEquals('donor', $user->role);
        $this->assertEquals('active', $user->status);
    }

    #[Test]
    public function inactive_or_blocked_user_cannot_login()
    {
        $blockedUser = User::factory()->create([
            'email' => 'blocked@example.com',
            'password' => Hash::make('password123'),
            'status' => 'blocked',
            'role' => 'donor',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'blocked@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function active_user_can_login_and_access_dashboard()
    {
        $activeUser = User::factory()->create([
            'email' => 'active@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'active@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($activeUser);
    }

    #[Test]
    public function login_attempts_are_rate_limited_after_five_failures()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'wrong@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function role_authorization_matrix_enforces_strict_access_control()
    {
        $hospitalUser = User::factory()->create([
            'status' => 'active',
            'role' => 'hospital',
            'email_verified_at' => now(),
        ]);

        $donorUser = User::factory()->create([
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => now(),
        ]);

        // Donor trying to access Hospital Dashboard -> 403
        $this->actingAs($donorUser)
            ->get('/hospital/dashboard')
            ->assertStatus(403);

        // Hospital user trying to access Admin Dashboard -> 403
        $this->actingAs($hospitalUser)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    }

    #[Test]
    public function hospital_user_cannot_access_another_hospitals_patient_data()
    {
        $hospitalA = Hospital::create([
            'name' => 'Hospital Alpha',
            'license_number' => 'LIC-101',
            'address' => 'Street A',
            'city' => 'Metropolis',
            'state' => 'State A',
            'contact_person' => 'Dr. A',
            'contact_phone' => '123456',
            'email' => 'hospa@example.com',
            'status' => 'active',
        ]);

        $hospitalB = Hospital::create([
            'name' => 'Hospital Beta',
            'license_number' => 'LIC-102',
            'address' => 'Street B',
            'city' => 'Metropolis',
            'state' => 'State B',
            'contact_person' => 'Dr. B',
            'contact_phone' => '654321',
            'email' => 'hospb@example.com',
            'status' => 'active',
        ]);

        $userA = User::factory()->create([
            'status' => 'active',
            'role' => 'hospital',
            'hospital_id' => $hospitalA->id,
            'email_verified_at' => now(),
        ]);

        $patientB = Patient::create([
            'hospital_id' => $hospitalB->id,
            'name' => 'Patient in Hospital B',
            'mrn' => 'MRN-B-999',
            'gender' => 'male',
            'date_of_birth' => '1995-05-15',
            'blood_group_id' => $this->bloodGroup->id,
            'contact_number' => '999999',
            'status' => 'admitted',
        ]);

        // User from Hospital A attempts to view Patient from Hospital B
        $response = $this->actingAs($userA)
            ->get("/hospital/patients/{$patientB->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function secure_logout_clears_session_and_unauthenticates_user()
    {
        $user = User::factory()->create([
            'status' => 'active',
            'role' => 'hospital',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
