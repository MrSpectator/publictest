<?php

namespace App\Modules\Logger\Services;

use App\Modules\Logger\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LogService
{
    protected $request;

    public function __construct(Request $request = null)
    {
        $this->request = $request ?? request();
    }

    /**
     * Log an emergency message
     */
    public function emergency(string $message, array $context = [], array $metadata = [])
    {
        return $this->log(SystemLog::LEVEL_EMERGENCY, $message, $context, $metadata);
    }

    /**
     * Log an alert message
     */
    public function alert(string $message, array $context = [], array $metadata = [])
    {
        return $this->log(SystemLog::LEVEL_ALERT, $message, $context, $metadata);
    }

    /**
     * Log a critical message
     */
    public function critical(string $message, array $context = [], array $metadata = [])
    {
        return $this->log(SystemLog::LEVEL_CRITICAL, $message, $context, $metadata);
    }

    /**
     * Log an error message
     */
    public function error(string $message, array $context = [], array $metadata = [])
    {
        return $this->log(SystemLog::LEVEL_ERROR, $message, $context, $metadata);
    }

    /**
     * Log a warning message
     */
    public function warning(string $message, array $context = [], array $metadata = [])
    {
        return $this->log(SystemLog::LEVEL_WARNING, $message, $context, $metadata);
    }

    /**
     * Log a notice message
     */
    public function notice(string $message, array $context = [], array $metadata = [])
    {
        return $this->log(SystemLog::LEVEL_NOTICE, $message, $context, $metadata);
    }

    /**
     * Log an info message
     */
    public function info(string $message, array $context = [], array $metadata = [])
    {
        return $this->log(SystemLog::LEVEL_INFO, $message, $context, $metadata);
    }

    /**
     * Log a debug message
     */
    public function debug(string $message, array $context = [], array $metadata = [])
    {
        return $this->log(SystemLog::LEVEL_DEBUG, $message, $context, $metadata);
    }

    /**
     * Log API request
     */
    public function logApiRequest(string $message, array $context = [], array $metadata = [])
    {
        $metadata['category'] = SystemLog::CATEGORY_API;
        return $this->info($message, $context, $metadata);
    }

    /**
     * Log authentication event
     */
    public function logAuth(string $message, array $context = [], array $metadata = [])
    {
        $metadata['category'] = SystemLog::CATEGORY_AUTH;
        return $this->info($message, $context, $metadata);
    }

    /**
     * Log database operation
     */
    public function logDatabase(string $message, array $context = [], array $metadata = [])
    {
        $metadata['category'] = SystemLog::CATEGORY_DATABASE;
        return $this->info($message, $context, $metadata);
    }

    /**
     * Log email operation
     */
    public function logEmail(string $message, array $context = [], array $metadata = [])
    {
        $metadata['category'] = SystemLog::CATEGORY_EMAIL;
        return $this->info($message, $context, $metadata);
    }

    /**
     * Log security event
     */
    public function logSecurity(string $message, array $context = [], array $metadata = [])
    {
        $metadata['category'] = SystemLog::CATEGORY_SECURITY;
        return $this->warning($message, $context, $metadata);
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $message, float $executionTime = null, int $memoryUsage = null, array $context = [], array $metadata = [])
    {
        $metadata['category'] = SystemLog::CATEGORY_PERFORMANCE;
        if ($executionTime) {
            $metadata['execution_time'] = $executionTime;
        }
        if ($memoryUsage) {
            $metadata['memory_usage'] = $memoryUsage;
        }
        return $this->info($message, $context, $metadata);
    }

    /**
     * Log user action
     */
    public function logUserAction(string $message, array $context = [], array $metadata = [])
    {
        $metadata['category'] = SystemLog::CATEGORY_USER_ACTION;
        return $this->info($message, $context, $metadata);
    }

    /**
     * Log exception with stack trace
     */
    public function logException(\Throwable $exception, array $context = [], array $metadata = [])
    {
        $metadata['stack_trace'] = $exception->getTraceAsString();
        $metadata['file'] = $exception->getFile();
        $metadata['line'] = $exception->getLine();
        
        return $this->error($exception->getMessage(), $context, $metadata);
    }

    /**
     * Main logging method
     */
    protected function log(string $level, string $message, array $context = [], array $metadata = [])
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $logData = [
            'level' => $level,
            'category' => $metadata['category'] ?? SystemLog::CATEGORY_SYSTEM,
            'message' => $message,
            'context' => $context,
            'user_id' => Auth::id(),
            'ip_address' => $this->request->ip(),
            'user_agent' => $this->request->userAgent(),
            'request_id' => $this->getRequestId(),
            'execution_time' => microtime(true) - $startTime,
            'memory_usage' => memory_get_usage() - $startMemory,
            'metadata' => $metadata
        ];

        // Add stack trace for error levels
        if (in_array($level, [SystemLog::LEVEL_ERROR, SystemLog::LEVEL_CRITICAL, SystemLog::LEVEL_ALERT, SystemLog::LEVEL_EMERGENCY])) {
            $logData['stack_trace'] = (new \Exception())->getTraceAsString();
        }

        return SystemLog::create($logData);
    }

    /**
     * Get or generate request ID
     */
    protected function getRequestId(): string
    {
        if (!$this->request->hasHeader('X-Request-ID')) {
            $this->request->headers->set('X-Request-ID', Str::uuid()->toString());
        }
        
        return $this->request->header('X-Request-ID');
    }

    /**
     * Get logs with filters
     */
    public function getLogs(array $filters = [])
    {
        $query = SystemLog::with('user');

        // Apply filters
        if (isset($filters['level'])) {
            $query->byLevel($filters['level']);
        }

        if (isset($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Order by created_at desc by default
        $query->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $filters['per_page'] ?? 50;
        
        return $query->paginate($perPage);
    }

    /**
     * Get log statistics
     */
    public function getStatistics(array $filters = [])
    {
        $query = SystemLog::query();

        // Apply date range filter if provided
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        $stats = [
            'total_logs' => $query->count(),
            'levels' => $query->selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level')
                ->toArray(),
            'categories' => $query->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'recent_errors' => $query->whereIn('level', [SystemLog::LEVEL_ERROR, SystemLog::LEVEL_CRITICAL, SystemLog::LEVEL_ALERT, SystemLog::LEVEL_EMERGENCY])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        return $stats;
    }

    /**
     * Clean old logs
     */
    public function cleanOldLogs(int $days = 30)
    {
        $cutoffDate = now()->subDays($days);
        
        return SystemLog::where('created_at', '<', $cutoffDate)
            ->whereNotIn('level', [SystemLog::LEVEL_CRITICAL, SystemLog::LEVEL_ALERT, SystemLog::LEVEL_EMERGENCY])
            ->delete();
    }
} 