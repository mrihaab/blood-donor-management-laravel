<?php

namespace App\Services;

use App\Models\BloodInventory;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DonationService
{
    protected DonorEligibilityService $eligibilityService;

    public function __construct(DonorEligibilityService $eligibilityService)
    {
        $this->eligibilityService = $eligibilityService;
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

            $expiryDate = Carbon::parse($donation->donation_date)->addDays(42);

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

            $donor->update([
                'last_donation_date' => $donation->donation_date,
            ]);

            activity()
                ->causedBy($createdBy ?? auth()->user())
                ->performedOn($donation)
                ->log("Donation of {$donation->quantity}ml recorded for donor {$donor->user->name}");

            return $donation;
        });
    }
}
