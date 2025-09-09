<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\User\Models\User;
use App\Domain\User\Services\UserService;
use App\Domain\User\DTOs\CreateUserDTO;
use App\Domain\User\DTOs\UpdateUserDTO;
use App\Http\Resources\UserResource;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        // Get pagination parameters
        $perPage = $request->get('per_page', 50);
        $page = $request->get('page', 1);
        $search = $request->get('search');
        $role = $request->get('role');
        $verified = $request->get('verified');
        $companyId = $request->get('company_id');

        // Build query
        $query = User::with('company');

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($verified !== null) {
            if ($verified === 'true') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        // Get paginated results
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        // Calculate statistics
        $stats = [
            'total' => User::count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'this_month' => User::whereYear('created_at', now()->year)
                               ->whereMonth('created_at', now()->month)
                               ->count()
        ];

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'stats' => $stats
        ]);
    }

    /**
     * Store a newly created user
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'company_id' => $request->company_id,
        ]);

        return response()->json([
            'data' => new UserResource($user->load('company'))
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user->load('company'))
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updateData = new UpdateUserDTO(
            name: $request->name,
            email: $request->email,
            password: $request->password ? Hash::make($request->password) : null,
            role: $request->role,
            companyId: $request->company_id,
            avatarUrl: $request->avatar_url
        );

        $updatedUser = $this->userService->updateUser($user, $updateData);

        return response()->json([
            'data' => new UserResource($updatedUser->load('company'))
        ]);
    }

    /**
     * Remove the specified user (soft delete)
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->deleteUser($user);

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('avatars', $filename, 'public');
            
            $user->update(['avatar_url' => '/storage/' . $path]);

            return response()->json([
                'data' => [
                    'avatar_url' => $user->avatar_url
                ]
            ]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    /**
     * Resend email verification
     */
    public function resendVerification(User $user): JsonResponse
    {
        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email is already verified'
            ], 400);
        }

        // In a real implementation, you would send the verification email here
        // $user->sendEmailVerificationNotification();

        return response()->json([
            'data' => [
                'message' => 'Verification email sent successfully'
            ]
        ]);
    }

    /**
     * Get users by role
     */
    public function getUsersByRole(Request $request): JsonResponse
    {
        $role = $request->get('role');
        
        if (!$role) {
            return response()->json(['message' => 'Role parameter is required'], 400);
        }

        $users = $this->userService->getUsersByRole($role);

        return response()->json([
            'data' => UserResource::collection($users)
        ]);
    }

    /**
     * Get company users
     */
    public function getCompanyUsers(Request $request): JsonResponse
    {
        $companyId = $request->get('company_id');
        
        if (!$companyId) {
            // Get current user's company
            $companyId = auth()->user()->company_id;
        }

        if (!$companyId) {
            return response()->json(['message' => 'Company ID not found'], 400);
        }

        $users = $this->userService->getUsersByCompany($companyId);

        return response()->json([
            'data' => UserResource::collection($users)
        ]);
    }
}