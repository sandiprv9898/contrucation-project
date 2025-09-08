<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        
        // Protected authentication routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    // Protected user management routes
    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard routes
        Route::prefix('dashboard')->group(function () {
            Route::get('stats', [DashboardController::class, 'stats']);
            Route::get('recent-activity', [DashboardController::class, 'recentActivity']);
        });

        Route::prefix('users')->group(function () {
            Route::get('/', function () {
                return response()->json(['message' => 'User list endpoint - to be implemented']);
            });
            Route::get('/{user}', function () {
                return response()->json(['message' => 'User details endpoint - to be implemented']);
            });
            Route::put('/{user}', function () {
                return response()->json(['message' => 'Update user endpoint - to be implemented']);
            });
        });
    });
});