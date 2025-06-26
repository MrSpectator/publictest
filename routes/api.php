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
        'modules' => [
            'email' => '/api/email/*',
            'logger' => '/api/logger/*',
            'registration' => '/api/registration/*'
        ],
        'documentation' => '/api/documentation'
    ]);
});

// Module routes are loaded by their respective service providers
// Email Module: /api/email/*
// Logger Module: /api/logger/*
// Registration Module: /api/registration/*

Route::get('/user', function (Request $request) {
    return $request->user();
}); 