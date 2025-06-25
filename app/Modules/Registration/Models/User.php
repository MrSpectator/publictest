<?php

namespace App\Modules\Registration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'date_of_birth',
        'gender',
        'profile_picture',
        'bio',
        'website',
        'location',
        'timezone',
        'language',
        'is_active',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'last_login_ip',
        'registration_ip',
        'registration_source',
        'preferences',
        'metadata'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'preferences' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Gender options
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';
    const GENDER_PREFER_NOT_TO_SAY = 'prefer_not_to_say';

    // Registration sources
    const SOURCE_WEB = 'web';
    const SOURCE_MOBILE = 'mobile';
    const SOURCE_API = 'api';
    const SOURCE_SOCIAL = 'social';
    const SOURCE_INVITE = 'invite';

    public static function getGenders()
    {
        return [
            self::GENDER_MALE,
            self::GENDER_FEMALE,
            self::GENDER_OTHER,
            self::GENDER_PREFER_NOT_TO_SAY
        ];
    }

    public static function getRegistrationSources()
    {
        return [
            self::SOURCE_WEB,
            self::SOURCE_MOBILE,
            self::SOURCE_API,
            self::SOURCE_SOCIAL,
            self::SOURCE_INVITE
        ];
    }

    /**
     * Check if user is verified
     */
    public function isVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Check if phone is verified
     */
    public function isPhoneVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get user's display name (username or name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->username ?: $this->name;
    }

    /**
     * Get user's age
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        
        return $this->date_of_birth->age;
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for verified users
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope for unverified users
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    /**
     * Scope for users by registration source
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('registration_source', $source);
    }

    /**
     * Scope for users registered in date range
     */
    public function scopeRegisteredBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for users who logged in recently
     */
    public function scopeRecentlyActive($query, $days = 30)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    /**
     * Search users by name, email, or username
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%");
        });
    }

    /**
     * Update last login information
     */
    public function updateLastLogin(string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?: request()->ip()
        ]);
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): void
    {
        $this->update(['email_verified_at' => now()]);
    }

    /**
     * Mark phone as verified
     */
    public function markPhoneAsVerified(): void
    {
        $this->update(['phone_verified_at' => now()]);
    }

    /**
     * Deactivate user account
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Activate user account
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Get user's profile completion percentage
     */
    public function getProfileCompletionPercentage(): int
    {
        $fields = [
            'name', 'email', 'username', 'phone', 'date_of_birth',
            'gender', 'profile_picture', 'bio', 'website', 'location'
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }

    /**
     * Check if profile is complete
     */
    public function isProfileComplete(): bool
    {
        return $this->getProfileCompletionPercentage() >= 80;
    }
} 