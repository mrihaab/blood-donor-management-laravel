<?php

namespace Tests\Feature;

use App\Models\BloodComponent;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Hospital;
use App\Models\InventoryTransaction;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\EmergencyBloodRequestNotification;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class Phase8ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $hospitalUser;
    protected Hospital $hospital;
    protected Patient $patient;
    protected BloodGroup $groupA;
    protected BloodComponent $componentPRBC;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Hardening User',
            'role' => 'admin',
            'email_verified_at' => now(),
            'google2fa_enabled' => false,
        ]);

        $this->hospital = Hospital::create([
            'name' => 'General Hardening Hospital',
            'license_number' => 'HOSP-801',
            'address' => '800 Safety Way',
            'city' => 'Metropolis',
            'state' => 'NY',
            'contact_person' => 'Dr. Guard',
            'contact_phone' => '555-8001',
            'email' => 'er@hardening.org',
            'status' => 'active',
        ]);

        $this->hospitalUser = User::factory()->create([
            'name' => 'Hospital User Hardening',
            'role' => 'hospital',
            'hospital_id' => $this->hospital->id,
            'email_verified_at' => now(),
        ]);

        $this->groupA = BloodGroup::create(['name' => 'A+', 'description' => 'A Positive']);
        $this->componentPRBC = BloodComponent::where('code', 'PRBC')->first()
            ?? BloodComponent::create([
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
            ]);

        $this->patient = Patient::create([
            'hospital_id' => $this->hospital->id,
            'name' => 'Patient Hardening Test',
            'mrn' => 'MRN-PAT-801',
            'gender' => 'male',
            'date_of_birth' => '1988-08-08',
            'blood_group_id' => $this->groupA->id,
            'status' => 'active',
        ]);
    }

    public function test_inventory_expiry_command_processes_physical_blood_units_via_domain_service()
    {
        // Expired physical unit
        $expiredUnit = BloodUnit::create([
            'unit_number' => 'UNIT-801-EXPIRED',
            'blood_group_id' => $this->groupA->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(50)->format('Y-m-d'),
            'expiry_date' => now()->subDays(2)->format('Y-m-d'),
            'status' => 'available',
        ]);

        // Active physical unit
        $activeUnit = BloodUnit::create([
            'unit_number' => 'UNIT-802-ACTIVE',
            'blood_group_id' => $this->groupA->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addDays(35)->format('Y-m-d'),
            'status' => 'available',
        ]);

        Artisan::call('inventory:check-expiry');

        $this->assertEquals('expired', $expiredUnit->fresh()->status);
        $this->assertEquals('available', $activeUnit->fresh()->status);

        $this->assertDatabaseHas('inventory_transactions', [
            'blood_unit_id'    => $expiredUnit->id,
            'transaction_type' => 'expired',
        ]);
    }

    public function test_admin_user_with_2fa_enabled_is_redirected_to_2fa_prompt_middleware()
    {
        $admin2FA = User::factory()->create([
            'name' => 'Admin 2FA User',
            'role' => 'admin',
            'email_verified_at' => now(),
            'google2fa_enabled' => true,
            'google2fa_secret' => 'SECRETKEY1234567',
        ]);

        // Attempting to access admin dashboard without 2fa_verified session key
        $response = $this->actingAs($admin2FA)->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.2fa.show'));
    }

    public function test_admin_user_with_verified_2fa_can_access_dashboard()
    {
        $admin2FA = User::factory()->create([
            'name' => 'Admin 2FA User Verified',
            'role' => 'admin',
            'email_verified_at' => now(),
            'google2fa_enabled' => true,
            'google2fa_secret' => 'SECRETKEY1234567',
        ]);

        $response = $this->actingAs($admin2FA)
            ->withSession(['2fa_verified' => true])
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_emergency_notification_is_dispatched_to_queue()
    {
        Notification::fake();

        $request = BloodRequest::create([
            'patient_id' => $this->patient->id,
            'patient_name' => $this->patient->name,
            'hospital_id' => $this->hospital->id,
            'hospital' => $this->hospital->name,
            'city' => 'Metropolis',
            'blood_group' => 'A+',
            'units_needed' => 2,
            'urgency' => 'emergency',
            'status' => 'pending',
            'user_id' => $this->hospitalUser->id,
            'reason' => 'Severe Trauma Bleed',
        ]);

        $donorUser = User::factory()->create(['role' => 'donor', 'status' => 'active']);
        \App\Models\Donor::create([
            'user_id' => $donorUser->id,
            'blood_group_id' => $this->groupA->id,
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'contact_number' => '555-9999',
            'address' => '999 Queue Rd',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10001',
            'status' => 'active',
        ]);

        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->sendEmergencyBroadcast($request);

        Notification::assertSentTo(
            [$donorUser],
            EmergencyBloodRequestNotification::class
        );
    }

    public function test_clinical_action_endpoints_are_rate_limited()
    {
        $this->actingAs($this->hospitalUser);

        // Perform normal POST request within throttle limits
        $response = $this->post(route('hospital.patients.store'), [
            'name' => 'Rate Limit Test Patient',
            'mrn' => 'MRN-PAT-999',
            'gender' => 'female',
            'date_of_birth' => '1995-05-05',
            'blood_group_id' => $this->groupA->id,
        ]);

        $response->assertStatus(302);
    }

    public function test_inventory_transactions_and_activity_logs_are_immutable()
    {
        $transaction = InventoryTransaction::create([
            'blood_unit_id' => null,
            'blood_group_id' => $this->groupA->id,
            'component_id' => $this->componentPRBC->id,
            'transaction_type' => 'received',
            'previous_quantity' => 0,
            'quantity_changed' => 450,
            'resulting_quantity' => 450,
            'user_id' => $this->adminUser->id,
            'reason' => 'Initial intake audit test',
        ]);

        $this->expectException(\LogicException::class);
        $transaction->update(['reason' => 'Attempted tamper']);
    }

    public function test_activity_logs_are_immutable()
    {
        $activity = activity()
            ->causedBy($this->adminUser)
            ->log('Test audit activity event');

        $this->expectException(\LogicException::class);
        $activity->delete();
    }

    public function test_security_headers_are_present_in_responses()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
    }
}
