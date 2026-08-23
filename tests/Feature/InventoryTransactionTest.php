<?php

namespace Tests\Feature;

use App\Models\BloodGroup;
use App\Models\BloodInventory;
use App\Services\BloodInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_atomic_inventory_reservation_under_transaction()
    {
        $group = BloodGroup::create(['name' => 'B+', 'description' => 'B Positive']);

        BloodInventory::create([
            'blood_group_id' => $group->id,
            'quantity' => 2250,
            'units_available' => 5,
            'units_requested' => 0,
            'collection_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addDays(35)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $service = app(BloodInventoryService::class);
        $success = $service->reserveUnits($group->id, 3);

        $this->assertTrue($success);

        $inventory = BloodInventory::where('blood_group_id', $group->id)->first();
        $this->assertEquals(2, $inventory->units_available);
        $this->assertEquals(3, $inventory->units_requested);
    }

    public function test_inventory_reservation_fails_when_requested_units_exceed_available()
    {
        $group = BloodGroup::create(['name' => 'AB-', 'description' => 'AB Negative']);

        BloodInventory::create([
            'blood_group_id' => $group->id,
            'quantity' => 900,
            'units_available' => 2,
            'units_requested' => 0,
            'collection_date' => now()->subDays(2)->format('Y-m-d'),
            'expiry_date' => now()->addDays(40)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $service = app(BloodInventoryService::class);
        $success = $service->reserveUnits($group->id, 5);

        $this->assertFalse($success);

        $inventory = BloodInventory::where('blood_group_id', $group->id)->first();
        $this->assertEquals(2, $inventory->units_available);
    }
}
