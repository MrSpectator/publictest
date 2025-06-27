<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register module service providers
        $this->app->register(\App\Modules\Email\Providers\EmailServiceProvider::class);
        $this->app->register(\App\Modules\Logger\Providers\LoggerServiceProvider::class);
        $this->app->register(\App\Modules\Registration\Providers\RegistrationServiceProvider::class);
        $this->app->register(\App\Modules\Auth\Providers\AuthServiceProvider::class);
    }
}
