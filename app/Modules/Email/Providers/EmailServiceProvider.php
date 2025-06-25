<?php

namespace App\Modules\Email\Providers;

use Illuminate\Support\ServiceProvider;

class EmailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Email services here if needed
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