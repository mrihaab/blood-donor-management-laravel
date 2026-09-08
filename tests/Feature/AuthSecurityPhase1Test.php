<?php

namespace Tests\Feature;

use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
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

    // ==========================================
    // 1. EMAIL VERIFICATION TESTS
    // ==========================================

    #[Test]
    public function new_registration_remains_unverified_and_dispatches_registered_event()
    {
        Event::fake();

        $response = $this->post('/register', [
            'name' => 'Jane Donor',
            'email' => 'janedonor@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $user = User::where('email', 'janedonor@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        $this->assertEquals('donor', $user->role);
        $this->assertEquals('active', $user->status);

        Event::assertDispatched(Registered::class);
    }

    #[Test]
    public function unverified_user_cannot_access_verified_protected_routes()
    {
        $unverifiedUser = User::factory()->create([
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($unverifiedUser)->get('/donor/dashboard');

        $response->assertRedirect('/verify-email');
    }

    #[Test]
    public function valid_signed_verification_link_verifies_the_user()
    {
        $unverifiedUser = User::factory()->create([
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $unverifiedUser->id, 'hash' => sha1($unverifiedUser->email)]
        );

        $response = $this->actingAs($unverifiedUser)->get($verificationUrl);

        $this->assertTrue($unverifiedUser->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    #[Test]
    public function invalid_signed_verification_link_fails()
    {
        $unverifiedUser = User::factory()->create([
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => null,
        ]);

        $invalidUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $unverifiedUser->id, 'hash' => 'invalid-hash-string']
        );

        $response = $this->actingAs($unverifiedUser)->get($invalidUrl);

        $this->assertFalse($unverifiedUser->fresh()->hasVerifiedEmail());
        $response->assertStatus(403);
    }

    #[Test]
    public function expired_signed_verification_link_fails()
    {
        $unverifiedUser = User::factory()->create([
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => null,
        ]);

        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(10),
            ['id' => $unverifiedUser->id, 'hash' => sha1($unverifiedUser->email)]
        );

        $response = $this->actingAs($unverifiedUser)->get($expiredUrl);

        $this->assertFalse($unverifiedUser->fresh()->hasVerifiedEmail());
        $response->assertStatus(403);
    }

    #[Test]
    public function verification_resend_is_rate_limited()
    {
        $unverifiedUser = User::factory()->create([
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => null,
        ]);

        $response = null;
        for ($i = 0; $i < 7; $i++) {
            $response = $this->actingAs($unverifiedUser)
                ->post('/email/verification-notification', [], ['Accept' => 'application/json']);
        }

        $response->assertStatus(429);
    }

    // ==========================================
    // 2. ACCOUNT STATUS ENFORCEMENT TESTS
    // ==========================================

    #[Test]
    public function active_user_can_login()
    {
        $activeUser = User::factory()->create([
            'email' => 'activeuser@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'activeuser@example.com',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($activeUser);
    }

    #[Test]
    public function inactive_user_cannot_login()
    {
        $inactiveUser = User::factory()->create([
            'email' => 'inactiveuser@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'inactive',
            'role' => 'donor',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'inactiveuser@example.com',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function blocked_user_cannot_login()
    {
        $blockedUser = User::factory()->create([
            'email' => 'blockeduser@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'blocked',
            'role' => 'donor',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'blockeduser@example.com',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function authenticated_user_loses_access_and_session_is_invalidated_when_status_becomes_blocked_or_inactive()
    {
        $user = User::factory()->create([
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);
        $this->assertAuthenticated();

        // Account status is updated to blocked in database while user has an active session
        $user->update(['status' => 'blocked']);

        $response = $this->get('/donor/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    // ==========================================
    // 3. ROLE AUTHORIZATION MATRIX (ALL 9 COMBINATIONS)
    // ==========================================

    #[Test]
    public function full_role_authorization_matrix_enforces_strict_isolation()
    {
        $admin = User::factory()->create([
            'status' => 'active',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $hospRecord = Hospital::create([
            'name' => 'General Hospital',
            'license_number' => 'HOSP-12345',
            'address' => '123 Main St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Smith',
            'contact_phone' => '555-0199',
            'email' => 'hosp@example.com',
            'status' => 'active',
        ]);
        $hospital = User::factory()->create([
            'status' => 'active',
            'role' => 'hospital',
            'hospital_id' => $hospRecord->id,
            'email_verified_at' => now(),
        ]);
        $donor = User::factory()->create([
            'status' => 'active',
            'role' => 'donor',
            'email_verified_at' => now(),
        ]);

        // 1. Admin -> Admin (Allowed)
        $this->actingAs($admin)->withSession(['2fa_verified' => true])->get('/admin/dashboard')->assertStatus(200);

        // 2. Admin -> Hospital (Denied 403)
        $this->actingAs($admin)->withSession(['2fa_verified' => true])->get('/hospital/dashboard')->assertStatus(403);

        // 3. Admin -> Donor (Denied 403)
        $this->actingAs($admin)->withSession(['2fa_verified' => true])->get('/donor/dashboard')->assertStatus(403);

        // 4. Hospital -> Admin (Denied 403)
        $this->actingAs($hospital)->get('/admin/dashboard')->assertStatus(403);

        // 5. Hospital -> Hospital (Allowed)
        $this->actingAs($hospital)->get('/hospital/dashboard')->assertStatus(200);

        // 6. Hospital -> Donor (Denied 403)
        $this->actingAs($hospital)->get('/donor/dashboard')->assertStatus(403);

        // 7. Donor -> Admin (Denied 403)
        $this->actingAs($donor)->get('/admin/dashboard')->assertStatus(403);

        // 8. Donor -> Hospital (Denied 403)
        $this->actingAs($donor)->get('/hospital/dashboard')->assertStatus(403);

        // 9. Donor -> Donor (Allowed)
        $this->actingAs($donor)->get('/donor/dashboard')->assertStatus(200);
    }

    // ==========================================
    // 4. LOGOUT & CACHE CONTROL TESTS
    // ==========================================

    #[Test]
    public function valid_post_logout_succeeds_and_unauthenticates_user()
    {
        $user = User::factory()->create(['status' => 'active', 'role' => 'hospital', 'email_verified_at' => now()]);

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    #[Test]
    public function duplicate_logout_is_handled_safely()
    {
        $user = User::factory()->create(['status' => 'active', 'role' => 'hospital', 'email_verified_at' => now()]);

        $this->actingAs($user);
        $this->post('/logout');

        // Second logout request when already unauthenticated
        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    #[Test]
    public function protected_html_responses_contain_no_store_cache_headers()
    {
        $user = User::factory()->create(['status' => 'active', 'role' => 'hospital', 'email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/hospital/dashboard');

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
    }

    // ==========================================
    // 5. HOSPITAL DATA ISOLATION TESTS
    // ==========================================

    #[Test]
    public function hospital_user_cannot_access_another_hospitals_patient_data()
    {
        $hospitalA = Hospital::create([
            'name' => 'Hospital Alpha', 'license_number' => 'LIC-101', 'address' => 'Street A',
            'city' => 'Metropolis', 'state' => 'State A', 'contact_person' => 'Dr. A',
            'contact_phone' => '123456', 'email' => 'hospa@example.com', 'status' => 'active',
        ]);

        $hospitalB = Hospital::create([
            'name' => 'Hospital Beta', 'license_number' => 'LIC-102', 'address' => 'Street B',
            'city' => 'Metropolis', 'state' => 'State B', 'contact_person' => 'Dr. B',
            'contact_phone' => '654321', 'email' => 'hospb@example.com', 'status' => 'active',
        ]);

        $userA = User::factory()->create([
            'status' => 'active', 'role' => 'hospital', 'hospital_id' => $hospitalA->id, 'email_verified_at' => now(),
        ]);

        $patientB = Patient::create([
            'hospital_id' => $hospitalB->id, 'name' => 'Patient in Hospital B', 'mrn' => 'MRN-B-999',
            'gender' => 'male', 'date_of_birth' => '1995-05-15', 'blood_group_id' => $this->bloodGroup->id,
            'contact_number' => '999999', 'status' => 'admitted',
        ]);

        // User from Hospital A attempts to view Patient from Hospital B
        $response = $this->actingAs($userA)->get("/hospital/patients/{$patientB->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function hospital_user_cannot_access_another_hospitals_requisition_data()
    {
        $hospitalA = Hospital::create([
            'name' => 'Hospital Alpha', 'license_number' => 'LIC-101', 'address' => 'Street A',
            'city' => 'Metropolis', 'state' => 'State A', 'contact_person' => 'Dr. A',
            'contact_phone' => '123456', 'email' => 'hospa@example.com', 'status' => 'active',
        ]);

        $hospitalB = Hospital::create([
            'name' => 'Hospital Beta', 'license_number' => 'LIC-102', 'address' => 'Street B',
            'city' => 'Metropolis', 'state' => 'State B', 'contact_person' => 'Dr. B',
            'contact_phone' => '654321', 'email' => 'hospb@example.com', 'status' => 'active',
        ]);

        $userA = User::factory()->create([
            'status' => 'active', 'role' => 'hospital', 'hospital_id' => $hospitalA->id, 'email_verified_at' => now(),
        ]);

        $userB = User::factory()->create([
            'status' => 'active', 'role' => 'hospital', 'hospital_id' => $hospitalB->id, 'email_verified_at' => now(),
        ]);

        $patientB = Patient::create([
            'hospital_id' => $hospitalB->id, 'name' => 'Patient B', 'mrn' => 'MRN-B-100',
            'gender' => 'female', 'date_of_birth' => '1990-01-01', 'blood_group_id' => $this->bloodGroup->id,
            'contact_number' => '888888', 'status' => 'admitted',
        ]);

        $reqB = BloodRequest::create([
            'user_id' => $userB->id,
            'hospital_id' => $hospitalB->id,
            'patient_id' => $patientB->id,
            'patient_name' => 'Patient B',
            'hospital' => 'Hospital Beta',
            'blood_group_id' => $this->bloodGroup->id,
            'blood_group' => 'O+',
            'city' => 'Metropolis',
            'units_requested' => 2,
            'status' => 'pending',
            'urgency_level' => 'urgent',
            'required_by' => now()->addHours(24),
        ]);

        // User from Hospital A attempts to view BloodRequest from Hospital B
        $response = $this->actingAs($userA)->get("/hospital/requests/{$reqB->id}");

        $response->assertStatus(403);
    }
}
