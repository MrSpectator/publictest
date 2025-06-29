<?php

namespace App\Modules\Registration\Services;

use App\Modules\Email\Services\EmailService;
use App\Modules\Registration\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    protected Request $request;
    protected EmailService $emailService;

    public function __construct(Request $request, EmailService $emailService)
    {
        $this->request = $request;
        $this->emailService = $emailService;
    }

    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        $type = $data['type'] ?? User::TYPE_INDIVIDUAL;
        
        $validator = Validator::make($data, [
            // Individual fields (required only if type is 1)
            'first_name' => 'required_if:type,1|string|max:255',
            'last_name' => 'required_if:type,1|string|max:255',
            'phone_number' => 'required_if:type,1|string|max:20',
            'company_address' => 'required_if:type,1|string|max:500',
            'company_url' => 'required_if:type,1|string|max:255',
            'country_id' => 'required_if:type,1|integer',
            'state_id' => 'required_if:type,1|integer',
            'company_name' => 'required_if:type,1|string|max:255',

            // Company fields (required only if type is 2)
            'company_name' => 'required_if:type,2|string|max:255',
            'company_contact_person' => 'required_if:type,2|string|max:255',
            'company_contact_number' => 'required_if:type,2|string|max:20',
            'company_address' => 'required_if:type,2|string|max:500',
            'company_url' => 'required_if:type,2|string|max:255',
            'country_id' => 'required_if:type,2|integer',
            'state_id' => 'required_if:type,2|integer',

            // Shared fields (required for both, but only if type matches)
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
            'type' => 'required|integer|in:1,2',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Create user data based on type
        $userData = [
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'company_address' => $data['company_address'] ?? null,
            'company_url' => $data['company_url'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'state_id' => $data['state_id'] ?? null,
            'type' => $type,
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
        ];

        // Add type-specific fields
        if ($type === User::TYPE_INDIVIDUAL) {
            $userData['first_name'] = $data['first_name'];
            $userData['last_name'] = $data['last_name'];
            $userData['phone_number'] = $data['phone_number'];
            $userData['company_name'] = $data['company_name'] ?? $data['first_name'] . ' ' . $data['last_name'];
        } else {
            $userData['company_name'] = $data['company_name'];
            $userData['company_contact_person'] = $data['company_contact_person'];
            $userData['company_contact_number'] = $data['company_contact_number'];
        }

        // Create organization and user in a transaction
        return DB::transaction(function () use ($userData, $data, $type) {
            // Create organization
            $organization = Organization::create([
                'name' => $userData['company_name'],
                'code' => Organization::generateUniqueCode(),
                'email' => $data['email'],
                'phone_number' => $type === User::TYPE_INDIVIDUAL ? $data['phone_number'] : $data['company_contact_number'],
                'address' => $data['company_address'] ?? null,
                'website' => $data['company_url'] ?? null,
                'country_id' => $data['country_id'] ?? null,
                'state_id' => $data['state_id'] ?? null,
                'is_active' => true
            ]);

            // Associate user with organization
            $userData['organization_id'] = $organization->id;

            // Create user
            $user = User::create($userData);

            // Send verification email with organization details
            $this->sendVerificationEmail($user, $organization);

            return $user;
        });
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        $type = $user->type;
        
        if ($type === User::TYPE_INDIVIDUAL) {
            $validator = Validator::make($data, [
                'first_name' => 'sometimes|required|string|max:255',
                'last_name' => 'sometimes|required|string|max:255',
                'phone_number' => 'sometimes|required|string|max:20',
                'company_address' => 'sometimes|required|string|max:500',
                'company_url' => 'sometimes|required|string|max:255',
                'country_id' => 'sometimes|required|integer',
                'state_id' => 'sometimes|required|integer',
                'company_name' => 'sometimes|required|string|max:255'
            ]);
        } else {
            $validator = Validator::make($data, [
                'company_name' => 'sometimes|required|string|max:255',
                'company_contact_person' => 'sometimes|required|string|max:255',
                'company_contact_number' => 'sometimes|required|string|max:20',
                'company_address' => 'sometimes|required|string|max:500',
                'company_url' => 'sometimes|required|string|max:255',
                'country_id' => 'sometimes|required|integer',
                'state_id' => 'sometimes|required|integer'
            ]);
        }

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
            throw ValidationException::withMessages(['current_password' => 'Current password is incorrect']);
        }

        $user->update(['password' => Hash::make($newPassword)]);

        return true;
    }

    /**
     * Send verification email
     */
    protected function sendVerificationEmail(User $user, Organization $organization): void
    {
        try {
            $token = Str::random(64);
            
            // Store token in cache for 24 hours
            Cache::put("email_verification_{$token}", $user->id, now()->addHours(24));

            // Render the email template
            $emailBody = view('emails.verification', [
                'user' => $user,
                'verification_url' => url("/verify-email?token={$token}"),
                'expires_at' => now()->addHours(24)->format('Y-m-d H:i:s'),
                'organization' => $organization
            ])->render();

            $emailData = [
                'to' => $user->email,
                'subject' => 'Welcome to iSalesBook - Verify Your Email',
                'body' => $emailBody
            ];

            $this->emailService->sendEmail($emailData);
            
            Log::info('Verification email sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'organization_code' => $organization->code
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verify email with token
     */
    public function verifyEmail(string $token): bool
    {
        $userId = Cache::get("email_verification_{$token}");
        
        if (!$userId) {
            return false;
        }

        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        $user->markEmailAsVerified();
        Cache::forget("email_verification_{$token}");

        return true;
    }

    /**
     * Resend verification email
     */
    public function resendVerification(string $email): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user || $user->isVerified()) {
            return false;
        }

        // Get the user's organization
        $organization = $user->organization;
        
        if (!$organization) {
            Log::error('User has no organization for resend verification', [
                'user_id' => $user->id,
                'email' => $email
            ]);
            return false;
        }

        $this->sendVerificationEmail($user, $organization);
        
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

        try {
            $token = Str::random(64);
            
            // Store token in cache for 1 hour
            Cache::put("password_reset_{$token}", $user->id, now()->addHour());

            // Render the email template
            $emailBody = view('emails.password-reset', [
                'user' => $user,
                'reset_url' => url("/api/registration/reset-password?token={$token}"),
                'expires_at' => now()->addHour()->format('Y-m-d H:i:s')
            ])->render();

            $emailData = [
                'to' => $user->email,
                'subject' => 'Reset Your iSalesBook Password',
                'body' => $emailBody
            ];

            $this->emailService->sendEmail($emailData);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $userId = Cache::get("password_reset_{$token}");
        
        if (!$userId) {
            return false;
        }

        $user = User::find($userId);
        
        if (!$user) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);
        Cache::forget("password_reset_{$token}");

        return true;
    }

    /**
     * Get registration statistics
     */
    public function getStatistics(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::active()->count();
        $verifiedUsers = User::verified()->count();
        $individualUsers = User::individual()->count();
        $companyUsers = User::company()->count();
        
        $recentRegistrations = User::where('created_at', '>=', now()->subDays(30))->count();
        $recentLogins = User::recentlyActive()->count();

        $registrationsBySource = User::selectRaw('registration_source, COUNT(*) as count')
            ->groupBy('registration_source')
            ->pluck('count', 'registration_source')
            ->toArray();

        $registrationsByMonth = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'verified_users' => $verifiedUsers,
            'individual_users' => $individualUsers,
            'company_users' => $companyUsers,
            'recent_registrations' => $recentRegistrations,
            'recent_logins' => $recentLogins,
            'registrations_by_source' => $registrationsBySource,
            'registrations_by_month' => $registrationsByMonth,
            'verification_rate' => $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 2) : 0,
            'activity_rate' => $totalUsers > 0 ? round(($recentLogins / $totalUsers) * 100, 2) : 0
        ];
    }

    /**
     * Search users with filters
     */
    public function searchUsers(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = User::query();

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
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
     * Check if phone number is available
     */
    public function isPhoneAvailable(string $phone): bool
    {
        return !User::where('phone_number', $phone)->exists();
    }

    /**
     * Check if company name is available
     */
    public function isCompanyNameAvailable(string $companyName): bool
    {
        return !User::where('company_name', $companyName)->exists();
    }
} 