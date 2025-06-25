<?php

namespace App\Modules\Registration\Providers;

use Illuminate\Support\ServiceProvider;

class RegistrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the RegistrationService as a singleton
        $this->app->singleton(\App\Modules\Registration\Services\RegistrationService::class, function ($app) {
            return new \App\Modules\Registration\Services\RegistrationService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        // Load factories
        $this->loadFactoriesFrom(__DIR__ . '/../Database/Factories');
    }
} 