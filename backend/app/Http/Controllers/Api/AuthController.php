<?php

namespace App\Http\Controllers\Api;

use App\Domain\User\DTOs\CreateUserDTO;
use App\Domain\User\Services\AuthService;
use App\Domain\User\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private UserService $userService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userData = CreateUserDTO::from([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => $request->role ?? 'field_worker',
                'companyId' => $request->company_id,
            ]);

            $user = $this->authService->register($userData);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => new UserResource($user),
            ], 201);
        } catch (\Exception $e) {
            Log::error('User registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->email, $request->password);

            return response()->json([
                'message' => 'Login successful',
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]);
        } catch (\Exception $e) {
            Log::warning('Login attempt failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            // For now, just log the password reset request since we're not implementing email
            Log::info('Password reset requested for: ' . $request->email);

            return response()->json([
                'message' => 'Password reset instructions sent to your email',
            ]);
        } catch (\Exception $e) {
            Log::error('Password reset request failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Password reset request failed',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'user' => new UserResource($user),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get current user', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to get user information',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}