<?php

namespace App\Observers;

use App\Models\Session;

class SessionObserver
{
    /**
     * Handle the Session "created" event.
     */
    public function created(Session $session): void
    {
        //
    }

    /**
     * Handle the Session "updated" event.
     */
    public function updated(Session $session): void
    {
        //
    }

    /**
     * Handle the Session "deleted" event.
     */
    public function deleted(Session $session): void
    {
        //
    }

    /**
     * Handle the Session "restored" event.
     */
    public function restored(Session $session): void
    {
        //
    }

    /**
     * Handle the Session "force deleted" event.
     */
    public function forceDeleted(Session $session): void
    {
        //
    }
}
