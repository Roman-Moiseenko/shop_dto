<?php

namespace App\Modules\Auth\Application\DTOs\User;

use Spatie\LaravelData\Data;

class UserProfileData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $fullName,
        public readonly ?string $position,
        public readonly array $roles,
        public readonly array $permissions,
    ) {}
}
