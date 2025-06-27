<?php

namespace App\Modules\Auth\Services;

use App\Modules\Registration\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Authenticate user with email, organization code, and password
     */
    public function login(string $email, string $orgCode, string $password): array
    {
        // Find organization by code
        $organization = Organization::where('code', $orgCode)
            ->where('is_active', true)
            ->first();

        if (!$organization) {
            throw ValidationException::withMessages([
                'org_code' => 'Invalid organization code or organization is inactive.'
            ]);
        }

        // Find user by email and organization
        $user = User::where('email', $email)
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials or user is inactive.'
            ]);
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Invalid credentials.'
            ]);
        }

        // Check if email is verified
        if (!$user->isVerified()) {
            throw ValidationException::withMessages([
                'email' => 'Please verify your email address before logging in.'
            ]);
        }

        // Update last login
        $user->updateLastLogin();

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'organization_id' => $organization->id,
            'organization_code' => $organization->code
        ]);

        return [
            'user' => $user->load('organization'),
            'organization' => $organization,
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): bool
    {
        // Revoke all tokens for the user
        $user->tokens()->delete();

        Log::info('User logged out', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return true;
    }

    /**
     * Get current authenticated user with organization
     */
    public function getCurrentUser(): ?User
    {
        $user = Auth::user();
        
        if ($user) {
            return $user->load('organization');
        }

        return null;
    }

    /**
     * Validate organization code
     */
    public function validateOrganizationCode(string $orgCode): bool
    {
        return Organization::where('code', $orgCode)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get organization by code
     */
    public function getOrganizationByCode(string $orgCode): ?Organization
    {
        return Organization::where('code', $orgCode)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check if user belongs to organization
     */
    public function userBelongsToOrganization(User $user, string $orgCode): bool
    {
        return $user->organization && $user->organization->code === $orgCode;
    }
} 