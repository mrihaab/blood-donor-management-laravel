<?php

namespace Tests\Feature\Api\V1\Hospital;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HospitalAuthTest extends TestCase
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
    }

    // 1 & 2. Successful login returns token, safe fields, and hospital-mobile ability in DB
    #[Test]
    public function successful_login_returns_token_and_safe_approved_allowlist_fields(): void
    {
        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'SARAH.CONNOR@centralhosp.org',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'hospital' => [
                        'id',
                        'name',
                        'license_number',
                        'status',
                    ],
                ],
            ]);

        $tokenString = $response->json('token');
        $this->assertNotEmpty($tokenString);
        $this->assertEquals('sarah.connor@centralhosp.org', $response->json('user.email'));

        // Verify token in database has exactly ['hospital-mobile'] ability
        $tokenRecord = DB::table('personal_access_tokens')->where('tokenable_id', $this->validHospitalUser->id)->first();
        $this->assertNotNull($tokenRecord);
        $this->assertEquals(json_encode(['hospital-mobile']), $tokenRecord->abilities);
    }

    // 3. Invalid email returns generic 401
    #[Test]
    public function login_with_unknown_email_returns_generic_401(): void
    {
        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'unknown.doctor@centralhosp.org',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid email or password.']);
    }

    // 4. Invalid password returns identical generic 401
    #[Test]
    public function login_with_wrong_password_returns_identical_generic_401(): void
    {
        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'sarah.connor@centralhosp.org',
            'password' => 'WrongPassword999!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid email or password.']);
    }

    // 5. Admin rejected after valid credentials
    #[Test]
    public function admin_user_with_valid_password_is_rejected_with_generic_403(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.user@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'admin.user@example.com',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 6. Donor rejected after valid credentials
    #[Test]
    public function donor_user_with_valid_password_is_rejected_with_generic_403(): void
    {
        $donor = User::factory()->create([
            'email' => 'donor.user@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'donor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'donor.user@example.com',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 7. Inactive hospital user rejected
    #[Test]
    public function inactive_hospital_user_is_rejected_with_generic_403(): void
    {
        $inactiveUser = User::factory()->create([
            'email' => 'inactive.staff@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'inactive',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'inactive.staff@centralhosp.org',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 8. Blocked hospital user rejected
    #[Test]
    public function blocked_hospital_user_is_rejected_with_generic_403(): void
    {
        $blockedUser = User::factory()->create([
            'email' => 'blocked.staff@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'blocked',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'blocked.staff@centralhosp.org',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 9. Unverified hospital user rejected
    #[Test]
    public function unverified_hospital_user_is_rejected_with_generic_403(): void
    {
        $unverifiedUser = User::factory()->create([
            'email' => 'unverified.staff@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => null,
            'hospital_id' => $this->activeHospital->id,
        ]);

        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'unverified.staff@centralhosp.org',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 10. Missing hospital relationship rejected
    #[Test]
    public function hospital_user_without_hospital_association_is_rejected_with_generic_403(): void
    {
        $noHospUser = User::factory()->create([
            'email' => 'nohospital.staff@example.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => null,
        ]);

        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'nohospital.staff@example.org',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 11. Inactive hospital rejected
    #[Test]
    public function hospital_user_with_inactive_hospital_is_rejected_with_generic_403(): void
    {
        $inactiveHospUser = User::factory()->create([
            'email' => 'staff@suspendedhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => $this->inactiveHospital->id,
        ]);

        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'staff@suspendedhosp.org',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 12. Validation failures return 422
    #[Test]
    public function invalid_input_format_returns_422(): void
    {
        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'not-an-email',
            'password' => '',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // 13. Missing device_name returns 422
    #[Test]
    public function missing_device_name_returns_422(): void
    {
        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'sarah.connor@centralhosp.org',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device_name']);
    }

    // 14. Sixth login request within the limiter window returns 429
    #[Test]
    public function excessive_login_attempts_are_rate_limited_with_429(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/hospital/auth/login', [
                'email' => 'rate.limit@centralhosp.org',
                'password' => 'WrongPassword!',
                'device_name' => 'TestDevice',
            ]);
        }

        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'rate.limit@centralhosp.org',
            'password' => 'WrongPassword!',
            'device_name' => 'TestDevice',
        ]);

        $response->assertStatus(429);
    }

    // 15. /me without a token returns 401 JSON
    #[Test]
    public function me_endpoint_without_token_returns_401_json(): void
    {
        $response = $this->getJson('/api/v1/hospital/auth/me');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    // 16. /me with a valid correct-ability token returns profile data
    #[Test]
    public function me_endpoint_with_valid_hospital_mobile_token_returns_profile(): void
    {
        $token = $this->validHospitalUser->createToken('TestDevice', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/hospital/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $this->validHospitalUser->id,
                    'name' => $this->validHospitalUser->name,
                    'email' => $this->validHospitalUser->email,
                    'role' => 'hospital',
                    'hospital' => [
                        'id' => $this->activeHospital->id,
                        'name' => $this->activeHospital->name,
                        'license_number' => $this->activeHospital->license_number,
                        'status' => 'active',
                    ],
                ],
            ]);
    }

    // 17. Token without hospital-mobile ability (e.g. wrong ability) is denied 403 by CheckAbilities
    #[Test]
    public function token_without_hospital_mobile_ability_is_denied_access_to_me(): void
    {
        $wrongAbilityToken = $this->validHospitalUser->createToken('TestDevice', ['donor-mobile'])->plainTextToken;

        $response = $this->withToken($wrongAbilityToken)
            ->getJson('/api/v1/hospital/auth/me');

        $response->assertStatus(403);
    }

    // 18. Wildcard token ability behaviour test
    #[Test]
    public function wildcard_token_ability_behaviour_on_me_endpoint(): void
    {
        $wildcardToken = $this->validHospitalUser->createToken('TestDevice', ['*'])->plainTextToken;

        $response = $this->withToken($wildcardToken)
            ->getJson('/api/v1/hospital/auth/me');

        // Sanctum CheckAbilities middleware evaluates tokenCan('*') as true for any required ability string.
        $response->assertStatus(200);
    }

    // 19. Token without hospital-mobile ability cannot call /logout
    #[Test]
    public function token_without_hospital_mobile_ability_cannot_call_logout(): void
    {
        $wrongAbilityToken = $this->validHospitalUser->createToken('TestDevice', ['donor-mobile'])->plainTextToken;

        $response = $this->withToken($wrongAbilityToken)
            ->postJson('/api/v1/hospital/auth/logout');

        $response->assertStatus(403);
    }

    // 20. Correct-ability token can call /logout
    #[Test]
    public function logout_with_correct_ability_token_succeeds_and_revokes_token(): void
    {
        $token1 = $this->validHospitalUser->createToken('Phone', ['hospital-mobile'])->plainTextToken;
        $token2 = $this->validHospitalUser->createToken('Tablet', ['hospital-mobile'])->plainTextToken;

        $response = $this->withToken($token1)
            ->postJson('/api/v1/hospital/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        // Token 1 is revoked
        $this->withToken($token1)
            ->getJson('/api/v1/hospital/auth/me')
            ->assertStatus(401);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        // Token 2 remains valid
        $this->withToken($token2)
            ->getJson('/api/v1/hospital/auth/me')
            ->assertStatus(200);
    }

    // 21. A blocked user with a previously issued correct-ability token can logout
    #[Test]
    public function blocked_user_with_issued_token_can_still_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'status.change.logout@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        $token = $user->createToken('TestDevice', ['hospital-mobile'])->plainTextToken;

        // Account status changes to blocked in database after token was issued
        $user->update(['status' => 'blocked']);

        // Data endpoint /me is denied (403 by hospital.active middleware)
        $this->withToken($token)
            ->getJson('/api/v1/hospital/auth/me')
            ->assertStatus(403);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        // Revocation endpoint /logout remains accessible (200) so token can be cleaned up
        $this->withToken($token)
            ->postJson('/api/v1/hospital/auth/logout')
            ->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);
    }

    // 22. Blocked user still cannot access /me
    #[Test]
    public function blocked_user_still_cannot_access_me_endpoint(): void
    {
        $user = User::factory()->create([
            'email' => 'blocked.me.check@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        $token = $user->createToken('TestDevice', ['hospital-mobile'])->plainTextToken;
        $user->update(['status' => 'blocked']);

        $this->withToken($token)
            ->getJson('/api/v1/hospital/auth/me')
            ->assertStatus(403)
            ->assertJson(['message' => 'Access denied. Account is ineligible.']);
    }

    // 23. /logout-all remains available to an ineligible user with correct ability
    #[Test]
    public function logout_all_remains_available_to_ineligible_user_with_correct_ability(): void
    {
        $user = User::factory()->create([
            'email' => 'ineligible.logoutall@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        $token1 = $user->createToken('Phone', ['hospital-mobile'])->plainTextToken;
        $token2 = $user->createToken('Tablet', ['hospital-mobile'])->plainTextToken;

        // User account becomes blocked
        $user->update(['status' => 'blocked']);

        $response = $this->withToken($token1)
            ->postJson('/api/v1/hospital/auth/logout-all');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out from all devices successfully']);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        // Both tokens are revoked
        $this->withToken($token1)
            ->getJson('/api/v1/hospital/auth/me')
            ->assertStatus(401);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        $this->withToken($token2)
            ->getJson('/api/v1/hospital/auth/me')
            ->assertStatus(401);
    }

    // 24. Tokens belonging to another user remain untouched
    #[Test]
    public function logout_all_does_not_affect_tokens_belonging_to_other_users(): void
    {
        $otherHospitalUser = User::factory()->create([
            'email' => 'other.doctor@centralhosp.org',
            'password' => Hash::make('Password123!'),
            'role' => 'hospital',
            'status' => 'active',
            'email_verified_at' => now(),
            'hospital_id' => $this->activeHospital->id,
        ]);

        $myToken = $this->validHospitalUser->createToken('MyPhone', ['hospital-mobile'])->plainTextToken;
        $otherToken = $otherHospitalUser->createToken('OtherPhone', ['hospital-mobile'])->plainTextToken;

        $this->withToken($myToken)
            ->postJson('/api/v1/hospital/auth/logout-all')
            ->assertStatus(200);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();

        // Other user's token is untouched
        $this->withToken($otherToken)
            ->getJson('/api/v1/hospital/auth/me')
            ->assertStatus(200);
    }

    // 25. Exact approved field allowlist and complete absence of excluded fields
    #[Test]
    public function exact_approved_field_allowlist_and_absence_of_excluded_fields(): void
    {
        $response = $this->postJson('/api/v1/hospital/auth/login', [
            'email' => 'sarah.connor@centralhosp.org',
            'password' => 'Password123!',
            'device_name' => 'Vivo V2120',
        ]);

        $response->assertStatus(200);
        $userPayload = $response->json('user');
        $hospitalPayload = $userPayload['hospital'];

        // Assert exact allowed keys in user object
        $this->assertEquals(['id', 'name', 'email', 'role', 'hospital'], array_keys($userPayload));

        // Assert exact allowed keys in hospital object
        $this->assertEquals(['id', 'name', 'license_number', 'status'], array_keys($hospitalPayload));

        // Negative assertions for excluded fields
        $this->assertArrayNotHasKey('status', $userPayload);
        $this->assertArrayNotHasKey('hospital_id', $userPayload);
        $this->assertArrayNotHasKey('email_verified_at', $userPayload);
        $this->assertArrayNotHasKey('password', $userPayload);
        $this->assertArrayNotHasKey('remember_token', $userPayload);

        $this->assertArrayNotHasKey('address', $hospitalPayload);
        $this->assertArrayNotHasKey('city', $hospitalPayload);
        $this->assertArrayNotHasKey('state', $hospitalPayload);
        $this->assertArrayNotHasKey('contact_person', $hospitalPayload);
        $this->assertArrayNotHasKey('contact_phone', $hospitalPayload);
        $this->assertArrayNotHasKey('email', $hospitalPayload);
        $this->assertArrayNotHasKey('created_at', $hospitalPayload);
        $this->assertArrayNotHasKey('updated_at', $hospitalPayload);
    }
}
