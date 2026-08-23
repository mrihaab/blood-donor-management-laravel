<?php

namespace App\Policies;

use App\Models\BloodRequest;
use App\Models\User;

class BloodRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BloodRequest $request): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $request->user_id) {
            return true;
        }

        if ($user->isHospital() && $user->hospital_id && (int)$user->hospital_id === (int)$request->hospital_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, BloodRequest $request): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, BloodRequest $request): bool
    {
        return $user->isAdmin();
    }

    public function dispense(User $user, BloodRequest $request): bool
    {
        return $user->isAdmin();
    }
}
