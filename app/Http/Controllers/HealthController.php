<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('SELECT 1');
        } catch (Throwable) {
            return response()->json([
                'status' => 'unhealthy',
                'checks' => [
                    'application' => true,
                    'database' => false,
                ],
            ], 503);
        }

        return response()->json([
            'status' => 'healthy',
            'checks' => [
                'application' => true,
                'database' => true,
            ],
        ]);
    }
}
