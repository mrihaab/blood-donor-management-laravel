<?php

namespace App\Policies;

use App\Models\Transfusion;
use App\Models\User;

class TransfusionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isHospital();
    }

    public function view(User $user, Transfusion $transfusion): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isHospital() && $user->hospital_id !== null) {
            return (int)$user->hospital_id === (int)$transfusion->hospital_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || ($user->isHospital() && $user->hospital_id !== null);
    }

    public function update(User $user, Transfusion $transfusion): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isHospital() && $user->hospital_id !== null) {
            return (int)$user->hospital_id === (int)$transfusion->hospital_id;
        }

        return false;
    }

    public function recordReaction(User $user, Transfusion $transfusion): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isHospital() && $user->hospital_id !== null) {
            return (int)$user->hospital_id === (int)$transfusion->hospital_id;
        }

        return false;
    }
}
