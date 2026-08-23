<?php

namespace App\Services;

use App\Models\BloodComponent;
use App\Models\BloodInventory;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DonationService
{
    protected DonorEligibilityService $eligibilityService;
    protected BloodUnitService $bloodUnitService;
    protected InventoryTransactionService $transactionService;

    public function __construct(
        DonorEligibilityService $eligibilityService,
        BloodUnitService $bloodUnitService,
        InventoryTransactionService $transactionService
    ) {
        $this->eligibilityService = $eligibilityService;
        $this->bloodUnitService = $bloodUnitService;
        $this->transactionService = $transactionService;
    }

    public function recordDonation(Donor $donor, array $data, ?User $createdBy = null): Donation
    {
        $eligibility = $this->eligibilityService->checkEligibility($donor);

        if (!$eligibility['eligible']) {
            throw new \Exception("Donor is not eligible to donate yet. Next eligible date: " . $eligibility['next_eligible_date']->format('Y-m-d'));
        }

        return DB::transaction(function () use ($donor, $data, $createdBy) {
            $donation = Donation::create([
                'donor_id' => $donor->id,
                'blood_group_id' => $donor->blood_group_id,
                'quantity' => $data['quantity'] ?? 450,
                'status' => 'completed',
                'donation_date' => $data['donation_date'] ?? now()->format('Y-m-d'),
                'collection_center' => $data['collection_center'] ?? null,
                'created_by' => $createdBy ? $createdBy->id : null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Determine blood component (default PRBC if unspecified)
            $componentCode = $data['component_code'] ?? 'PRBC';
            $component = BloodComponent::where('code', $componentCode)->first()
                ?? BloodComponent::where('code', 'PRBC')->first()
                ?? BloodComponent::first();

            // Create physical BloodUnit with dynamic shelf-life calculation
            $bloodUnit = $this->bloodUnitService->createUnitFromDonation(
                $donor,
                $donation,
                $component,
                $data['storage_location'] ?? 'Main Refrigerator Bay 1'
            );

            // Log auditable InventoryTransaction
            $this->transactionService->logTransaction(
                bloodUnit: $bloodUnit,
                transactionType: 'received',
                previousQuantity: 0,
                quantityChanged: $donation->quantity,
                resultingQuantity: $donation->quantity,
                reason: "Donation intake for donor #{$donor->id}",
                actor: $createdBy ?? auth()->user(),
                referenceType: Donation::class,
                referenceId: $donation->id
            );

            // Update legacy aggregate table blood_inventory as derived sync layer
            $expiryDate = Carbon::parse($donation->donation_date)->addDays($component->shelf_life_days);
            BloodInventory::create([
                'blood_group_id' => $donor->blood_group_id,
                'donor_id' => $donor->id,
                'quantity' => $donation->quantity,
                'units_available' => 1,
                'units_requested' => 0,
                'collection_date' => $donation->donation_date,
                'expiry_date' => $expiryDate->format('Y-m-d'),
                'status' => 'available',
            ]);

            // Update donor last_donation_date
            $donor->update([
                'last_donation_date' => $donation->donation_date,
            ]);

            activity()
                ->causedBy($createdBy ?? auth()->user())
                ->performedOn($donation)
                ->log("Donation of {$donation->quantity}ml recorded with BloodUnit #{$bloodUnit->unit_number}");

            return $donation;
        });
    }
}
