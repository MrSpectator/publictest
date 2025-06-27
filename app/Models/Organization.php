<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone_number',
        'address',
        'website',
        'country_id',
        'state_id',
        'is_active',
        'email_verified_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Generate a unique 5-digit organization code
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Check if organization is verified
     */
    public function isVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Check if organization is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): void
    {
        $this->update(['email_verified_at' => now()]);
    }

    /**
     * Activate organization
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate organization
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get organization's users
     */
    public function users()
    {
        return $this->hasMany(\App\Modules\Registration\Models\User::class);
    }

    /**
     * Scope for active organizations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for verified organizations
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Search organizations by name, email, or code
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }
}
