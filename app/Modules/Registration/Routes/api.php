<?php

use App\Modules\Registration\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Registration Module API Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Registration module API endpoints.
| All routes are prefixed with /api/registration
|
*/

Route::prefix('api')->group(function () {
    Route::prefix('registration')->group(function () {
        // Public registration endpoints
        Route::post('/register', [RegistrationController::class, 'register']);
        Route::post('/verify-email', [RegistrationController::class, 'verifyEmail']);
        Route::post('/resend-verification', [RegistrationController::class, 'resendVerification']);
        Route::post('/forgot-password', [RegistrationController::class, 'forgotPassword']);
        Route::post('/reset-password', [RegistrationController::class, 'resetPassword']);
        
        // Public utility endpoints
        Route::get('/check-availability', [RegistrationController::class, 'checkAvailability']);
        Route::get('/genders', [RegistrationController::class, 'getGenders']);
        Route::get('/sources', [RegistrationController::class, 'getSources']);
        Route::get('/statistics', [RegistrationController::class, 'getStatistics']);
        
        // Protected profile management endpoints
        Route::get('/profile', [RegistrationController::class, 'getProfile']);
        Route::put('/profile', [RegistrationController::class, 'updateProfile']);
        Route::post('/change-password', [RegistrationController::class, 'changePassword']);
        
        // Admin endpoints (you can add admin middleware here)
        Route::get('/users', [RegistrationController::class, 'searchUsers']);
    });
}); 