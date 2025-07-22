<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\BloodInventory;
use App\Models\Donor;
use App\Models\ActivityLog;
use Carbon\Carbon;

class BloodDonationService
{
    /**
     * Check if a donor is eligible to donate
     */
    public function isDonorEligible(Donor $donor): array
    {
        $lastDonation = $donor->donations()->latest()->first();
        $daysSinceLastDonation = $lastDonation 
            ? now()->diffInDays($lastDonation->created_at) 
            : 999;

        $minDaysBetweenDonations = 90; // 3 months
        $isEligible = $daysSinceLastDonation >= $minDaysBetweenDonations;

        return [
            'eligible' => $isEligible,
            'days_since_last' => $daysSinceLastDonation,
            'days_until_eligible' => $isEligible ? 0 : ($minDaysBetweenDonations - $daysSinceLastDonation),
            'last_donation_date' => $lastDonation?->created_at,
            'next_eligible_date' => $lastDonation 
                ? $lastDonation->created_at->addDays($minDaysBetweenDonations)
                : now()
        ];
    }

    /**
     * Process a blood donation
     */
    public function processDonation(Donor $donor, array $data): Donation
    {
        $eligibility = $this->isDonorEligible($donor);
        
        if (!$eligibility['eligible']) {
            throw new \Exception("Donor is not eligible to donate yet. Next eligible date: " . $eligibility['next_eligible_date']->format('Y-m-d'));
        }

        // Create donation record
        $donation = Donation::create([
            'donor_id' => $donor->id,
            'blood_group_id' => $donor->blood_group_id,
            'quantity' => $data['quantity'] ?? 450, // Default 450ml
            'status' => 'completed',
            'donation_date' => $data['donation_date'] ?? now(),
            'notes' => $data['notes'] ?? null,
        ]);

        // Add to blood inventory
        $expiryDate = Carbon::parse($donation->donation_date)->addDays(42); // Blood expires in 42 days
        
        BloodInventory::create([
            'blood_group_id' => $donor->blood_group_id,
            'quantity' => $donation->quantity,
            'collection_date' => $donation->donation_date,
            'expiry_date' => $expiryDate,
            'donor_id' => $donor->id,
            'status' => 'available'
        ]);

        // Update donor's last donation date
        $donor->update([
            'last_donation_date' => $donation->donation_date
        ]);

        // Log activity
        ActivityLog::create([
            'description' => "Blood donation completed by {$donor->user->name} ({$donation->quantity}ml of {$donor->bloodGroup->name})",
            'type' => 'donation',
            'user_id' => $donor->user_id,
        ]);

        return $donation;
    }

    /**
     * Get blood availability by type
     */
    public function getBloodAvailability(): array
    {
        return BloodInventory::available()
            ->join('blood_groups', 'blood_inventory.blood_group_id', '=', 'blood_groups.id')
            ->selectRaw('blood_groups.name as blood_group, SUM(quantity) as total_quantity, COUNT(*) as units_count')
            ->groupBy('blood_groups.name', 'blood_groups.id')
            ->get()
            ->toArray();
    }

    /**
     * Find compatible donors for a blood request
     */
    public function findCompatibleDonors(string $requestedBloodGroup, string $city = null): array
    {
        // Blood compatibility matrix
        $compatibility = [
            'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-'],
            'AB-' => ['AB-', 'A-', 'B-', 'O-'],
            'A+' => ['A+', 'A-', 'O+', 'O-'],
            'A-' => ['A-', 'O-'],
            'B+' => ['B+', 'B-', 'O+', 'O-'],
            'B-' => ['B-', 'O-'],
            'O+' => ['O+', 'O-'],
            'O-' => ['O-']
        ];

        $compatibleGroups = $compatibility[$requestedBloodGroup] ?? [$requestedBloodGroup];

        $query = Donor::whereHas('bloodGroup', function($q) use ($compatibleGroups) {
                $q->whereIn('name', $compatibleGroups);
            })
            ->where('is_available', true)
            ->with(['user', 'bloodGroup']);

        if ($city) {
            $query->where('city', $city);
        }

        // Check eligibility
        $donors = $query->get()->filter(function($donor) {
            return $this->isDonorEligible($donor)['eligible'];
        });

        return $donors->toArray();
    }

    /**
     * Mark expired blood units
     */
    public function markExpiredBlood(): int
    {
        $expiredCount = BloodInventory::where('status', 'available')
            ->where('expiry_date', '<=', now())
            ->update(['status' => 'expired']);

        if ($expiredCount > 0) {
            ActivityLog::create([
                'description' => "Marked {$expiredCount} blood units as expired",
                'type' => 'system',
            ]);
        }

        return $expiredCount;
    }
}
