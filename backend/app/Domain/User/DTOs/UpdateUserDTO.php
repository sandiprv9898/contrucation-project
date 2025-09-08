<?php

namespace App\Domain\User\DTOs;

use Spatie\LaravelData\Data;

class UpdateUserDTO extends Data
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $role = null,
        public ?string $companyId = null,
        public ?string $avatarUrl = null
    ) {}
}