<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_as_donor(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Donor User',
            'email' => 'testdonor@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        
        $user = User::where('email', 'testdonor@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('donor', $user->role);
    }

    public function test_malicious_registration_payload_cannot_escalate_role_to_admin(): void
    {
        $response = $this->post('/register', [
            'name' => 'Malicious Attacker',
            'email' => 'hacker@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin', // Attack payload attempting privilege escalation
        ]);

        $user = User::where('email', 'hacker@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('donor', $user->role); // Must be strictly donor
        $this->assertNotEquals('admin', $user->role);
    }
}
