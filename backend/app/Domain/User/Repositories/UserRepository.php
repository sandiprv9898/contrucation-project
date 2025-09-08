<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function update(User $user, array $attributes): User
    {
        $user->update($attributes);
        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function getAll(): Collection
    {
        return User::with('company')->get();
    }

    public function getByCompany(string $companyId): Collection
    {
        return User::where('company_id', $companyId)->with('company')->get();
    }

    public function getByRole(string $role): Collection
    {
        return User::where('role', $role)->with('company')->get();
    }
}