<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Donor;
use App\Models\DonorScreening;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DonorScreeningService
{
    protected DonorDeferralService $deferralService;

    public function __construct(DonorDeferralService $deferralService)
    {
        $this->deferralService = $deferralService;
    }

    public function processScreening(
        Appointment $appointment,
        array $vitals,
        User $screener
    ): DonorScreening {
        return DB::transaction(function () use ($appointment, $vitals, $screener) {
            $donor = $appointment->donor;

            // Evaluate medical screening thresholds
            $status = $vitals['status'] ?? $this->evaluateVitals($vitals);
            $notes = $vitals['notes'] ?? null;

            $screening = DonorScreening::create([
                'appointment_id' => $appointment->id,
                'donor_id' => $donor->id,
                'screened_by' => $screener->id,
                'blood_pressure' => $vitals['blood_pressure'] ?? null,
                'pulse' => $vitals['pulse'] ?? null,
                'temperature' => $vitals['temperature'] ?? null,
                'weight' => $vitals['weight'] ?? null,
                'hemoglobin' => $vitals['hemoglobin'] ?? null,
                'status' => $status,
                'notes' => $notes,
            ]);

            // Handle deferrals if screening failed
            if ($status === 'temporarily_deferred') {
                $endDate = now()->addDays($vitals['deferral_days'] ?? 14);
                $this->deferralService->deferDonor(
                    $donor,
                    'temporary',
                    $notes ?? 'Failed pre-donation screening (temporary)',
                    $endDate,
                    $screener
                );
                $appointment->update(['status' => 'deferred']);
            } elseif ($status === 'permanently_deferred' || $status === 'rejected') {
                $this->deferralService->deferDonor(
                    $donor,
                    'permanent',
                    $notes ?? 'Failed pre-donation screening (permanent)',
                    null,
                    $screener
                );
                $appointment->update(['status' => 'deferred']);
            } else {
                $appointment->update(['status' => 'screening']);
            }

            activity()
                ->performedOn($appointment)
                ->causedBy($screener)
                ->log("Pre-donation screening recorded with outcome: {$status}");

            return $screening;
        });
    }

    protected function evaluateVitals(array $vitals): string
    {
        if (isset($vitals['hemoglobin']) && (float)$vitals['hemoglobin'] < 12.5) {
            return 'temporarily_deferred';
        }

        if (isset($vitals['temperature']) && (float)$vitals['temperature'] > 37.5) {
            return 'temporarily_deferred';
        }

        if (isset($vitals['weight']) && (float)$vitals['weight'] < 50.0) {
            return 'temporarily_deferred';
        }

        return 'eligible';
    }
}
