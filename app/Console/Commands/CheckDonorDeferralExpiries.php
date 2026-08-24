<?php

namespace App\Console\Commands;

use App\Services\DonorDeferralService;
use Illuminate\Console\Command;

class CheckDonorDeferralExpiries extends Command
{
    protected $signature = 'donors:check-deferral-expiries';
    protected $description = 'Check and expire temporary donor deferrals past their end date';

    public function handle(DonorDeferralService $deferralService): int
    {
        $result = $deferralService->processExpiredDeferrals();

        $this->info("Expired {$result['expired_deferrals']} temporary donor deferral(s).");
        $this->info("Reactivated {$result['reactivated_donors']} donor(s).");

        if ($result['still_blocked_donors'] > 0) {
            $this->info("{$result['still_blocked_donors']} donor(s) remain blocked by other active deferrals.");
        }

        return Command::SUCCESS;
    }
}
