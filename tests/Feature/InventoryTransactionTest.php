<?php

namespace Tests\Feature;

use App\Models\BloodComponent;
use App\Models\BloodGroup;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Services\BloodInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_atomic_inventory_reservation_under_transaction()
    {
        $group = BloodGroup::create(['name' => 'B+', 'description' => 'B Positive']);
        $component = BloodComponent::where('code', 'PRBC')->first()
            ?? BloodComponent::create([
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
            ]);

        for ($i = 1; $i <= 5; $i++) {
            BloodUnit::create([
                'unit_number' => "UNIT-BPLUS-{$i}",
                'blood_group_id' => $group->id,
                'component_id' => $component->id,
                'collection_date' => now()->subDays(5)->format('Y-m-d'),
                'expiry_date' => now()->addDays(35)->format('Y-m-d'),
                'status' => 'available',
            ]);
        }

        $service = app(BloodInventoryService::class);
        $success = $service->reserveUnits($group->id, 3);

        $this->assertTrue($success);

        $overview = $service->getInventoryOverview()->firstWhere('blood_group', 'B+');
        $this->assertEquals(2, $overview['units_available']);
        $this->assertEquals(3, $overview['units_requested']);
    }

    public function test_inventory_reservation_fails_when_requested_units_exceed_available()
    {
        $group = BloodGroup::create(['name' => 'AB-', 'description' => 'AB Negative']);
        $component = BloodComponent::where('code', 'PRBC')->first()
            ?? BloodComponent::create([
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
            ]);

        for ($i = 1; $i <= 2; $i++) {
            BloodUnit::create([
                'unit_number' => "UNIT-ABNEG-{$i}",
                'blood_group_id' => $group->id,
                'component_id' => $component->id,
                'collection_date' => now()->subDays(2)->format('Y-m-d'),
                'expiry_date' => now()->addDays(40)->format('Y-m-d'),
                'status' => 'available',
            ]);
        }

        $service = app(BloodInventoryService::class);
        $success = $service->reserveUnits($group->id, 5);

        $this->assertFalse($success);

        $overview = $service->getInventoryOverview()->firstWhere('blood_group', 'AB-');
        $this->assertEquals(2, $overview['units_available']);
    }
}
