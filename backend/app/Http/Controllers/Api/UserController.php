<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\FilterableTrait;
use App\Http\Services\StatisticsService;
use App\Domain\User\Models\User;
use App\Domain\User\Services\UserService;
use App\Domain\User\DTOs\CreateUserDTO;
use App\Domain\User\DTOs\UpdateUserDTO;
use App\Domain\User\Filters\UserFilter;
use App\Http\Resources\UserResource;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use ApiResponseTrait, FilterableTrait;

    public function __construct(
        private UserService $userService,
        private StatisticsService $statisticsService
    ) {}

    /**
     * Display a listing of users with advanced filtering and statistics
     */
    public function index(Request $request): JsonResponse
    {
        $userFilter = new UserFilter($request);
        
        // Validate filter parameters
        $validationErrors = $this->validateFilterParameters($request, $userFilter);
        if (!empty($validationErrors)) {
            return $this->validationErrorResponse($validationErrors);
        }

        // Build base query
        $query = User::with(['company']);
        
        // Get filtered and paginated results
        $users = $this->getFilteredResults(
            $query, 
            $userFilter, 
            $request, 
            true, // paginate
            $request->boolean('use_cache', false) // cache
        );

        // Get statistics
        $stats = $this->getUserStatistics($query, $userFilter, $request);
        
        // Get filter metadata
        $filterMeta = [
            'applied_filters' => $userFilter->getAppliedFilters(),
            'available_filters' => array_keys($userFilter->getFilterConfig())
        ];

        return $this->paginatedResponse(
            $users,
            UserResource::class,
            array_merge($stats, $filterMeta),
            'Users retrieved successfully'
        );
    }

    /**
     * Store a newly created user
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $createDTO = new CreateUserDTO(
                name: $request->name,
                email: $request->email,
                password: $request->password,
                role: $request->role,
                companyId: $request->company_id,
                phone: $request->phone,
                department: $request->department
            );

            $user = $this->userService->createUser($createDTO);
            
            // Clear relevant caches
            $this->clearUserCaches();

            return $this->resourceResponse(
                $user->load('company'),
                UserResource::class,
                [],
                'User created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to create user: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        $cacheKey = "user_profile_{$user->id}";
        
        $userData = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            return $user->load(['company']);
        });

        return $this->resourceResponse(
            $userData,
            UserResource::class,
            [],
            'User retrieved successfully'
        );
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $updateData = new UpdateUserDTO(
                name: $request->name,
                email: $request->email,
                password: $request->password ? Hash::make($request->password) : null,
                role: $request->role,
                companyId: $request->company_id,
                phone: $request->phone,
                department: $request->department,
                bio: $request->bio,
                avatarUrl: $request->avatar_url
            );

            $updatedUser = $this->userService->updateUser($user, $updateData);
            
            // Clear user-specific caches
            Cache::forget("user_profile_{$user->id}");
            $this->clearUserCaches();

            return $this->resourceResponse(
                $updatedUser->load('company'),
                UserResource::class,
                [],
                'User updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to update user: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified user (soft delete)
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->deleteUser($user);
            
            // Clear user-specific caches
            Cache::forget("user_profile_{$user->id}");
            $this->clearUserCaches();

            return $this->deletedResponse('User deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to delete user: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        try {
            if (!$request->hasFile('avatar')) {
                return $this->errorResponse('No file uploaded', 400);
            }

            $file = $request->file('avatar');
            $filename = uniqid() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');
            
            // Delete old avatar if exists
            if ($user->avatar_url) {
                $oldPath = str_replace('/storage/', '', $user->avatar_url);
                Storage::disk('public')->delete($oldPath);
            }
            
            $user->update(['avatar_url' => '/storage/' . $path]);
            
            // Clear user cache
            Cache::forget("user_profile_{$user->id}");

            return $this->successResponse([
                'avatar_url' => $user->avatar_url
            ], 'Avatar uploaded successfully');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to upload avatar: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Resend email verification
     */
    public function resendVerification(User $user): JsonResponse
    {
        if ($user->email_verified_at) {
            return $this->errorResponse('Email is already verified', 400);
        }

        try {
            // Check rate limiting
            $rateLimitKey = "verification_email_{$user->id}";
            if (Cache::has($rateLimitKey)) {
                return $this->errorResponse(
                    'Verification email was sent recently. Please wait before requesting again.',
                    429
                );
            }

            // In a real implementation, you would send the verification email here
            // $user->sendEmailVerificationNotification();
            
            // Set rate limit (5 minutes)
            Cache::put($rateLimitKey, true, now()->addMinutes(5));

            return $this->successResponse(
                ['message' => 'Verification email sent successfully'],
                'Verification email sent'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to send verification email: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get users by role with caching
     */
    public function getUsersByRole(Request $request): JsonResponse
    {
        $role = $request->get('role');
        
        if (!$role) {
            return $this->errorResponse('Role parameter is required', 400);
        }

        // Validate role
        $validRoles = ['admin', 'project_manager', 'supervisor', 'field_worker'];
        if (!in_array($role, $validRoles)) {
            return $this->errorResponse('Invalid role specified', 400);
        }

        $cacheKey = "users_by_role_{$role}";
        
        $users = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($role) {
            return $this->userService->getUsersByRole($role);
        });

        return $this->collectionResponse(
            $users,
            UserResource::class,
            ['role' => $role, 'count' => $users->count()],
            'Users retrieved successfully'
        );
    }

    /**
     * Get company users with caching
     */
    public function getCompanyUsers(Request $request): JsonResponse
    {
        $companyId = $request->get('company_id');
        
        if (!$companyId) {
            $companyId = auth()->user()?->company_id;
        }

        if (!$companyId) {
            return $this->errorResponse('Company ID not found', 400);
        }

        $cacheKey = "company_users_{$companyId}";
        
        $users = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($companyId) {
            return $this->userService->getUsersByCompany($companyId);
        });

        return $this->collectionResponse(
            $users,
            UserResource::class,
            ['company_id' => $companyId, 'count' => $users->count()],
            'Company users retrieved successfully'
        );
    }

    /**
     * Get user statistics with caching
     */
    protected function getUserStatistics($query, UserFilter $filter, Request $request): array
    {
        $cacheKey = 'user_stats_' . md5(serialize($filter->getAppliedFilters()));
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($query) {
            $baseQuery = clone $query;
            
            // Basic statistics
            $basicStats = $this->statisticsService->getBasicStats($baseQuery, [
                'verified' => ['email_verified_at' => 'NOT_NULL'],
                'unverified' => ['email_verified_at' => null],
                'admins' => ['role' => 'admin'],
                'active' => ['deleted_at' => null]
            ]);

            // Time-based statistics
            $timeStats = $this->statisticsService->getTimeBasedStats(
                clone $baseQuery, 
                'created_at', 
                ['today', 'this_week', 'this_month', 'last_30_days']
            );

            // Role distribution
            $roleDistribution = $this->statisticsService->getDistributionStats(
                clone $baseQuery, 
                'role', 
                10
            );

            return [
                'stats' => array_merge($basicStats, $timeStats),
                'role_distribution' => $roleDistribution
            ];
        });
    }

    /**
     * Clear user-related caches
     */
    protected function clearUserCaches(): void
    {
        // Clear user statistics cache
        Cache::forget('user_stats_*');
        
        // Clear role-based caches
        $roles = ['admin', 'project_manager', 'supervisor', 'field_worker'];
        foreach ($roles as $role) {
            Cache::forget("users_by_role_{$role}");
        }
        
        // Clear company user caches
        $this->statisticsService->clearCache('User');
    }

    /**
     * Export users
     */
    public function export(Request $request): JsonResponse
    {
        $userFilter = new UserFilter($request);
        $query = User::with(['company']);
        
        $exportQuery = $this->buildExportQuery($query, $userFilter, $request);
        $format = $request->get('format', 'csv');
        
        $users = $exportQuery->get();
        
        return $this->successResponse([
            'total_exported' => $users->count(),
            'format' => $format,
            'applied_filters' => $userFilter->getAppliedFilters()
        ], 'Export prepared successfully');
    }

    /**
     * Get user statistics endpoint
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $userFilter = new UserFilter($request);
        $query = User::query();
        
        $stats = $this->getUserStatistics($query, $userFilter, $request);
        
        return $this->successResponse($stats, 'Statistics retrieved successfully');
    }

    /**
     * Bulk operations on users
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:delete,verify',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ]);

        $action = $request->get('action');
        $userIds = $request->get('user_ids');
        
        try {
            switch ($action) {
                case 'delete':
                    User::whereIn('id', $userIds)->delete();
                    $message = 'Users deleted successfully';
                    break;
                case 'verify':
                    User::whereIn('id', $userIds)->update(['email_verified_at' => now()]);
                    $message = 'Users verified successfully';
                    break;
                default:
                    return $this->errorResponse('Invalid action', 400);
            }
            
            // Clear caches
            $this->clearUserCaches();
            
            return $this->successResponse([
                'affected_count' => count($userIds),
                'action' => $action
            ], $message);
            
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Bulk operation failed: ' . $e->getMessage(),
                500
            );
        }
    }
}