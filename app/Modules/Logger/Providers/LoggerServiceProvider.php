<?php

namespace App\Modules\Logger\Providers;

use Illuminate\Support\ServiceProvider;

class LoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the LogService as a singleton
        $this->app->singleton(\App\Modules\Logger\Services\LogService::class, function ($app) {
            return new \App\Modules\Logger\Services\LogService();
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