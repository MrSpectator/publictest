<?php

namespace App\Modules\Registration\Services;

use App\Modules\Registration\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request ?? request();
    }

    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'username' => 'nullable|string|max:50|unique:users|alpha_dash',
            'phone' => 'nullable|string|max:20|unique:users',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:' . implode(',', User::getGenders()),
            'accept_terms' => 'required|accepted',
            'accept_privacy' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Generate username if not provided
        if (empty($data['username'])) {
            $data['username'] = $this->generateUsername($data['name']);
        }

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'username' => $data['username'],
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'registration_ip' => $this->request->ip(),
            'registration_source' => $this->getRegistrationSource(),
            'is_active' => true,
            'preferences' => [
                'email_notifications' => true,
                'sms_notifications' => false,
                'marketing_emails' => false,
                'privacy_level' => 'public'
            ],
            'metadata' => [
                'registration_method' => 'email',
                'user_agent' => $this->request->userAgent(),
                'referrer' => $this->request->header('referer')
            ]
        ]);

        // Send verification email
        $this->sendVerificationEmail($user);

        return $user;
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:50|alpha_dash|unique:users,username,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:20|unique:users,phone,' . $user->id,
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'gender' => 'sometimes|nullable|string|in:' . implode(',', User::getGenders()),
            'bio' => 'sometimes|nullable|string|max:500',
            'website' => 'sometimes|nullable|url|max:255',
            'location' => 'sometimes|nullable|string|max:255',
            'timezone' => 'sometimes|nullable|string|max:50',
            'language' => 'sometimes|nullable|string|max:10'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $user->update($data);

        return $user->fresh();
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(User $user, array $preferences): User
    {
        $currentPreferences = $user->preferences ?? [];
        $updatedPreferences = array_merge($currentPreferences, $preferences);

        $user->update(['preferences' => $updatedPreferences]);

        return $user->fresh();
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new ValidationException(Validator::make([], []), 'Current password is incorrect');
        }

        $user->update(['password' => Hash::make($newPassword)]);

        return true;
    }

    /**
     * Send verification email
     */
    public function sendVerificationEmail(User $user): void
    {
        // Generate verification token
        $token = Str::random(64);
        
        // Store token in cache for 24 hours
        cache()->put("email_verification_{$user->id}", $token, now()->addHours(24));

        // Send email (you can implement your email service here)
        // For now, we'll just log it
        \Log::info("Verification email sent to {$user->email} with token: {$token}");
    }

    /**
     * Verify email with token
     */
    public function verifyEmail(string $token): bool
    {
        // Find user by token
        $userId = null;
        foreach (cache()->get('email_verification_*') as $key => $value) {
            if ($value === $token) {
                $userId = str_replace('email_verification_', '', $key);
                break;
            }
        }

        if (!$userId) {
            return false;
        }

        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $user->markEmailAsVerified();
        cache()->forget("email_verification_{$userId}");

        return true;
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $email): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }

        $token = Str::random(64);
        cache()->put("password_reset_{$user->id}", $token, now()->addHours(1));

        // Send email (you can implement your email service here)
        \Log::info("Password reset email sent to {$email} with token: {$token}");

        return true;
    }

    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        // Find user by token
        $userId = null;
        foreach (cache()->get('password_reset_*') as $key => $value) {
            if ($value === $token) {
                $userId = str_replace('password_reset_', '', $key);
                break;
            }
        }

        if (!$userId) {
            return false;
        }

        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);
        cache()->forget("password_reset_{$userId}");

        return true;
    }

    /**
     * Deactivate user account
     */
    public function deactivateAccount(User $user): bool
    {
        $user->deactivate();
        return true;
    }

    /**
     * Reactivate user account
     */
    public function reactivateAccount(User $user): bool
    {
        $user->activate();
        return true;
    }

    /**
     * Delete user account
     */
    public function deleteAccount(User $user): bool
    {
        $user->delete();
        return true;
    }

    /**
     * Get user statistics
     */
    public function getStatistics(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::active()->count();
        $verifiedUsers = User::verified()->count();
        $recentRegistrations = User::where('created_at', '>=', now()->subDays(30))->count();
        $recentlyActive = User::recentlyActive()->count();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'verified_users' => $verifiedUsers,
            'unverified_users' => $totalUsers - $verifiedUsers,
            'recent_registrations' => $recentRegistrations,
            'recently_active' => $recentlyActive,
            'verification_rate' => $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 2) : 0
        ];
    }

    /**
     * Search users
     */
    public function searchUsers(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = User::query();

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->active();
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if (isset($filters['verified'])) {
            if ($filters['verified'] === 'yes') {
                $query->verified();
            } elseif ($filters['verified'] === 'no') {
                $query->unverified();
            }
        }

        if (isset($filters['source'])) {
            $query->bySource($filters['source']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->registeredBetween($filters['start_date'], $filters['end_date']);
        }

        $perPage = $filters['per_page'] ?? 20;
        
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Generate unique username
     */
    protected function generateUsername(string $name): string
    {
        $baseUsername = Str::slug($name);
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Get registration source
     */
    protected function getRegistrationSource(): string
    {
        $userAgent = $this->request->userAgent();
        
        if (str_contains($userAgent, 'Mobile')) {
            return User::SOURCE_MOBILE;
        }
        
        if ($this->request->is('api/*')) {
            return User::SOURCE_API;
        }
        
        return User::SOURCE_WEB;
    }

    /**
     * Check if email is available
     */
    public function isEmailAvailable(string $email): bool
    {
        return !User::where('email', $email)->exists();
    }

    /**
     * Check if username is available
     */
    public function isUsernameAvailable(string $username): bool
    {
        return !User::where('username', $username)->exists();
    }

    /**
     * Check if phone is available
     */
    public function isPhoneAvailable(string $phone): bool
    {
        return !User::where('phone', $phone)->exists();
    }
} 