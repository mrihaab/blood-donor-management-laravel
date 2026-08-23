<?php

namespace App\Services;

use App\Models\Donor;
use Carbon\Carbon;

class DonorEligibilityService
{
    public const DONATION_INTERVAL_DAYS = 56;

    public function checkEligibility(Donor $donor): array
    {
        $lastDonationDate = $donor->getLastDonationDate();
        
        if (!$lastDonationDate) {
            return [
                'eligible' => true,
                'days_since_last' => null,
                'days_until_eligible' => 0,
                'last_donation_date' => null,
                'next_eligible_date' => now()->startOfDay(),
            ];
        }

        $daysSince = (int) $lastDonationDate->diffInDays(now());
        $isEligible = $daysSince >= self::DONATION_INTERVAL_DAYS;
        $nextEligibleDate = $lastDonationDate->copy()->addDays(self::DONATION_INTERVAL_DAYS);
        $daysUntil = $isEligible ? 0 : max(0, self::DONATION_INTERVAL_DAYS - $daysSince);

        return [
            'eligible' => $isEligible,
            'days_since_last' => $daysSince,
            'days_until_eligible' => $daysUntil,
            'last_donation_date' => $lastDonationDate,
            'next_eligible_date' => $nextEligibleDate,
        ];
    }
}
