<?php

namespace App\Modules\Registration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Registration\Models\User;
use App\Modules\Registration\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Registration",
 *     description="User registration and profile management"
 * )
 */
class RegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
        $this->middleware('auth:sanctum')->only(['getProfile', 'updateProfile', 'changePassword']);
    }

    /**
     * @OA\Post(
     *     path="/api/registration/register",
     *     summary="Register a new user",
     *     tags={"Registration"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation", "accept_terms", "accept_privacy"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", minLength=8, example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="gender", type="string", enum={"male", "female", "other", "prefer_not_to_say"}),
     *             @OA\Property(property="accept_terms", type="boolean", example=true),
     *             @OA\Property(property="accept_privacy", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $user = $this->registrationService->register($request->all());

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please check your email for verification.',
                'data' => $user->only(['id', 'name', 'email', 'username', 'created_at'])
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/registration/verify-email",
     *     summary="Verify email with token",
     *     tags={"Registration"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"token"}, @OA\Property(property="token", type="string"))),
     *     @OA\Response(response=200, description="Email verified successfully"),
     *     @OA\Response(response=400, description="Invalid or expired token")
     * )
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);
        $verified = $this->registrationService->verifyEmail($request->token);
        return $verified
            ? response()->json(['success' => true, 'message' => 'Email verified successfully'])
            : response()->json(['success' => false, 'message' => 'Invalid or expired verification token'], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/registration/resend-verification",
     *     summary="Resend verification email",
     *     tags={"Registration"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email"}, @OA\Property(property="email", type="string", format="email"))),
     *     @OA\Response(response=200, description="Verification email sent"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
        if ($user->isVerified()) {
            return response()->json(['success' => false, 'message' => 'Email is already verified'], 400);
        }

        $this->registrationService->sendVerificationEmail($user);
        return response()->json(['success' => true, 'message' => 'Verification email sent']);
    }

    /**
     * @OA\Post(
     *     path="/api/registration/forgot-password",
     *     summary="Send password reset email",
     *     tags={"Registration"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"email"}, @OA\Property(property="email", type="string", format="email"))),
     *     @OA\Response(response=200, description="Password reset email sent")
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        $this->registrationService->sendPasswordResetEmail($request->email);
        return response()->json(['success' => true, 'message' => 'If the email exists, a password reset link has been sent']);
    }

    /**
     * @OA\Post(
     *     path="/api/registration/reset-password",
     *     summary="Reset password with token",
     *     tags={"Registration"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"token", "password", "password_confirmation"}, @OA\Property(property="token", type="string"), @OA\Property(property="password", type="string", minLength=8), @OA\Property(property="password_confirmation", type="string"))),
     *     @OA\Response(response=200, description="Password reset successfully"),
     *     @OA\Response(response=400, description="Invalid token or validation error")
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $reset = $this->registrationService->resetPassword($request->token, $request->password);
        return $reset
            ? response()->json(['success' => true, 'message' => 'Password has been reset successfully.'])
            : response()->json(['success' => false, 'message' => 'Invalid or expired password reset token.'], 400);
    }
    
    /**
     * @OA\Get(
     *     path="/api/registration/profile",
     *     summary="Get authenticated user profile",
     *     tags={"Registration"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Profile retrieved successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function getProfile(): JsonResponse
    {
        $user = Auth::user();
        return response()->json(['success' => true, 'data' => $user]);
    }

    /**
     * @OA\Put(
     *     path="/api/registration/profile",
     *     summary="Update authenticated user profile",
     *     tags={"Registration"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="username", type="string"),
     *         @OA\Property(property="phone", type="string"),
     *         @OA\Property(property="date_of_birth", type="string", format="date"),
     *         @OA\Property(property="gender", type="string"),
     *         @OA\Property(property="bio", type="string"),
     *         @OA\Property(property="website", type="string", format="url"),
     *         @OA\Property(property="location", type="string"),
     *         @OA\Property(property="timezone", type="string"),
     *         @OA\Property(property="language", type="string")
     *     )),
     *     @OA\Response(response=200, description="Profile updated successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::user();
        $updatedUser = $this->registrationService->updateProfile($user, $request->all());
        return response()->json(['success' => true, 'message' => 'Profile updated successfully', 'data' => $updatedUser]);
    }

    /**
     * @OA\Post(
     *     path="/api/registration/change-password",
     *     summary="Change authenticated user password",
     *     tags={"Registration"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"current_password", "password", "password_confirmation"}, @OA\Property(property="current_password", type="string"), @OA\Property(property="password", type="string", minLength=8), @OA\Property(property="password_confirmation", type="string"))),
     *     @OA\Response(response=200, description="Password changed successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $this->registrationService->changePassword(Auth::user(), $request->current_password, $request->password);
        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }

    /**
     * @OA\Get(
     *     path="/api/registration/check-availability",
     *     summary="Check availability of email, username, or phone",
     *     tags={"Registration"},
     *     @OA\Parameter(name="email", in="query", schema=@OA\Schema(type="string", format="email")),
     *     @OA\Parameter(name="username", in="query", schema=@OA\Schema(type="string")),
     *     @OA\Parameter(name="phone", in="query", schema=@OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Availability checked")
     * )
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $results = [];
        if ($request->has('email')) {
            $results['email'] = $this->registrationService->isEmailAvailable($request->email);
        }
        if ($request->has('username')) {
            $results['username'] = $this->registrationService->isUsernameAvailable($request->username);
        }
        if ($request->has('phone')) {
            $results['phone'] = $this->registrationService->isPhoneAvailable($request->phone);
        }
        return response()->json(['success' => true, 'data' => $results]);
    }

    /**
     * @OA\Get(
     *     path="/api/registration/statistics",
     *     summary="Get registration statistics",
     *     tags={"Registration"},
     *     @OA\Response(response=200, description="Statistics retrieved successfully")
     * )
     */
    public function getStatistics(): JsonResponse
    {
        $statistics = $this->registrationService->getStatistics();
        return response()->json(['success' => true, 'data' => $statistics]);
    }
    
    /**
     * @OA\Get(
     *     path="/api/registration/genders",
     *     summary="Get available genders",
     *     tags={"Registration"},
     *     @OA\Response(response=200, description="Genders retrieved")
     * )
     */
    public function getGenders(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => User::getGenders()]);
    }
    
    /**
     * @OA\Get(
     *     path="/api/registration/sources",
     *     summary="Get available registration sources",
     *     tags={"Registration"},
     *     @OA\Response(response=200, description="Sources retrieved")
     * )
     */
    public function getSources(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => User::getSources()]);
    }
}