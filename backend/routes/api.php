<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Unauthenticated response route
Route::get('unauthenticated', function() {
    return response()->json(['message' => 'Unauthenticated'], 401);
});

Route::prefix('v1')->group(function () {
    // Temporary test route for settings (remove in production)
    Route::get('test-settings', function() {
        return response()->json([
            'message' => 'Settings API is working',
            'data' => [
                'company' => [
                    'company_name' => 'Test Company',
                    'email' => 'test@company.com',
                    'phone' => '555-0123'
                ],
                'system' => [
                    'app_name' => 'Construction Platform',
                    'maintenance_mode' => 'disabled'
                ]
            ]
        ]);
    });
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

        // Settings Management Routes
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingController::class, 'index']);
            Route::put('/{category}', [SettingController::class, 'updateCategory']);
            Route::post('/{category}/reset', [SettingController::class, 'resetCategoryToDefaults']);
            Route::post('/import', [SettingController::class, 'import']);
            Route::get('/export', [SettingController::class, 'export']);
            Route::get('/permissions', [SettingController::class, 'permissions']);
            Route::get('/validations', [SettingController::class, 'validations']);
            Route::get('/system/health', [SettingController::class, 'systemHealth']);
            Route::post('/system/maintenance', [SettingController::class, 'toggleMaintenance']);
            Route::post('/company/logo', [SettingController::class, 'uploadLogo']);
        });
    });
});