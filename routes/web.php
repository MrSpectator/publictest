<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Registration\Controllers\RegistrationController;

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

// Redirect landing page to login
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
})->name('auth.login');

Route::post('/login', function (Request $request) {
    // Validate the request
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'org_code' => 'required|string|max:5'
    ]);

    try {
        // Use Laravel's internal request handling
        $authController = app(\App\Modules\Auth\Controllers\AuthController::class);
        $response = $authController->login($request);
        
        $data = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() === 200 && $data['success']) {
            // Extract data from the API response structure
            $userData = $data['data']['user'] ?? null;
            $token = $data['data']['token'] ?? null;
            
            if ($userData && $token) {
                // Store user data in session
                session()->put('user', $userData);
                session()->put('token', $token);
                
                return redirect('/dashboard')->with('success', 'Login successful!');
            } else {
                return back()
                    ->withInput($request->only('email', 'org_code'))
                    ->withErrors(['login' => 'Invalid response from server.']);
            }
        } else {
            $errorMessage = $data['message'] ?? 'Invalid email, password, or organization code.';
            
            // Handle validation errors
            if (isset($data['errors'])) {
                $errors = [];
                foreach ($data['errors'] as $field => $messages) {
                    foreach ($messages as $message) {
                        $errors[] = $message;
                    }
                }
                return back()
                    ->withInput($request->only('email', 'org_code'))
                    ->withErrors(['login' => implode(', ', $errors)]);
            }
            
            return back()
                ->withInput($request->only('email', 'org_code'))
                ->withErrors(['login' => $errorMessage]);
        }
    } catch (\Exception $e) {
        return back()
            ->withInput($request->only('email', 'org_code'))
            ->withErrors(['login' => 'Login error: ' . $e->getMessage()]);
    }
})->name('login.post');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('auth.forgot-password');

Route::post('/forgot-password', function (Request $request) {
    // Validate the request
    $request->validate([
        'email' => 'required|email'
    ]);

    try {
        // Use Laravel's internal request handling
        $registrationController = app(\App\Modules\Registration\Controllers\RegistrationController::class);
        $response = $registrationController->forgotPassword($request);
        
        $data = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() === 200 && $data['success']) {
            // Show success page
            return view('auth.password-reset-sent', ['email' => $request->email]);
        } else {
            $errorMessage = $data['message'] ?? 'Email not found in our system.';
            
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $errorMessage]);
        }
    } catch (\Exception $e) {
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Error: ' . $e->getMessage()]);
    }
})->name('forgot-password.post');

// Password reset success page
Route::get('/password-reset-sent', function () {
    return view('auth.password-reset-sent');
})->name('password-reset-sent');

// Dashboard route (protected)
Route::get('/dashboard', function () {
    // Get authenticated user from session
    $user = session()->get('user');
    
    if (!$user) {
        return redirect('/login')->with('error', 'Please login first');
    }
    
    return view('dashboard', compact('user'));
})->name('dashboard');

// Logout route
Route::get('/logout', function () {
    // Clear session and redirect to login
    session()->flush();
    return redirect('/login')->with('message', 'Logged out successfully');
})->name('logout');

// Email verification page (web interface)
Route::get('/verify-email', function () {
    $token = request()->query('token');
    if (!$token) {
        return redirect('/login')->with('error', 'Invalid verification link');
    }
    
    // Show verification page
    return view('auth.verify-email', compact('token'));
})->name('verify-email');

// Redirect to Swagger UI for API documentation
Route::get('/api-docs', function () {
    return redirect('/swagger');
});
