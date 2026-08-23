<?php

namespace App\Policies;

use App\Models\Donor;
use App\Models\User;

class DonorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Donor $donor): bool
    {
        return $user->isAdmin() || $user->id === $donor->user_id;
    }

    public function update(User $user, Donor $donor): bool
    {
        return $user->isAdmin() || $user->id === $donor->user_id;
    }

    public function delete(User $user, Donor $donor): bool
    {
        return $user->isAdmin();
    }
}
