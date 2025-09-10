<?php

namespace App\Http\Controllers\Api;

use App\Domain\Settings\Services\SettingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\GetSettingsRequest;
use App\Http\Requests\Settings\UpdateCategorySettingsRequest;
use App\Http\Requests\Settings\ImportSettingsRequest;
use App\Http\Resources\SettingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function __construct(
        private SettingService $settingService
    ) {}

    /**
     * Get all settings or by category
     */
    public function index(GetSettingsRequest $request): JsonResponse
    {
        $category = $request->validated('category');
        
        if ($category) {
            $settings = $this->settingService->getSettingsByCategory($category);
        } else {
            $settings = $this->settingService->getAllSettings();
        }
        
        // Transform flat settings into structured format grouped by category
        $structuredSettings = [];
        $defaultSettings = $this->settingService->getDefaultSettings();
        
        // Initialize with default settings
        foreach ($defaultSettings as $cat => $defaults) {
            $structuredSettings[$cat] = $defaults;
        }
        
        // Override with actual saved settings
        foreach ($settings as $setting) {
            if (!isset($structuredSettings[$setting->category])) {
                $structuredSettings[$setting->category] = [];
            }
            $structuredSettings[$setting->category][$setting->key] = $setting->getValue();
        }
        
        return response()->json([
            'data' => $structuredSettings,
            'meta' => [
                'total' => $settings->count(),
                'categories' => $settings->pluck('category')->unique()->values(),
            ]
        ]);
    }

    /**
     * Update settings by category
     */
    public function updateCategory(UpdateCategorySettingsRequest $request, string $category): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            $success = $this->settingService->updateCategorySettings(
                $category,
                $validated['settings']
            );
            
            if ($success) {
                $settings = $this->settingService->getSettingsByCategory($category);
                
                return response()->json([
                    'message' => 'Settings updated successfully',
                    'data' => SettingResource::collection($settings)
                ]);
            }
            
            return response()->json([
                'message' => 'Failed to update settings'
            ], 500);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Export settings
     */
    public function export(): JsonResponse
    {
        $settings = $this->settingService->exportSettings();
        
        return response()->json([
            'data' => $settings,
            'exported_at' => now()->toISOString(),
            'company_id' => auth()->user()->company_id
        ]);
    }

    /**
     * Import settings
     */
    public function import(ImportSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        try {
            $success = $this->settingService->importSettings($validated['settings']);
            
            if ($success) {
                return response()->json([
                    'message' => 'Settings imported successfully',
                    'imported_count' => count($validated['settings'])
                ]);
            }
            
            return response()->json([
                'message' => 'Failed to import settings'
            ], 500);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Import validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Get setting permissions by role
     */
    public function permissions(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $permissions = [
            'company' => [
                'can_read' => $user->hasRole(['admin', 'manager']),
                'can_write' => $user->hasRole(['admin']),
            ],
            'system' => [
                'can_read' => $user->hasRole(['admin', 'manager']),
                'can_write' => $user->hasRole(['admin']),
            ],
            'notifications' => [
                'can_read' => true,
                'can_write' => $user->hasRole(['admin', 'manager']),
            ],
            'security' => [
                'can_read' => $user->hasRole(['admin']),
                'can_write' => $user->hasRole(['admin']),
            ],
            'backup' => [
                'can_read' => $user->hasRole(['admin']),
                'can_write' => $user->hasRole(['admin']),
            ],
        ];
        
        return response()->json([
            'data' => $permissions,
            'user_roles' => $user->getRoleNames()
        ]);
    }

    /**
     * Get validation rules for settings
     */
    public function validations(): JsonResponse
    {
        $service = new SettingService(resolve(\App\Domain\Settings\Repositories\SettingRepositoryInterface::class));
        $rules = $service->getValidationRules();
        
        return response()->json([
            'data' => $rules
        ]);
    }

    /**
     * Reset settings category to defaults
     */
    public function resetCategoryToDefaults(string $category): JsonResponse
    {
        try {
            $success = $this->settingService->resetCategoryToDefaults($category);
            
            if ($success) {
                $settings = $this->settingService->getSettingsByCategory($category);
                
                return response()->json([
                    'message' => ucfirst($category) . ' settings reset to defaults successfully',
                    'data' => SettingResource::collection($settings)
                ]);
            }
            
            return response()->json([
                'message' => 'Failed to reset settings to defaults'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error resetting settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system health check
     */
    public function systemHealth(): JsonResponse
    {
        $health = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'timestamp' => now()->toISOString()
        ];
        
        $overall = collect($health)->except('timestamp')->every(fn($status) => $status === 'healthy');
        
        return response()->json([
            'status' => $overall ? 'healthy' : 'degraded',
            'checks' => $health
        ]);
    }

    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenance(Request $request): JsonResponse
    {
        $enabled = $request->boolean('enabled');
        
        $this->settingService->setSetting('system', 'maintenance_mode', $enabled);
        
        return response()->json([
            'message' => $enabled ? 'Maintenance mode enabled' : 'Maintenance mode disabled',
            'maintenance_mode' => $enabled
        ]);
    }

    /**
     * Upload company logo
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        
        $file = $request->file('logo');
        $path = $file->store('company/logos', 'public');
        
        $this->settingService->setSetting('company', 'logo', $path);
        
        return response()->json([
            'message' => 'Logo uploaded successfully',
            'logo_path' => $path,
            'logo_url' => asset('storage/' . $path)
        ]);
    }

    protected function checkDatabase(): string
    {
        try {
            \DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    protected function checkCache(): string
    {
        try {
            Cache::put('health_check', 'test', 10);
            $value = Cache::get('health_check');
            return $value === 'test' ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    protected function checkStorage(): string
    {
        try {
            $testPath = storage_path('app/health_check.txt');
            file_put_contents($testPath, 'test');
            $content = file_get_contents($testPath);
            unlink($testPath);
            return $content === 'test' ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    protected function checkQueue(): string
    {
        try {
            // Simple check if queue connection works
            $connection = config('queue.default');
            return $connection ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }
}