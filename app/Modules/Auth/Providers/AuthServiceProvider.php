<?php

namespace App\Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Auth\Services\AuthService;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the AuthService
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
} 