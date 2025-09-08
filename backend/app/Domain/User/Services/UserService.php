<?php

namespace App\Domain\User\Services;

use App\Domain\User\DTOs\UpdateUserDTO;
use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getAllUsers(): Collection
    {
        return $this->userRepository->getAll();
    }

    public function getUserById(string $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function updateUser(User $user, UpdateUserDTO $updateData): User
    {
        $attributes = [];

        if ($updateData->name !== null) {
            $attributes['name'] = $updateData->name;
        }

        if ($updateData->email !== null) {
            $attributes['email'] = $updateData->email;
        }

        if ($updateData->password !== null) {
            $attributes['password'] = Hash::make($updateData->password);
        }

        if ($updateData->role !== null) {
            $attributes['role'] = $updateData->role;
        }

        if ($updateData->companyId !== null) {
            $attributes['company_id'] = $updateData->companyId;
        }

        if ($updateData->avatarUrl !== null) {
            $attributes['avatar_url'] = $updateData->avatarUrl;
        }

        return $this->userRepository->update($user, $attributes);
    }

    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user);
    }

    public function getUsersByCompany(string $companyId): Collection
    {
        return $this->userRepository->getByCompany($companyId);
    }

    public function getUsersByRole(string $role): Collection
    {
        return $this->userRepository->getByRole($role);
    }
}