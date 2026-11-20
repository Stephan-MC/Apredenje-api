<?php

namespace App\Providers;

use App\Routing\ApredenjeResourceRegistrar;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ResourceRegistrar::class, ApredenjeResourceRegistrar::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::morphUsingUlids();

        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            return config('app.frontend_url', config('app.url'))."/password-reset/{$token}?email=".urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
