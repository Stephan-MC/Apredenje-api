<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\WelcomeUserNotification;
use Silber\Bouncer\BouncerFacade;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (! empty($user->email)) {
            $user->notify(new WelcomeUserNotification($user));
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->cleanupAccess($user);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $this->cleanupAccess($user);
    }

    /**
     * Revoke API tokens and remove assigned roles and abilities for the user.
     */
    private function cleanupAccess(User $user): void
    {
        $user->tokens()->delete();
        $user->roles()->detach();
        $user->abilities()->detach();
        BouncerFacade::refreshFor($user);
    }
}
