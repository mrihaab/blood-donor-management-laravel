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
        return $user->isAdmin() || $user->id === $request->user_id;
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
