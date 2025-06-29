<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Auth\Services\AuthService;

class TestController extends Controller
{
    public function testLogin(Request $request): JsonResponse
    {
        try {
            $authService = new AuthService();
            
            $result = $authService->login(
                $request->email ?? 'test@example.com',
                $request->org_code ?? '12345',
                $request->password ?? 'password'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Test successful',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
} 