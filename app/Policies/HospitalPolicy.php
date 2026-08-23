<?php

namespace App\Policies;

use App\Models\Hospital;
use App\Models\User;

class HospitalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isHospital();
    }

    public function view(User $user, Hospital $hospital): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isHospital() && (int)$user->hospital_id === (int)$hospital->id;
    }

    public function update(User $user, Hospital $hospital): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Hospital $hospital): bool
    {
        return $user->isAdmin();
    }
}
