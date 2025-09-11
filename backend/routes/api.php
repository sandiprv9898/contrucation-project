<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\CompanyProfileController;
use App\Http\Controllers\Api\CompanyBrandingController;
use App\Http\Controllers\Api\CompanyPortfolioController;
use App\Http\Controllers\Api\LocalizationController;
use App\Http\Controllers\Api\Admin\TranslationManagementController;
use App\Http\Controllers\Api\V1\Project\ProjectController;
use App\Http\Controllers\Api\V1\Task\TaskController;
use App\Http\Controllers\Api\V1\Task\TaskAttachmentController;
use App\Http\Controllers\Api\V1\Task\TimeTrackingController;
use App\Http\Controllers\Api\V1\Task\TaskNotificationController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Unauthenticated response route
Route::get('unauthenticated', function() {
    return response()->json(['message' => 'Unauthenticated'], 401);
});

Route::prefix('v1')->group(function () {
    // Localization routes (public access)
    Route::prefix('localization')->group(function () {
        Route::get('languages', [LocalizationController::class, 'languages']);
        Route::get('translations/{language}', [LocalizationController::class, 'translations']);
        Route::get('construction-terms/{language}', [LocalizationController::class, 'constructionTerms']);
        Route::get('search', [LocalizationController::class, 'search']);
        Route::get('translation/{language}/{key}', [LocalizationController::class, 'translation']);
        Route::get('language-stats/{language}', [LocalizationController::class, 'languageStats']);
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
        // User localization routes
        Route::prefix('localization')->group(function () {
            Route::get('user/language', [LocalizationController::class, 'userLanguage']);
            Route::post('user/language', [LocalizationController::class, 'updateUserLanguage']);
        });

        // Admin translation management routes
        Route::prefix('admin/translations')->middleware('role:admin')->group(function () {
            Route::get('keys', [TranslationManagementController::class, 'keys']);
            Route::post('keys', [TranslationManagementController::class, 'createKey']);
            Route::get('keys/{keyId}/translations', [TranslationManagementController::class, 'keyTranslations']);
            Route::post('translations', [TranslationManagementController::class, 'saveTranslation']);
            Route::post('translations/{translationId}/approve', [TranslationManagementController::class, 'approveTranslation']);
            Route::post('translations/{translationId}/reject', [TranslationManagementController::class, 'rejectTranslation']);
            Route::post('bulk-import', [TranslationManagementController::class, 'bulkImport']);
            Route::get('pending', [TranslationManagementController::class, 'pendingTranslations']);
            Route::post('clear-cache', [TranslationManagementController::class, 'clearCache']);
        });
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

        // Company Settings Routes
        Route::prefix('company')->group(function () {
            // Company Profile Management
            Route::prefix('profile')->group(function () {
                Route::get('/', [CompanyProfileController::class, 'show']);
                Route::put('/', [CompanyProfileController::class, 'update']);
            });

            // Company Branding Management
            Route::prefix('branding')->group(function () {
                Route::get('/', [CompanyBrandingController::class, 'index']);
                Route::post('/', [CompanyBrandingController::class, 'store']);
                Route::get('/{branding}', [CompanyBrandingController::class, 'show']);
                Route::put('/{branding}', [CompanyBrandingController::class, 'update']);
                Route::delete('/{branding}', [CompanyBrandingController::class, 'destroy']);
                Route::post('/upload', [CompanyBrandingController::class, 'upload']);
            });

            // Company Portfolio Management
            Route::prefix('portfolio')->group(function () {
                Route::get('/', [CompanyPortfolioController::class, 'index']);
                Route::post('/', [CompanyPortfolioController::class, 'store']);
                Route::get('/{portfolio}', [CompanyPortfolioController::class, 'show']);
                Route::put('/{portfolio}', [CompanyPortfolioController::class, 'update']);
                Route::delete('/{portfolio}', [CompanyPortfolioController::class, 'destroy']);
                Route::post('/{portfolio}/reorder', [CompanyPortfolioController::class, 'reorder']);
            });
        });

        // Project Management Routes
        Route::prefix('projects')->group(function () {
            Route::get('/', [ProjectController::class, 'index']);
            Route::post('/', [ProjectController::class, 'store']);
            Route::get('/search', [ProjectController::class, 'search']);
            Route::get('/overdue', [ProjectController::class, 'overdue']);
            Route::get('/statistics', [ProjectController::class, 'statistics']);
            Route::get('/company/{companyId}', [ProjectController::class, 'byCompany']);
            Route::get('/manager/{managerId}', [ProjectController::class, 'byManager']);
            
            Route::get('/{project}', [ProjectController::class, 'show']);
            Route::put('/{project}', [ProjectController::class, 'update']);
            Route::delete('/{project}', [ProjectController::class, 'destroy']);
            Route::patch('/{project}/status', [ProjectController::class, 'updateStatus']);
            Route::post('/{project}/progress', [ProjectController::class, 'updateProgress']);
        });

        // Task Management Routes
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::get('/search', [TaskController::class, 'search']);
            Route::get('/overdue', [TaskController::class, 'overdue']);
            Route::get('/due-soon', [TaskController::class, 'dueSoon']);
            Route::get('/statistics', [TaskController::class, 'statistics']);
            Route::get('/project/{projectId}', [TaskController::class, 'byProject']);
            Route::get('/assignee/{userId}', [TaskController::class, 'byAssignee']);
            Route::post('/bulk-update', [TaskController::class, 'bulkUpdate']);
            
            Route::get('/{task}', [TaskController::class, 'show']);
            Route::put('/{task}', [TaskController::class, 'update']);
            Route::delete('/{task}', [TaskController::class, 'destroy']);
            Route::patch('/{task}/status', [TaskController::class, 'updateStatus']);
            Route::patch('/{task}/progress', [TaskController::class, 'updateProgress']);
            Route::patch('/{task}/assign', [TaskController::class, 'assign']);
            Route::post('/{task}/time', [TaskController::class, 'logTime']);
            Route::get('/{task}/hierarchy', [TaskController::class, 'hierarchy']);
            Route::post('/{task}/duplicate', [TaskController::class, 'duplicate']);
            
            // Task Attachments Routes
            Route::get('/{task}/attachments', [TaskAttachmentController::class, 'index']);
            Route::post('/{task}/attachments', [TaskAttachmentController::class, 'store']);
            Route::delete('/{task}/attachments/bulk', [TaskAttachmentController::class, 'bulkDestroy']);
        });
        
        // Attachment-specific routes (not nested under tasks)
        Route::prefix('attachments')->group(function () {
            Route::get('/config', [TaskAttachmentController::class, 'config']);
            Route::get('/{attachment}', [TaskAttachmentController::class, 'show']);
            Route::get('/{attachment}/download', [TaskAttachmentController::class, 'download'])->name('api.v1.attachments.download');
            Route::get('/{attachment}/preview', [TaskAttachmentController::class, 'preview'])->name('api.v1.attachments.preview');
            Route::get('/{attachment}/thumbnail', [TaskAttachmentController::class, 'thumbnail'])->name('api.v1.attachments.thumbnail');
            Route::delete('/{attachment}', [TaskAttachmentController::class, 'destroy']);
        });
        
        // Time Tracking Routes
        Route::prefix('time-tracking')->group(function () {
            // User time logs and active tracking
            Route::get('/active', [TimeTrackingController::class, 'activeTimeLog']);
            Route::get('/my-logs', [TimeTrackingController::class, 'userTimeLogs']);
            Route::post('/clock-out', [TimeTrackingController::class, 'clockOut']);
            Route::get('/statistics', [TimeTrackingController::class, 'statistics']);
            Route::get('/activity-types', [TimeTrackingController::class, 'activityTypes']);
            
            // Individual time log operations
            Route::get('/{timeLog}', [TimeTrackingController::class, 'show']);
            Route::put('/{timeLog}', [TimeTrackingController::class, 'update']);
            Route::delete('/{timeLog}', [TimeTrackingController::class, 'destroy']);
        });
        
        // Task-specific time tracking routes
        Route::prefix('tasks/{task}/time-logs')->group(function () {
            Route::get('/', [TimeTrackingController::class, 'index']);
            Route::post('/', [TimeTrackingController::class, 'store']); // Manual time entry
            Route::post('/clock-in', [TimeTrackingController::class, 'clockIn']);
        });

        // Task Notification Routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [TaskNotificationController::class, 'index']);
            Route::get('/unread-count', [TaskNotificationController::class, 'unreadCount']);
            Route::get('/statistics', [TaskNotificationController::class, 'statistics']);
            Route::post('/mark-all-read', [TaskNotificationController::class, 'markAllAsRead']);
            Route::put('/{notification}/read', [TaskNotificationController::class, 'markAsRead']);
            Route::delete('/{notification}', [TaskNotificationController::class, 'destroy']);
        });
    });
});