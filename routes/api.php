<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API info endpoint
Route::get('/info', function () {
    return response()->json([
        'message' => 'isalesbookv2 API',
        'version' => '1.0.0',
        'status' => 'operational',
        'modules' => [
            'email' => [
                'base_path' => '/api/email/*',
                'status' => 'operational',
                'endpoints' => [
                    'POST /api/email/send' => 'Send email with attachments',
                    'GET /api/email/logs' => 'Get email logs with filtering'
                ]
            ],
            'logger' => [
                'base_path' => '/api/logger/*',
                'status' => 'operational',
                'endpoints' => [
                    'POST /api/logger/log' => 'Create log entry',
                    'POST /api/logger/{level}' => 'Log by level (emergency, alert, critical, error, warning, notice, info, debug)',
                    'GET /api/logger/logs' => 'Get system logs'
                ]
            ],
            'registration' => [
                'base_path' => '/api/registration/*',
                'status' => 'operational',
                'endpoints' => [
                    'POST /api/registration/register' => 'Register new user',
                    'POST /api/registration/verify-email' => 'Verify email',
                    'POST /api/registration/forgot-password' => 'Send password reset',
                    'POST /api/registration/reset-password' => 'Reset password',
                    'GET /api/registration/profile' => 'Get user profile',
                    'PUT /api/registration/profile' => 'Update user profile'
                ]
            ],
            'auth' => [
                'base_path' => '/api/auth/*',
                'status' => 'operational',
                'endpoints' => [
                    'POST /api/auth/login' => 'User login',
                    'POST /api/auth/logout' => 'User logout',
                    'GET /api/auth/me' => 'Get current user'
                ]
            ]
        ],
        'documentation' => '/api/documentation',
        'swagger_ui' => '/swagger'
    ]);
});

// Module routes are loaded by their respective service providers
// Email Module: /api/email/*
// Logger Module: /api/logger/*
// Registration Module: /api/registration/*
// Auth Module: /api/auth/*

Route::get('/user', function (Request $request) {
    return $request->user();
}); 