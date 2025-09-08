<?php

namespace App\Domain\User\DTOs;

use Spatie\LaravelData\Data;

class CreateUserDTO extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role = 'field_worker',
        public ?string $companyId = null,
        public ?string $avatarUrl = null
    ) {}
}