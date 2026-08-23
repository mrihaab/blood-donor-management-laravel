<?php

namespace App\Services;

use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\User;
use App\Notifications\EmergencyBloodRequestNotification;
use Illuminate\Support\Facades\DB;

class BloodRequestService
{
    public function createRequest(array $data, ?User $user = null): BloodRequest
    {
        return DB::transaction(function () use ($data, $user) {
            $request = BloodRequest::create([
                'user_id' => $user ? $user->id : null,
                'patient_name' => $data['patient_name'],
                'blood_group' => $data['blood_group'],
                'units_needed' => $data['units_needed'] ?? 1,
                'hospital' => $data['hospital'],
                'city' => $data['city'] ?? 'Metropolis',
                'reason' => $data['reason'] ?? null,
                'status' => 'pending',
            ]);

            $urgency = $data['urgency'] ?? 'emergency';
            if ($urgency === 'emergency') {
                $this->notifyMatchingDonors($request);
            }

            activity()
                ->causedBy($user)
                ->performedOn($request)
                ->log("Blood request #{$request->id} created for {$request->patient_name} ({$request->blood_group})");

            return $request;
        });
    }

    public function approveRequest(BloodRequest $request, User $admin, ?string $notes = null): bool
    {
        return DB::transaction(function () use ($request, $admin, $notes) {
            $request->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            activity()
                ->causedBy($admin)
                ->performedOn($request)
                ->log("Admin approved blood request #{$request->id}");

            return true;
        });
    }

    public function rejectRequest(BloodRequest $request, User $admin, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($request, $admin, $reason) {
            $request->update([
                'status' => 'rejected',
                'rejected_by' => $admin->id,
                'rejected_at' => now(),
            ]);

            activity()
                ->causedBy($admin)
                ->performedOn($request)
                ->log("Admin rejected blood request #{$request->id}");

            return true;
        });
    }

    public function dispenseRequest(BloodRequest $request, User $admin): bool
    {
        return DB::transaction(function () use ($request, $admin) {
            $request->update([
                'status' => 'dispensed',
            ]);

            activity()
                ->causedBy($admin)
                ->performedOn($request)
                ->log("Admin dispensed blood for request #{$request->id}");

            return true;
        });
    }

    public function notifyMatchingDonors(BloodRequest $request): int
    {
        $matchingUsers = User::where('role', 'donor')
            ->where('status', 'active')
            ->whereHas('donor.bloodGroup', function ($q) use ($request) {
                $q->where('name', $request->blood_group);
            })->get();

        foreach ($matchingUsers as $matchingUser) {
            $matchingUser->notify(new EmergencyBloodRequestNotification($request));
        }

        return $matchingUsers->count();
    }
}
