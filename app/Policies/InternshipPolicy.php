<?php

namespace App\Policies;

use App\Models\Internship;
use App\Models\User;

class InternshipPolicy
{
    /**
     * Perform pre-authorization checks.
     */
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
        return $user->isAn('admin') || $user->isA('trainer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Internship $internship): bool
    {
        return $user->isAn('admin') || $user->isA('trainer') || $user->id === $internship->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAn('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Internship $internship): bool
    {
        return $user->isAn('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Internship $internship): bool
    {
        return $user->isAn('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Internship $internship): bool
    {
        return $user->isAn('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Internship $internship): bool
    {
        return $user->isAn('admin');
    }
}
