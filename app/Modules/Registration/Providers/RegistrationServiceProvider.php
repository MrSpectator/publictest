<?php

namespace App\Modules\Registration\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Registration\Services\RegistrationService;
use App\Modules\Email\Services\EmailService;
use Illuminate\Http\Request;

class RegistrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the RegistrationService with its dependencies resolved from the container
        $this->app->singleton(RegistrationService::class, function ($app) {
            return new RegistrationService(
                $app->make(Request::class),
                $app->make(EmailService::class)
            );
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