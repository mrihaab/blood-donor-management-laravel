<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_2fa_settings_and_generate_secret()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($admin)->get(route('admin.2fa.show'));

        $response->assertStatus(200);
        $this->assertNotNull($admin->fresh()->google2fa_secret);
        $this->assertGreaterThanOrEqual(16, strlen($admin->fresh()->google2fa_secret));
    }

    public function test_admin_can_enable_2fa_with_valid_totp_code()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey(16);
        $admin->google2fa_secret = $secret;
        $admin->save();

        $validCode = $google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($admin->fresh())->post(route('admin.2fa.enable'), [
            'code' => $validCode,
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue((bool)$admin->fresh()->google2fa_enabled);
        $this->assertNotEmpty(json_decode($admin->fresh()->two_factor_recovery_codes));
    }

    public function test_admin_2fa_enable_fails_with_invalid_code()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey(16);
        $admin->google2fa_secret = $secret;
        $admin->save();

        $response = $this->actingAs($admin->fresh())->post(route('admin.2fa.enable'), [
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors(['code']);
        $this->assertFalse((bool)$admin->fresh()->google2fa_enabled);
    }
}
