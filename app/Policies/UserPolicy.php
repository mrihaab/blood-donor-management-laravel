<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin() || $user->id === $target->id;
    }

    public function delete(User $user, User $target): bool
    {
        $masterEmail = env('ADMIN_EMAIL', 'admin@example.com');
        if ($target->email === $masterEmail || $target->id === $user->id) {
            return false;
        }

        return $user->isAdmin();
    }
}
