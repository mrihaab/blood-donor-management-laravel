<?php

namespace App\Services;

use App\Models\Donor;
use Carbon\Carbon;

class DonorEligibilityService
{
    public const DONATION_INTERVAL_DAYS = 56;

    protected DonorDeferralService $deferralService;

    public function __construct(DonorDeferralService $deferralService)
    {
        $this->deferralService = $deferralService;
    }

    public function checkEligibility(Donor $donor): array
    {
        $reasons = [];
        $isEligible = true;

        // 1. Account Status Check
        if ($donor->user && $donor->user->status !== 'active') {
            $isEligible = false;
            $reasons[] = 'Donor account status is inactive.';
        }

        // 2. Active Medical Deferrals Check
        $activeDeferral = $this->deferralService->getActiveDeferral($donor);
        if ($activeDeferral) {
            $isEligible = false;
            if ($activeDeferral->deferral_type === 'permanent') {
                $reasons[] = "Permanently deferred: {$activeDeferral->reason}";
            } else {
                $endDateStr = $activeDeferral->end_date ? $activeDeferral->end_date->format('M d, Y') : 'specified date';
                $reasons[] = "Temporarily deferred until {$endDateStr}: {$activeDeferral->reason}";
            }
        }

        // 3. 56-Day Recovery Interval Check
        $lastDonationDate = $donor->getLastDonationDate();
        $daysSince = $lastDonationDate ? (int) $lastDonationDate->diffInDays(now()) : null;
        $intervalEligible = true;
        $nextEligibleDate = now()->startOfDay();

        if ($lastDonationDate) {
            $nextEligibleDate = $lastDonationDate->copy()->addDays(self::DONATION_INTERVAL_DAYS);
            if ($daysSince < self::DONATION_INTERVAL_DAYS) {
                $intervalEligible = false;
                $isEligible = false;
                $daysRemaining = self::DONATION_INTERVAL_DAYS - $daysSince;
                $reasons[] = "Must wait {$daysRemaining} more days before next donation (56-day rule).";
            }
        }

        $daysUntil = $isEligible ? 0 : ($lastDonationDate && !$intervalEligible ? max(0, self::DONATION_INTERVAL_DAYS - $daysSince) : 0);

        return [
            'eligible' => $isEligible,
            'reasons' => $reasons,
            'is_deferred' => $activeDeferral !== null,
            'deferral_type' => $activeDeferral ? $activeDeferral->deferral_type : null,
            'active_deferral' => $activeDeferral,
            'days_since_last' => $daysSince,
            'days_until_eligible' => $daysUntil,
            'last_donation_date' => $lastDonationDate,
            'next_eligible_date' => $nextEligibleDate,
        ];
    }
}
