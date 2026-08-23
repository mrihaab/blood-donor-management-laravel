<?php

namespace App\Services;

use App\Models\BloodComponent;
use App\Models\BloodGroup;
use App\Models\BloodUnit;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BloodUnitService
{
    /**
     * State machine allowed transitions map.
     */
    protected const ALLOWED_TRANSITIONS = [
        'available' => ['reserved', 'allocated', 'expired', 'discarded'],
        'reserved'  => ['allocated', 'available', 'expired', 'discarded'],
        'allocated' => ['dispensed', 'reserved', 'available', 'discarded'],
        'dispensed' => [], // Terminal state
        'expired'   => [], // Terminal state
        'discarded' => [], // Terminal state
    ];

    /**
     * Generate an internal unique unit barcode identifier.
     */
    public function generateUnitNumber(): string
    {
        do {
            $unitNumber = 'UNIT-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (BloodUnit::where('unit_number', $unitNumber)->exists());

        return $unitNumber;
    }

    /**
     * Validate and transition a BloodUnit to a new status server-side.
     */
    public function transitionStatus(BloodUnit $unit, string $newStatus, ?string $reason = null, ?User $actor = null): bool
    {
        $currentStatus = $unit->status;

        if ($currentStatus === $newStatus) {
            throw new \InvalidArgumentException("Unit {$unit->unit_number} is already in '{$newStatus}' state.");
        }

        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (!in_array($newStatus, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid status transition for unit {$unit->unit_number}: Cannot transition from '{$currentStatus}' to '{$newStatus}'.");
        }

        return DB::transaction(function () use ($unit, $newStatus, $reason, $actor) {
            $previousStatus = $unit->status;
            $unit->update(['status' => $newStatus]);

            activity()
                ->causedBy($actor ?? auth()->user())
                ->performedOn($unit)
                ->log("BloodUnit #{$unit->unit_number} status changed from {$previousStatus} to {$newStatus}. Reason: " . ($reason ?? 'Status update'));

            return true;
        });
    }

    /**
     * Create a physical blood unit from a donation.
     */
    public function createUnitFromDonation(Donor $donor, Donation $donation, BloodComponent $component, ?string $storageLocation = null): BloodUnit
    {
        return DB::transaction(function () use ($donor, $donation, $component, $storageLocation) {
            $collectionDate = Carbon::parse($donation->donation_date);
            $expiryDate = $collectionDate->copy()->addDays($component->shelf_life_days);

            $unitNumber = $this->generateUnitNumber();

            return BloodUnit::create([
                'unit_number'      => $unitNumber,
                'blood_group_id'   => $donor->blood_group_id,
                'component_id'     => $component->id,
                'donor_id'         => $donor->id,
                'donation_id'      => $donation->id,
                'collection_date'  => $collectionDate->format('Y-m-d'),
                'expiry_date'      => $expiryDate->format('Y-m-d'),
                'volume_ml'        => $donation->quantity ?? 450,
                'storage_location' => $storageLocation ?? 'Main Refrigerator Bay 1',
                'status'           => 'available',
            ]);
        });
    }
}
