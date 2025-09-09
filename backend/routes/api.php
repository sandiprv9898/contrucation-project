<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
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

        // User Management Routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/by-role', [UserController::class, 'getUsersByRole']);
            Route::get('/company', [UserController::class, 'getCompanyUsers']);
            Route::get('/{user}', [UserController::class, 'show']);
            Route::put('/{user}', [UserController::class, 'update']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
            Route::post('/{user}/avatar', [UserController::class, 'uploadAvatar']);
            Route::post('/{user}/resend-verification', [UserController::class, 'resendVerification']);
        });
    });
});