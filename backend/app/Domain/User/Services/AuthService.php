<?php

namespace App\Domain\User\Services;

use App\Domain\User\DTOs\CreateUserDTO;
use App\Domain\User\Events\UserRegistered;
use App\Domain\User\Jobs\SendWelcomeEmail;
use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(CreateUserDTO $userData): array
    {
        return DB::transaction(function () use ($userData) {
            // Check if user already exists
            if ($this->userRepository->findByEmail($userData->email)) {
                throw ValidationException::withMessages([
                    'email' => ['A user with this email already exists.'],
                ]);
            }

            // Create the user
            $user = $this->userRepository->create([
                'name' => $userData->name,
                'email' => $userData->email,
                'password' => Hash::make($userData->password),
                'role' => $userData->role,
                'company_id' => $userData->companyId,
                'avatar_url' => $userData->avatarUrl,
            ]);

            // Create API token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Dispatch events and jobs
            event(new UserRegistered($user));
            SendWelcomeEmail::dispatch($user);

            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create API token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}