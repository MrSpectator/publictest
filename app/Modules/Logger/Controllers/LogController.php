<?php

namespace App\Modules\Logger\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Logger\Models\SystemLog;
use App\Modules\Logger\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Logger",
 *     description="System logging operations"
 * )
 */
class LogController extends Controller
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * @OA\Post(
     *     path="/api/logger/log",
     *     summary="Create a new log entry",
     *     tags={"Logger"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"level", "message"},
     *             @OA\Property(property="level", type="string", enum={"emergency", "alert", "critical", "error", "warning", "notice", "info", "debug"}),
     *             @OA\Property(property="category", type="string", enum={"authentication", "api", "database", "email", "system", "security", "performance", "user_action"}),
     *             @OA\Property(property="message", type="string", example="User login successful"),
     *             @OA\Property(property="context", type="object", example={"user_id": 123, "action": "login"}),
     *             @OA\Property(property="metadata", type="object", example={"ip": "192.168.1.1"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Log entry created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Log entry created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function createLog(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|string|in:' . implode(',', SystemLog::getLevels()),
            'category' => 'nullable|string|in:' . implode(',', SystemLog::getCategories()),
            'message' => 'required|string|max:1000',
            'context' => 'nullable|array',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $logData = $request->only(['level', 'category', 'message', 'context', 'metadata']);
        
        // Use the appropriate method based on level
        $method = $logData['level'];
        $log = $this->logService->$method(
            $logData['message'],
            $logData['context'] ?? [],
            $logData['metadata'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Log entry created successfully',
            'data' => $log
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/logger/logs",
     *     summary="Get logs with filters",
     *     tags={"Logger"},
     *     @OA\Parameter(
     *         name="level",
     *         in="query",
     *         description="Filter by log level",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in message, context, or IP address",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of logs per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=50)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getLogs(Request $request): JsonResponse
    {
        $filters = $request->only(['level', 'category', 'search', 'start_date', 'end_date', 'per_page']);
        
        $logs = $this->logService->getLogs($filters);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/logger/logs/{id}",
     *     summary="Get a specific log entry",
     *     tags={"Logger"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Log entry ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Log entry retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Log entry not found"
     *     )
     * )
     */
    public function getLog(int $id): JsonResponse
    {
        $log = SystemLog::with('user')->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Log entry not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/logger/statistics",
     *     summary="Get log statistics",
     *     tags={"Logger"},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Start date for statistics (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="End date for statistics (Y-m-d)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
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
    public function getStatistics(Request $request): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date']);
        
        $statistics = $this->logService->getStatistics($filters);

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/logger/levels",
     *     summary="Get available log levels",
     *     tags={"Logger"},
     *     @OA\Response(
     *         response=200,
     *         description="Log levels retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function getLevels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SystemLog::getLevels()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/logger/categories",
     *     summary="Get available log categories",
     *     tags={"Logger"},
     *     @OA\Response(
     *         response=200,
     *         description="Log categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function getCategories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SystemLog::getCategories()
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/logger/logs/{id}",
     *     summary="Delete a log entry",
     *     tags={"Logger"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Log entry ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Log entry deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Log entry deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Log entry not found"
     *     )
     * )
     */
    public function deleteLog(int $id): JsonResponse
    {
        $log = SystemLog::find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Log entry not found'
            ], 404);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log entry deleted successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logger/clean",
     *     summary="Clean old logs",
     *     tags={"Logger"},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="days", type="integer", description="Delete logs older than X days", default=30)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Old logs cleaned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Old logs cleaned successfully"),
     *             @OA\Property(property="deleted_count", type="integer", example=150)
     *         )
     *     )
     * )
     */
    public function cleanOldLogs(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        
        $deletedCount = $this->logService->cleanOldLogs($days);

        return response()->json([
            'success' => true,
            'message' => 'Old logs cleaned successfully',
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logger/emergency",
     *     summary="Log emergency message",
     *     tags={"Logger"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="System emergency"),
     *             @OA\Property(property="context", type="object"),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Emergency log created successfully"
     *     )
     * )
     */
    public function emergency(Request $request): JsonResponse
    {
        return $this->createSpecificLog($request, 'emergency');
    }

    /**
     * @OA\Post(
     *     path="/api/logger/error",
     *     summary="Log error message",
     *     tags={"Logger"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Database connection failed"),
     *             @OA\Property(property="context", type="object"),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Error log created successfully"
     *     )
     * )
     */
    public function error(Request $request): JsonResponse
    {
        return $this->createSpecificLog($request, 'error');
    }

    /**
     * @OA\Post(
     *     path="/api/logger/warning",
     *     summary="Log warning message",
     *     tags={"Logger"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="High memory usage detected"),
     *             @OA\Property(property="context", type="object"),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Warning log created successfully"
     *     )
     * )
     */
    public function warning(Request $request): JsonResponse
    {
        return $this->createSpecificLog($request, 'warning');
    }

    /**
     * @OA\Post(
     *     path="/api/logger/info",
     *     summary="Log info message",
     *     tags={"Logger"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="User login successful"),
     *             @OA\Property(property="context", type="object"),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Info log created successfully"
     *     )
     * )
     */
    public function info(Request $request): JsonResponse
    {
        return $this->createSpecificLog($request, 'info');
    }

    /**
     * Helper method for specific log level endpoints
     */
    private function createSpecificLog(Request $request, string $level): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'context' => 'nullable|array',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $log = $this->logService->$level(
            $request->input('message'),
            $request->input('context', []),
            $request->input('metadata', [])
        );

        return response()->json([
            'success' => true,
            'message' => ucfirst($level) . ' log created successfully',
            'data' => $log
        ], 201);
    }
} 