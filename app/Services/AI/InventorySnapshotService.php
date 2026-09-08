<?php

namespace App\Services\AI;

use App\Enums\BloodUnitLifecycleState;
use App\Enums\BloodUnitAvailabilityState;
use App\Enums\BloodUnitTestingState;
use App\Enums\BloodUnitLocationState;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InventorySnapshotService
{
    /**
     * Centralized Canonical Usability Predicate.
     * Both Prediction Engine AND Outcome Evaluator strictly share this exact predicate.
     */
    public static function isUsable(object|array $unit): bool
    {
        $lifecycle = is_array($unit) ? ($unit['lifecycle_state'] ?? null) : ($unit->lifecycle_state ?? null);
        $availability = is_array($unit) ? ($unit['availability_state'] ?? null) : ($unit->availability_state ?? null);
        $testing = is_array($unit) ? ($unit['testing_state'] ?? null) : ($unit->testing_state ?? null);
        $location = is_array($unit) ? ($unit['location_state'] ?? null) : ($unit->location_state ?? null);
        $expiry = is_array($unit) ? ($unit['expiry_date'] ?? null) : ($unit->expiry_date ?? null);
        $recalled = is_array($unit) ? ($unit['is_recalled'] ?? false) : ($unit->is_recalled ?? false);

        // Normalize Enum strings
        $lifecycleStr = $lifecycle instanceof BloodUnitLifecycleState ? $lifecycle->value : (string) $lifecycle;
        $availabilityStr = $availability instanceof BloodUnitAvailabilityState ? $availability->value : (string) $availability;
        $testingStr = $testing instanceof BloodUnitTestingState ? $testing->value : (string) $testing;
        $locationStr = $location instanceof BloodUnitLocationState ? $location->value : (string) $location;

        if ($recalled) {
            return false;
        }

        if ($lifecycleStr !== BloodUnitLifecycleState::AVAILABLE->value) {
            return false;
        }

        if (!in_array($availabilityStr, [
            BloodUnitAvailabilityState::IMMEDIATELY_USABLE->value,
            BloodUnitAvailabilityState::TENTATIVELY_HELD->value
        ], true)) {
            return false;
        }

        if ($testingStr !== BloodUnitTestingState::PASSED->value) {
            return false;
        }

        if ($locationStr !== BloodUnitLocationState::AT_ORIGIN->value) {
            return false;
        }

        if ($expiry) {
            $expiryCarbon = Carbon::parse($expiry);
            if ($expiryCarbon->isPast()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Read-Only Soft-Lock Evaluation.
     * ZERO domain writes permitted during snapshot computation.
     */
    public function computeSnapshot(string $scopeType, ?int $scopeId, string $bloodGroup): array
    {
        // Query usable stock using canonical predicate
        $units = DB::table('blood_units')
            ->where('blood_group', $bloodGroup)
            ->where('lifecycle_state', BloodUnitLifecycleState::AVAILABLE->value)
            ->where('testing_state', BloodUnitTestingState::PASSED->value)
            ->where('location_state', BloodUnitLocationState::AT_ORIGIN->value)
            ->where('is_recalled', false)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                      ->orWhere('expiry_date', '>', DB::raw('UTC_TIMESTAMP()'));
            })
            ->get();

        $usableCount = 0;
        $tentativeCount = 0;

        foreach ($units as $unit) {
            if (self::isUsable($unit)) {
                if ($unit->availability_state === BloodUnitAvailabilityState::IMMEDIATELY_USABLE->value) {
                    $usableCount++;
                } elseif ($unit->availability_state === BloodUnitAvailabilityState::TENTATIVELY_HELD->value) {
                    $tentativeCount++;
                }
            }
        }

        return [
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'blood_group' => $bloodGroup,
            'usable_units_count' => $usableCount,
            'tentatively_held_count' => $tentativeCount,
            'total_available' => $usableCount + $tentativeCount,
        ];
    }
}
