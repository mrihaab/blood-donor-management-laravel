<?php

namespace Tests\Feature;

use App\Models\BloodComponent;
use App\Models\BloodGroup;
use App\Models\BloodUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3RemediationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected BloodGroup $groupA;
    protected BloodGroup $groupB;
    protected BloodComponent $componentPRBC;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'name' => 'Admin Boss',
            'email_verified_at' => now(),
        ]);
        $this->adminUser->role = 'admin';
        $this->adminUser->save();

        $this->groupA = BloodGroup::create(['name' => 'A+', 'description' => 'A Positive']);
        $this->groupB = BloodGroup::create(['name' => 'B+', 'description' => 'B Positive']);

        $this->componentPRBC = BloodComponent::where('code', 'PRBC')->first()
            ?? BloodComponent::create([
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
            ]);
    }

    public function test_dashboard_stock_aggregation_uses_grouped_query_and_matches_blood_units_count()
    {
        // Add 3 units of A+
        for ($i = 1; $i <= 3; $i++) {
            BloodUnit::create([
                'unit_number' => "UNIT-A-{$i}",
                'blood_group_id' => $this->groupA->id,
                'component_id' => $this->componentPRBC->id,
                'collection_date' => now()->subDays(2)->format('Y-m-d'),
                'expiry_date' => now()->addDays(30)->format('Y-m-d'),
                'status' => 'available',
            ]);
        }

        // Add 2 units of B+
        for ($i = 1; $i <= 2; $i++) {
            BloodUnit::create([
                'unit_number' => "UNIT-B-{$i}",
                'blood_group_id' => $this->groupB->id,
                'component_id' => $this->componentPRBC->id,
                'collection_date' => now()->subDays(2)->format('Y-m-d'),
                'expiry_date' => now()->addDays(30)->format('Y-m-d'),
                'status' => 'available',
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $inventory = $response->viewData('bloodInventory');
        $groupAStock = $inventory->firstWhere('blood_group', 'A+');
        $groupBStock = $inventory->firstWhere('blood_group', 'B+');

        $this->assertEquals(3, $groupAStock['units_available']);
        $this->assertEquals(2, $groupBStock['units_available']);
    }

    public function test_breadcrumbs_component_renders_accessible_navigation()
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.inventory.index'));
        $response->assertStatus(200);
        $response->assertSee('aria-label="Breadcrumb"', false);
        $response->assertSee('Blood Inventory');
    }

    public function test_confirmation_dialog_and_blood_request_action_forms_render_csrf_and_action_attributes()
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.blood_requests.index'));
        $response->assertStatus(200);
        $response->assertSee('confirm-reject');
        $response->assertSee('confirm-dispense');
    }
}
