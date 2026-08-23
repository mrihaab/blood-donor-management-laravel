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
use App\Services\InventoryTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2DatabaseDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_medical_blood_components_are_seeded()
    {
        $this->assertDatabaseHas('blood_components', ['code' => 'WB', 'name' => 'Whole Blood']);
        $this->assertDatabaseHas('blood_components', ['code' => 'PRBC', 'name' => 'Packed Red Blood Cells']);
        $this->assertDatabaseHas('blood_components', ['code' => 'PLT', 'name' => 'Platelets']);
        $this->assertDatabaseHas('blood_components', ['code' => 'FFP', 'name' => 'Fresh Frozen Plasma']);
        $this->assertDatabaseHas('blood_components', ['code' => 'CRYO', 'name' => 'Cryoprecipitate']);
    }

    public function test_hospital_and_patient_entity_relationships()
    {
        $hospital = Hospital::create([
            'name' => 'St. Jude Children Hospital',
            'city' => 'Metropolis',
            'contact_phone' => '555-9876',
            'email' => 'contact@stjude.org',
            'status' => 'active',
        ]);

        $bloodGroup = BloodGroup::create(['name' => 'O+', 'description' => 'O Positive']);

        $patient = Patient::create([
            'hospital_id' => $hospital->id,
            'name' => 'Jane Doe',
            'mrn' => 'MRN-88201',
            'gender' => 'female',
            'date_of_birth' => '1995-04-12',
            'blood_group_id' => $bloodGroup->id,
            'contact_number' => '555-3333',
        ]);

        $this->assertEquals($hospital->id, $patient->hospital->id);
        $this->assertEquals('MRN-88201', $hospital->patients->first()->mrn);
    }

    public function test_unit_bag_barcode_tracking_creation()
    {
        $group = BloodGroup::create(['name' => 'AB+', 'description' => 'AB Positive']);
        $component = BloodComponent::where('code', 'PRBC')->first();

        $unit = BloodUnit::create([
            'unit_number' => 'ISBT-US-2026-9901',
            'blood_group_id' => $group->id,
            'component_id' => $component->id,
            'collection_date' => now()->subDays(2)->format('Y-m-d'),
            'expiry_date' => now()->addDays(40)->format('Y-m-d'),
            'volume_ml' => 450,
            'storage_location' => 'Main Fridge Bay 3',
            'status' => 'available',
        ]);

        $this->assertDatabaseHas('blood_units', [
            'unit_number' => 'ISBT-US-2026-9901',
            'status' => 'available',
        ]);
        $this->assertEquals('Packed Red Blood Cells', $unit->component->name);
    }

    public function test_auditable_inventory_transaction_logging()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $group = BloodGroup::create(['name' => 'A-', 'description' => 'A Negative']);
        $component = BloodComponent::where('code', 'WB')->first();

        $unit = BloodUnit::create([
            'unit_number' => 'ISBT-US-2026-4402',
            'blood_group_id' => $group->id,
            'component_id' => $component->id,
            'collection_date' => now()->subDays(1)->format('Y-m-d'),
            'expiry_date' => now()->addDays(34)->format('Y-m-d'),
            'volume_ml' => 450,
            'status' => 'available',
        ]);

        $service = app(InventoryTransactionService::class);
        $transaction = $service->logTransaction(
            bloodUnit: $unit,
            transactionType: 'received',
            previousQuantity: 0,
            quantityChanged: 450,
            resultingQuantity: 450,
            reason: 'Routine donor phlebotomy intake',
            actor: $admin
        );

        $this->assertDatabaseHas('inventory_transactions', [
            'blood_unit_id' => $unit->id,
            'transaction_type' => 'received',
            'resulting_quantity' => 450,
            'user_id' => $admin->id,
        ]);
    }
}
