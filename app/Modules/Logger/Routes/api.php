<?php

use App\Modules\Logger\Controllers\LogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Logger Module API Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Logger module API endpoints.
| All routes are prefixed with /api/logger
|
*/

Route::prefix('logger')->group(function () {
    // General logging endpoint
    Route::post('/log', [LogController::class, 'createLog']);
    
    // Specific log level endpoints
    Route::post('/emergency', [LogController::class, 'emergency']);
    Route::post('/alert', [LogController::class, 'alert']);
    Route::post('/critical', [LogController::class, 'critical']);
    Route::post('/error', [LogController::class, 'error']);
    Route::post('/warning', [LogController::class, 'warning']);
    Route::post('/notice', [LogController::class, 'notice']);
    Route::post('/info', [LogController::class, 'info']);
    Route::post('/debug', [LogController::class, 'debug']);
    
    // Log retrieval and management
    Route::get('/logs', [LogController::class, 'getLogs']);
    Route::get('/logs/{id}', [LogController::class, 'getLog']);
    Route::delete('/logs/{id}', [LogController::class, 'deleteLog']);
    
    // Statistics and metadata
    Route::get('/statistics', [LogController::class, 'getStatistics']);
    Route::get('/levels', [LogController::class, 'getLevels']);
    Route::get('/categories', [LogController::class, 'getCategories']);
    
    // Maintenance
    Route::post('/clean', [LogController::class, 'cleanOldLogs']);
}); 