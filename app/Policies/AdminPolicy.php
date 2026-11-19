<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;

class AdminPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isA('super-admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Admin $admin): bool
    {
        return $user->is($admin);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Admin $admin): bool
    {
        return $user->is($admin);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Admin $admin): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Admin $admin): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Admin $admin): bool
    {
        return false;
    }
}
