<?php

namespace App\Observers;

use App\Models\Intern;

class InternObserver
{
    /**
     * Handle the Intern "created" event.
     */
    public function created(Intern $intern): void
    {
        //
    }

    /**
     * Handle the Intern "updated" event.
     */
    public function updated(Intern $intern): void
    {
        //
    }

    /**
     * Handle the Intern "deleted" event.
     */
    public function deleted(Intern $intern): void
    {
        //
    }

    /**
     * Handle the Intern "restored" event.
     */
    public function restored(Intern $intern): void
    {
        //
    }

    /**
     * Handle the Intern "force deleted" event.
     */
    public function forceDeleted(Intern $intern): void
    {
        //
    }
}
