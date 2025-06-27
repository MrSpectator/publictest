<?php

use App\Modules\Auth\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Module API Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Auth module API endpoints.
| All routes are prefixed with /api/auth
|
*/

Route::prefix('api')->group(function () {
    Route::prefix('auth')->group(function () {
        // Public authentication endpoints
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/validate-org-code', [AuthController::class, 'validateOrgCode']);
        
        // Protected authentication endpoints
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });
}); 