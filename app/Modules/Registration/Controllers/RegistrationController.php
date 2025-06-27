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
    }

    /**
     * @OA\Post(
     *     path="/api/registration/register",
     *     summary="Register a new user (Individual or Company)",
     *     tags={"Registration"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "password_confirmation", "type"},
     *             @OA\Property(property="type", type="integer", example=1, description="1=Individual, 2=Company"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", minLength=8, example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="first_name", type="string", example="Jane"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone_number", type="string", example="08012345678"),
     *             @OA\Property(property="company_address", type="string", example="123 Main St"),
     *             @OA\Property(property="company_url", type="string", example="https://company.com"),
     *             @OA\Property(property="country_id", type="integer", example=1),
     *             @OA\Property(property="state_id", type="integer", example=10),
     *             @OA\Property(property="company_name", type="string", example="Acme Inc."),
     *             @OA\Property(property="company_contact_person", type="string", example="John Manager"),
     *             @OA\Property(property="company_contact_number", type="string", example="09098765432")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $user = $this->registrationService->register($request->all());

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. Please check your email for verification.',
                'data' => [
                    'user' => $user->only(['id', 'email', 'type', 'created_at']),
                    'organization' => [
                        'name' => $user->organization->name,
                        'code' => $user->organization->code
                    ]
                ]
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", example="verification_token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Email verified successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired token"
     *     )
     * )
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $verified = $this->registrationService->verifyEmail($request->token);

        if ($verified) {
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired verification token'
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/registration/resend-verification",
     *     summary="Resend verification email",
     *     tags={"Registration"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification email sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Verification email sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="User not found or already verified"
     *     )
     * )
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $sent = $this->registrationService->resendVerification($request->email);

        if ($sent) {
            return response()->json([
                'success' => true,
                'message' => 'Verification email sent successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not found or already verified'
        ], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/registration/forgot-password",
     *     summary="Send password reset email",
     *     tags={"Registration"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset email sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset email sent")
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $sent = $this->registrationService->sendPasswordResetEmail($request->email);

        return response()->json([
            'success' => true,
            'message' => 'If the email exists, a password reset link has been sent'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/registration/reset-password",
     *     summary="Reset password with token",
     *     tags={"Registration"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token", "password", "password_confirmation"},
     *             @OA\Property(property="token", type="string", example="reset_token_here"),
     *             @OA\Property(property="password", type="string", minLength=8, example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired token"
     *     )
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $reset = $this->registrationService->resetPassword($request->token, $request->password);

        if ($reset) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired reset token'
        ], 400);
    }

    /**
     * @OA\Get(
     *     path="/api/registration/profile",
     *     summary="Get user profile",
     *     tags={"Registration"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getProfile(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/registration/profile",
     *     summary="Update user profile",
     *     tags={"Registration"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="phone_number", type="string", example="+1234567890"),
     *             @OA\Property(property="company_name", type="string", example="My Company"),
     *             @OA\Property(property="company_contact_person", type="string", example="Jane Smith"),
     *             @OA\Property(property="company_contact_number", type="string", example="+1234567890"),
     *             @OA\Property(property="company_url", type="string", example="https://example.com"),
     *             @OA\Property(property="company_address", type="string", example="123 Main St"),
     *             @OA\Property(property="country_id", type="integer", example=159),
     *             @OA\Property(property="state_id", type="integer", example=285)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $this->registrationService->updateProfile(Auth::user(), $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/registration/change-password",
     *     summary="Change user password",
     *     tags={"Registration"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldpassword123"),
     *             @OA\Property(property="new_password", type="string", minLength=8, example="newpassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password changed successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Current password is incorrect"
     *     )
     * )
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        try {
            $this->registrationService->changePassword(
                Auth::user(),
                $request->current_password,
                $request->new_password
            );

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/registration/check-availability",
     *     summary="Check if email/phone/company name is available",
     *     tags={"Registration"},
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Check email availability",
     *         required=false,
     *         @OA\Schema(type="string", format="email")
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="Check phone availability",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="company_name",
     *         in="query",
     *         description="Check company name availability",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Availability check completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $results = [];

        if ($request->has('email')) {
            $results['email'] = $this->registrationService->isEmailAvailable($request->email);
        }

        if ($request->has('phone')) {
            $results['phone'] = $this->registrationService->isPhoneAvailable($request->phone);
        }

        if ($request->has('company_name')) {
            $results['company_name'] = $this->registrationService->isCompanyNameAvailable($request->company_name);
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/registration/statistics",
     *     summary="Get registration statistics",
     *     tags={"Registration"},
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getStatistics(): JsonResponse
    {
        $statistics = $this->registrationService->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/registration/users",
     *     summary="Search users with filters",
     *     tags={"Registration"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, email, or company name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by user type (1=Individual, 2=Company)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (active/inactive)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="verified",
     *         in="query",
     *         description="Filter by verification status (yes/no)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Users retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'type', 'status', 'verified', 'source', 
            'start_date', 'end_date', 'per_page'
        ]);

        $users = $this->registrationService->searchUsers($filters);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/registration/types",
     *     summary="Get available user types",
     *     tags={"Registration"},
     *     @OA\Response(
     *         response=200,
     *         description="User types retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getUserTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => User::getUserTypes()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/registration/sources",
     *     summary="Get available registration sources",
     *     tags={"Registration"},
     *     @OA\Response(
     *         response=200,
     *         description="Sources retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function getSources(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => User::getRegistrationSources()
        ]);
    }
} 