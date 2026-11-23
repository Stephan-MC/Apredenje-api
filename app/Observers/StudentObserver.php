<?php

namespace App\Observers;

use App\Models\Student;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class StudentObserver
{
    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "deleting" event.
     */
    public function deleting(Student $student): void
    {
        if (Auth::check()) {
            Gate::authorize('delete', $student);

            return;
        }

        if ($student->payments()->exists()) {
            Response::deny('Cannot delete a student who has payments')
                ->withStatus(409)
                ->authorize();
        }
    }

    /**
     * Handle the Student "deleted" event.
     */
    public function deleted(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "restored" event.
     */
    public function restored(Student $student): void
    {
        //
    }

    /**
     * Handle the Student "force deleted" event.
     */
    public function forceDeleted(Student $student): void
    {
        //
    }
}
