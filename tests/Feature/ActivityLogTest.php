<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_creation_records_activity_log()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'John Activity',
            'email' => 'john.activity@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'donor',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $admin->id,
            'description' => 'Created user account for john.activity@example.com with role donor',
        ]);
    }
}
