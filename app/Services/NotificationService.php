<?php

namespace App\Services;

use App\Models\BloodRequest;
use App\Models\User;
use App\Notifications\EmergencyBloodRequestNotification;

class NotificationService
{
    public function sendEmergencyBroadcast(BloodRequest $bloodRequest): int
    {
        $matchingUsers = User::where('role', 'donor')
            ->where('status', 'active')
            ->whereHas('donor.bloodGroup', function ($q) use ($bloodRequest) {
                $q->where('name', $bloodRequest->blood_group);
            })->get();

        foreach ($matchingUsers as $user) {
            $user->notify(new EmergencyBloodRequestNotification($bloodRequest));
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($bloodRequest)
            ->log("Dispatched emergency broadcast to {$matchingUsers->count()} donors for request #{$bloodRequest->id}");

        return $matchingUsers->count();
    }
}
