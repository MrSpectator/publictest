<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect landing page to Swagger UI
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
})->name('auth.login');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('auth.forgot-password');

// Redirect to Swagger UI for API documentation
Route::get('/api-docs', function () {
    return redirect('/swagger');
});
