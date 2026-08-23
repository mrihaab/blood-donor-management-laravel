<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin() || ($user->donor && $user->donor->id === $appointment->donor_id);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin() || ($user->donor && $user->donor->id === $appointment->donor_id);
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin() || ($user->donor && $user->donor->id === $appointment->donor_id);
    }
}
