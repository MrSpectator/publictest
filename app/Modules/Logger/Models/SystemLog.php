<?php

namespace App\Modules\Logger\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="SystemLog",
 *     title="System Log",
 *     description="System log entry model"
 * )
 */
class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'category',
        'message',
        'context',
        'user_id',
        'ip_address',
        'user_agent',
        'request_id',
        'execution_time',
        'memory_usage',
        'stack_trace',
        'metadata'
    ];

    protected $casts = [
        'context' => 'array',
        'metadata' => 'array',
        'execution_time' => 'float',
        'memory_usage' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Log levels
    const LEVEL_EMERGENCY = 'emergency';
    const LEVEL_ALERT = 'alert';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_ERROR = 'error';
    const LEVEL_WARNING = 'warning';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_INFO = 'info';
    const LEVEL_DEBUG = 'debug';

    // Categories
    const CATEGORY_AUTH = 'authentication';
    const CATEGORY_API = 'api';
    const CATEGORY_DATABASE = 'database';
    const CATEGORY_EMAIL = 'email';
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_PERFORMANCE = 'performance';
    const CATEGORY_USER_ACTION = 'user_action';

    public static function getLevels()
    {
        return [
            self::LEVEL_EMERGENCY,
            self::LEVEL_ALERT,
            self::LEVEL_CRITICAL,
            self::LEVEL_ERROR,
            self::LEVEL_WARNING,
            self::LEVEL_NOTICE,
            self::LEVEL_INFO,
            self::LEVEL_DEBUG
        ];
    }

    public static function getCategories()
    {
        return [
            self::CATEGORY_AUTH,
            self::CATEGORY_API,
            self::CATEGORY_DATABASE,
            self::CATEGORY_EMAIL,
            self::CATEGORY_SYSTEM,
            self::CATEGORY_SECURITY,
            self::CATEGORY_PERFORMANCE,
            self::CATEGORY_USER_ACTION
        ];
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('message', 'like', "%{$search}%")
              ->orWhere('context', 'like', "%{$search}%")
              ->orWhere('ip_address', 'like', "%{$search}%");
        });
    }

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(property="level", type="string", example="info", enum={"info", "warning", "error", "debug"})
     * @OA\Property(property="message", type="string", example="User logged in")
     * @OA\Property(property="context", type="object", example={"user_id": 1})
     * @OA\Property(property="source", type="string", example="auth")
     * @OA\Property(property="ip_address", type="string", example="127.0.0.1")
     * @OA\Property(property="user_agent", type="string", example="Mozilla/5.0")
     * @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     */
} 