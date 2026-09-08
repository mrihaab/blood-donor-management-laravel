<?php

namespace App\Services\AI;

class GroundTruthMatrixEngine
{
    /**
     * Exhaustive Ground Truth Derivation Function F.
     */
    public function deriveGroundTruth(
        string $inventoryStatus,
        string $fulfillmentStatus,
        string $timeStatus,
        bool $isCompatible = true
    ): string {
        if ($inventoryStatus === 'UNKNOWN' || $fulfillmentStatus === 'UNKNOWN' || $timeStatus === 'UNKNOWN') {
            return 'UNDETERMINED_UNKNOWN';
        }

        if (!$isCompatible) {
            return 'EMERGENCY_UNFULFILLED';
        }

        if ($inventoryStatus === 'ABOVE_SAFETY_STOCK' && $fulfillmentStatus === 'FULL' && $timeStatus === 'WITHIN_SLA') {
            return 'NO_SHORTAGE';
        }

        if ($inventoryStatus === 'BELOW_SAFETY_STOCK' && $fulfillmentStatus === 'FULL' && $timeStatus === 'WITHIN_SLA') {
            return 'SOFT_SHORTAGE';
        }

        if ($inventoryStatus === 'BELOW_SAFETY_STOCK' && $fulfillmentStatus === 'PARTIAL' && $timeStatus === 'WITHIN_SLA') {
            return 'PARTIAL_FULFILLMENT';
        }

        if ($inventoryStatus === 'ZERO_COMPATIBLE_STOCK' && $fulfillmentStatus === 'NONE' && $timeStatus === 'WITHIN_SLA') {
            return 'HARD_SHORTAGE';
        }

        if ($timeStatus === 'SLA_BREACHED' || $fulfillmentStatus === 'UNFULFILLED') {
            return 'EMERGENCY_UNFULFILLED';
        }

        return 'PARTIAL_FULFILLMENT';
    }
}
