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
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'company_name',
        'company_contact_person',
        'company_contact_number',
        'company_url',
        'company_address',
        'country_id',
        'state_id',
        'type', // 1 = individual, 2 = company
        'organization_id', // Link to organization
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
        'is_active' => 'boolean',
        'type' => 'integer',
        'country_id' => 'integer',
        'state_id' => 'integer',
        'organization_id' => 'integer',
        'preferences' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // User types
    const TYPE_INDIVIDUAL = 1;
    const TYPE_COMPANY = 2;

    // Registration sources
    const SOURCE_WEB = 'web';
    const SOURCE_MOBILE = 'mobile';
    const SOURCE_API = 'api';
    const SOURCE_SOCIAL = 'social';
    const SOURCE_INVITE = 'invite';

    public static function getUserTypes()
    {
        return [
            self::TYPE_INDIVIDUAL => 'Individual',
            self::TYPE_COMPANY => 'Company'
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
     * Check if user is individual
     */
    public function isIndividual(): bool
    {
        return $this->type === self::TYPE_INDIVIDUAL;
    }

    /**
     * Check if user is company
     */
    public function isCompany(): bool
    {
        return $this->type === self::TYPE_COMPANY;
    }

    /**
     * Get user's full name
     */
    public function getFullNameAttribute(): string
    {
        if ($this->isIndividual()) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        return $this->company_name;
    }

    /**
     * Get user's display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->isIndividual()) {
            return $this->getFullNameAttribute();
        }
        return $this->company_name;
    }

    /**
     * Get contact person name (for companies)
     */
    public function getContactPersonAttribute(): ?string
    {
        if ($this->isCompany()) {
            return $this->company_contact_person;
        }
        return $this->getFullNameAttribute();
    }

    /**
     * Get contact number
     */
    public function getContactNumberAttribute(): ?string
    {
        if ($this->isCompany()) {
            return $this->company_contact_number;
        }
        return $this->phone_number;
    }

    /**
     * Scope for individual users
     */
    public function scopeIndividual($query)
    {
        return $query->where('type', self::TYPE_INDIVIDUAL);
    }

    /**
     * Scope for company users
     */
    public function scopeCompany($query)
    {
        return $query->where('type', self::TYPE_COMPANY);
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
     * Search users by name, email, or company name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('company_contact_person', 'like', "%{$search}%");
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
        if ($this->isIndividual()) {
            $fields = [
                'first_name', 'last_name', 'email', 'phone_number',
                'company_address', 'company_url', 'country_id', 'state_id'
            ];
        } else {
            $fields = [
                'company_name', 'company_contact_person', 'company_contact_number',
                'email', 'company_address', 'company_url', 'country_id', 'state_id'
            ];
        }

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

    /**
     * Get user's organization
     */
    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }

    /**
     * Get user's organization code
     */
    public function getOrganizationCodeAttribute(): ?string
    {
        return $this->organization?->code;
    }
} 