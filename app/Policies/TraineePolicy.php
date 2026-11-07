<?php

namespace App\Policies;

use App\Models\Trainee;
use App\Models\User;

class TraineePolicy
{
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
    public function view(User $user, Trainee $trainee): bool
    {
        return $user->isAn('admin') || $user->isA('trainer') || $user->id === $trainee->id;
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
    public function update(User $user, Trainee $trainee): bool
    {
        return $user->isAn('admin') || $user->id === $trainee->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Trainee $trainee): bool
    {
        return $user->isAn('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Trainee $trainee): bool
    {
        return $user->isAn('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Trainee $trainee): bool
    {
        return $user->isAn('admin');
    }
}
