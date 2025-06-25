<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Email\Controllers\EmailController;

Route::prefix('api')->group(function () {
    Route::prefix('email')->group(function () {
        Route::post('send', [EmailController::class, 'send']);
        Route::get('logs', [EmailController::class, 'logs']);
    });
}); 