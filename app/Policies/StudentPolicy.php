<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isA('super-admin') && ! in_array($ability, ['delete', 'forceDelete'], true)) {
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
    public function view(User $user, Student $student): bool
    {
        return $user->isAn('admin')
            || $user->isA('trainer')
            || $student->trainee_id === $user->id;
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
    public function update(User $user, Student $student): bool
    {
        return $user->isAn('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool|Response
    {
        if (! ($user->isAn('admin') || $user->isA('super-admin'))) {
            return false;
        }

        if ($this->hasPayments($student)) {
            return Response::deny('Cannot delete a student who has payments')
                ->withStatus(409);
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Student $student): bool
    {
        return $user->isAn('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Student $student): bool|Response
    {
        if (! ($user->isAn('admin') || $user->isA('super-admin'))) {
            return false;
        }

        if ($this->hasPayments($student)) {
            return Response::deny('Cannot delete a student who has payments')
                ->withStatus(409);
        }

        return true;
    }

    private function hasPayments(Student $student): bool
    {
        if (array_key_exists('payments_exists', $student->getAttributes())) {
            return (bool) $student->getAttribute('payments_exists');
        }

        return $student->payments()->exists();
    }
}
