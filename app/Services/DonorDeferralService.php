<?php

namespace App\Services;

use App\Models\Donor;
use App\Models\DonorDeferral;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DonorDeferralService
{
    public function deferDonor(
        Donor $donor,
        string $deferralType,
        string $reason,
        ?Carbon $endDate = null,
        ?User $actor = null,
        ?string $notes = null
    ): DonorDeferral {
        return DB::transaction(function () use ($donor, $deferralType, $reason, $endDate, $actor, $notes) {
            // Deactivate any existing active temporary deferrals if introducing a new one
            DonorDeferral::where('donor_id', $donor->id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            $deferral = DonorDeferral::create([
                'donor_id' => $donor->id,
                'deferral_type' => $deferralType,
                'reason' => $reason,
                'start_date' => now()->startOfDay(),
                'end_date' => $deferralType === 'temporary' ? $endDate : null,
                'created_by' => $actor ? $actor->id : auth()->id() ?? $donor->user_id,
                'notes' => $notes,
                'status' => 'active',
            ]);

            $donor->update(['is_available' => false]);

            activity()
                ->performedOn($donor)
                ->causedBy($actor ?? auth()->user())
                ->log("Donor deferred ({$deferralType}): {$reason}");

            return $deferral;
        });
    }

    public function revokeDeferral(DonorDeferral $deferral, User $actor, string $reason): bool
    {
        return DB::transaction(function () use ($deferral, $actor, $reason) {
            $deferral->update([
                'status' => 'revoked',
                'notes' => ($deferral->notes ? $deferral->notes . "\n" : '') . "Revoked by {$actor->name}: {$reason}",
            ]);

            $hasOtherActiveDeferral = DonorDeferral::where('donor_id', $deferral->donor_id)
                ->where('status', 'active')
                ->exists();

            if (!$hasOtherActiveDeferral) {
                $deferral->donor->update(['is_available' => true]);
            }

            activity()
                ->performedOn($deferral->donor)
                ->causedBy($actor)
                ->log("Donor deferral revoked: {$reason}");

            return true;
        });
    }

    public function getActiveDeferral(Donor $donor): ?DonorDeferral
    {
        $deferral = DonorDeferral::where('donor_id', $donor->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$deferral) {
            return null;
        }

        if ($deferral->deferral_type === 'temporary' && $deferral->end_date && $deferral->end_date->isPast()) {
            $deferral->update(['status' => 'expired']);
            return null;
        }

        return $deferral;
    }

    /**
     * Process expired temporary donor deferrals and reactivate eligible donors safely.
     */
    public function processExpiredDeferrals(): array
    {
        return DB::transaction(function () {
            $expiredDeferrals = DonorDeferral::where('status', 'active')
                ->where('deferral_type', 'temporary')
                ->whereNotNull('end_date')
                ->where('end_date', '<=', now()->format('Y-m-d'))
                ->get();

            $expiredCount = 0;
            $reactivatedDonors = 0;
            $blockedDonors = 0;

            foreach ($expiredDeferrals as $deferral) {
                $deferral->update(['status' => 'expired']);
                $expiredCount++;

                $donor = $deferral->donor;
                if ($donor) {
                    $hasOtherActiveDeferral = DonorDeferral::where('donor_id', $donor->id)
                        ->where('status', 'active')
                        ->exists();

                    if (!$hasOtherActiveDeferral) {
                        $donor->update([
                            'is_available' => true,
                            'status' => 'active',
                        ]);
                        $reactivatedDonors++;
                    } else {
                        $blockedDonors++;
                    }
                }
            }

            return [
                'expired_deferrals' => $expiredCount,
                'reactivated_donors' => $reactivatedDonors,
                'still_blocked_donors' => $blockedDonors,
            ];
        });
    }
}
