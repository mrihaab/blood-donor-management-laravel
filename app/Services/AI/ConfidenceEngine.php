<?php

namespace App\Services\AI;

use App\Enums\AISafetyState;

class ConfidenceEngine
{
    /**
     * Determine Safety State based on sample size K, data integrity, drift flags, and system status.
     */
    public function evaluateSafetyState(
        int $historicalSampleSize,
        bool $isDataTrustworthy,
        bool $isModelDriftDetected,
        bool $isModelAvailable
    ): AISafetyState {
        if (!$isModelAvailable) {
            return AISafetyState::MODEL_UNAVAILABLE;
        }

        if (!$isDataTrustworthy) {
            return AISafetyState::DATA_UNTRUSTWORTHY;
        }

        if ($isModelDriftDetected) {
            return AISafetyState::MODEL_DRIFT;
        }

        if ($historicalSampleSize === 0) {
            return AISafetyState::COLD_START;
        }

        if ($historicalSampleSize < 30) {
            return AISafetyState::DEGRADED;
        }

        return AISafetyState::CONFIDENT;
    }

    /**
     * Compute confidence score. Returns NULL for non-confident states.
     */
    public function computeConfidenceScore(AISafetyState $state, float $rawScore): ?float
    {
        return match ($state) {
            AISafetyState::CONFIDENT => max(0.70, min(1.0, $rawScore)),
            AISafetyState::DEGRADED => max(0.40, min(0.69, $rawScore)),
            AISafetyState::COLD_START,
            AISafetyState::MODEL_DRIFT,
            AISafetyState::DATA_UNTRUSTWORTHY,
            AISafetyState::MODEL_UNAVAILABLE => null,
        };
    }
}
