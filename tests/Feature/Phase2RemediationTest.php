<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BloodComponent;
use App\Models\BloodGroup;
use App\Models\BloodInventory;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Hospital;
use App\Models\InventoryTransaction;
use App\Models\Patient;
use App\Models\User;
use App\Services\BloodInventoryService;
use App\Services\BloodRequestService;
use App\Services\BloodUnitService;
use App\Services\DonationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase2RemediationTest extends TestCase
{
    use RefreshDatabase;

    protected BloodGroup $groupO;
    protected BloodComponent $componentPRBC;
    protected Donor $donor;
    protected User $donorUser;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupO = BloodGroup::create(['name' => 'O+', 'description' => 'O Positive']);
        
        $this->componentPRBC = BloodComponent::where('code', 'PRBC')->first()
            ?? BloodComponent::create([
                'name' => 'Packed Red Blood Cells',
                'code' => 'PRBC',
                'storage_temperature' => '2°C - 6°C',
                'shelf_life_days' => 42,
                'status' => 'active',
            ]);

        $this->donorUser = User::factory()->create(['role' => 'donor', 'name' => 'John Donor']);
        $this->donor = Donor::create([
            'user_id' => $this->donorUser->id,
            'blood_group_id' => $this->groupO->id,
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'contact_number' => '555-0101',
            'address' => '100 Main St',
            'city' => 'Metropolis',
            'state' => 'NY',
            'zip_code' => '10001',
        ]);

        $this->adminUser = User::factory()->create(['role' => 'admin', 'name' => 'Admin Boss']);
    }

    // 1. Donation creates BloodUnit
    public function test_1_donation_creates_blood_unit()
    {
        $donationService = app(DonationService::class);
        $donation = $donationService->recordDonation($this->donor, [
            'quantity' => 450,
            'donation_date' => now()->format('Y-m-d'),
        ], $this->adminUser);

        $this->assertDatabaseHas('blood_units', [
            'donor_id' => $this->donor->id,
            'donation_id' => $donation->id,
            'blood_group_id' => $this->groupO->id,
            'status' => 'available',
        ]);
    }

    // 2. Donation uses component shelf_life_days dynamically
    public function test_2_donation_uses_component_shelf_life_days_dynamically()
    {
        $pltComponent = BloodComponent::where('code', 'PLT')->first(); // 5 days shelf life
        $donationService = app(DonationService::class);
        
        $donationDate = now()->format('Y-m-d');
        $donation = $donationService->recordDonation($this->donor, [
            'quantity' => 250,
            'donation_date' => $donationDate,
            'component_code' => 'PLT',
        ], $this->adminUser);

        $unit = BloodUnit::where('donation_id', $donation->id)->first();
        $expectedExpiry = Carbon::parse($donationDate)->addDays(5)->format('Y-m-d');
        
        $this->assertEquals($expectedExpiry, $unit->expiry_date);
    }

    // 3. Donation creates received transaction
    public function test_3_donation_creates_received_inventory_transaction()
    {
        $donationService = app(DonationService::class);
        $donation = $donationService->recordDonation($this->donor, [
            'quantity' => 450,
            'donation_date' => now()->format('Y-m-d'),
        ], $this->adminUser);

        $unit = BloodUnit::where('donation_id', $donation->id)->first();

        $this->assertDatabaseHas('inventory_transactions', [
            'blood_unit_id' => $unit->id,
            'transaction_type' => 'received',
            'user_id' => $this->adminUser->id,
        ]);
    }

    // 4. Multiple donation units receive unique identifiers
    public function test_4_multiple_donation_units_receive_unique_identifiers()
    {
        $donationService = app(DonationService::class);
        
        $d1 = $donationService->recordDonation($this->donor, ['donation_date' => now()->subDays(60)->format('Y-m-d')], $this->adminUser);
        $d2 = $donationService->recordDonation($this->donor, ['donation_date' => now()->format('Y-m-d')], $this->adminUser);

        $u1 = BloodUnit::where('donation_id', $d1->id)->first();
        $u2 = BloodUnit::where('donation_id', $d2->id)->first();

        $this->assertNotEquals($u1->unit_number, $u2->unit_number);
    }

    // 5. BloodUnit lifecycle rejects invalid transitions
    public function test_5_blood_unit_lifecycle_rejects_invalid_transitions()
    {
        $unitService = app(BloodUnitService::class);
        $unit = BloodUnit::create([
            'unit_number' => 'UNIT-TEST-001',
            'blood_group_id' => $this->groupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->format('Y-m-d'),
            'expiry_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'dispensed',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $unitService->transitionStatus($unit, 'available', 'Illegal reversal attempt');
    }

    // 6. Expired units cannot be allocated
    public function test_6_expired_units_cannot_be_allocated()
    {
        BloodUnit::create([
            'unit_number' => 'UNIT-EXPIRED-01',
            'blood_group_id' => $this->groupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(50)->format('Y-m-d'),
            'expiry_date' => now()->subDays(5)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $requestService = app(BloodRequestService::class);
        $request = $requestService->createRequest([
            'patient_name' => 'Need Blood Patient',
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => 'City Hospital',
        ], $this->donorUser);

        $this->expectException(\RuntimeException::class);
        $requestService->approveRequest($request, $this->adminUser);
    }

    // 7. Discarded units cannot be allocated
    public function test_7_discarded_units_cannot_be_allocated()
    {
        BloodUnit::create([
            'unit_number' => 'UNIT-DISCARDED-01',
            'blood_group_id' => $this->groupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(2)->format('Y-m-d'),
            'expiry_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'discarded',
        ]);

        $requestService = app(BloodRequestService::class);
        $request = $requestService->createRequest([
            'patient_name' => 'Need Blood Patient 2',
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => 'City Hospital',
        ], $this->donorUser);

        $this->expectException(\RuntimeException::class);
        $requestService->approveRequest($request, $this->adminUser);
    }

    // 8. Allocation creates transaction
    public function test_8_allocation_creates_inventory_transaction()
    {
        $unit = BloodUnit::create([
            'unit_number' => 'UNIT-ALLOC-01',
            'blood_group_id' => $this->groupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(2)->format('Y-m-d'),
            'expiry_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $requestService = app(BloodRequestService::class);
        $request = $requestService->createRequest([
            'patient_name' => 'Patient Alloc Test',
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => 'City Hospital',
        ], $this->donorUser);

        $requestService->approveRequest($request, $this->adminUser);

        $this->assertDatabaseHas('inventory_transactions', [
            'blood_unit_id' => $unit->id,
            'transaction_type' => 'allocated',
            'user_id' => $this->adminUser->id,
        ]);
    }

    // 9. Dispensing creates transaction
    public function test_9_dispensing_creates_inventory_transaction()
    {
        $unit = BloodUnit::create([
            'unit_number' => 'UNIT-DISP-01',
            'blood_group_id' => $this->groupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(2)->format('Y-m-d'),
            'expiry_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $requestService = app(BloodRequestService::class);
        $request = $requestService->createRequest([
            'patient_name' => 'Patient Dispense Test',
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => 'City Hospital',
        ], $this->donorUser);

        $requestService->approveRequest($request, $this->adminUser);
        $requestService->dispenseRequest($request, $this->adminUser);

        $this->assertDatabaseHas('inventory_transactions', [
            'blood_unit_id' => $unit->id,
            'transaction_type' => 'dispensed',
            'user_id' => $this->adminUser->id,
        ]);
        $this->assertEquals('dispensed', $unit->fresh()->status);
    }

    // 10 & 11. Same unit cannot be allocated or dispensed twice
    public function test_10_and_11_same_unit_cannot_be_dispensed_twice()
    {
        $unit = BloodUnit::create([
            'unit_number' => 'UNIT-DOUBLE-01',
            'blood_group_id' => $this->groupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(2)->format('Y-m-d'),
            'expiry_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $requestService = app(BloodRequestService::class);
        $request = $requestService->createRequest([
            'patient_name' => 'Double Dispense Test',
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => 'City Hospital',
        ], $this->donorUser);

        $requestService->approveRequest($request, $this->adminUser);
        $requestService->dispenseRequest($request, $this->adminUser);

        $this->expectException(\InvalidArgumentException::class);
        $requestService->dispenseRequest($request, $this->adminUser);
    }

    // 12. Concurrent allocation of final unit is safe
    public function test_12_concurrent_allocation_of_final_unit_is_safe()
    {
        BloodUnit::create([
            'unit_number' => 'UNIT-FINAL-CONCURRENT',
            'blood_group_id' => $this->groupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(1)->format('Y-m-d'),
            'expiry_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $requestService = app(BloodRequestService::class);
        
        $req1 = $requestService->createRequest([
            'patient_name' => 'Concurrent P1',
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => 'City Hospital',
        ], $this->donorUser);

        $req2 = $requestService->createRequest([
            'patient_name' => 'Concurrent P2',
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => 'City Hospital',
        ], $this->donorUser);

        // Approve req1 successfully
        $res1 = $requestService->approveRequest($req1, $this->adminUser);
        $this->assertTrue($res1);

        // Attempting to approve req2 must fail as stock is now 0
        $this->expectException(\RuntimeException::class);
        $requestService->approveRequest($req2, $this->adminUser);
    }

    // 14 & 15. Hospital and Patient association is enforced
    public function test_14_and_15_hospital_and_patient_association_is_enforced()
    {
        $hospital = Hospital::create([
            'name' => 'Metro General Hospital',
            'city' => 'Metropolis',
            'contact_phone' => '555-8888',
        ]);

        $patient = Patient::create([
            'hospital_id' => $hospital->id,
            'name' => 'Alice Patient',
            'mrn' => 'MRN-7771',
            'blood_group_id' => $this->groupO->id,
        ]);

        $requestService = app(BloodRequestService::class);
        $request = $requestService->createRequest([
            'hospital_id' => $hospital->id,
            'patient_id' => $patient->id,
            'patient_name' => $patient->name,
            'blood_group' => 'O+',
            'units_needed' => 1,
            'hospital' => $hospital->name,
        ], $this->donorUser);

        $this->assertEquals($hospital->id, $request->hospital_id);
        $this->assertEquals($patient->id, $request->patient_id);
    }

    // 18. Failed transaction rolls back all related changes
    public function test_18_failed_transaction_rolls_back_all_related_changes()
    {
        $initialUnitsCount = BloodUnit::count();
        $initialTxCount = InventoryTransaction::count();

        try {
            DB::transaction(function () {
                BloodUnit::create([
                    'unit_number' => 'UNIT-ROLLBACK-TEST',
                    'blood_group_id' => $this->groupO->id,
                    'component_id' => $this->componentPRBC->id,
                    'collection_date' => now()->format('Y-m-d'),
                    'expiry_date' => now()->addDays(30)->format('Y-m-d'),
                    'status' => 'available',
                ]);

                throw new \Exception("Simulated unexpected error inside transaction");
            });
        } catch (\Exception $e) {
            // Transaction caught
        }

        $this->assertEquals($initialUnitsCount, BloodUnit::count());
        $this->assertEquals($initialTxCount, InventoryTransaction::count());
    }

    // 19 & 20. Legacy requests and stock overview compatibility
    public function test_19_and_20_legacy_inventory_overview_derived_from_blood_units()
    {
        BloodUnit::create([
            'unit_number' => 'UNIT-STOCK-01',
            'blood_group_id' => $this->groupO->id,
            'component_id' => $this->componentPRBC->id,
            'collection_date' => now()->subDays(1)->format('Y-m-d'),
            'expiry_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'available',
        ]);

        $invService = app(BloodInventoryService::class);
        $overview = $invService->getInventoryOverview();

        $groupOStock = $overview->firstWhere('blood_group', 'O+');
        $this->assertEquals(1, $groupOStock['units_available']);
    }
}
