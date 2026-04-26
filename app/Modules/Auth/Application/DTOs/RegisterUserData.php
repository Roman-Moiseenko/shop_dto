<?php

namespace App\Modules\Auth\Application\DTOs;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class RegisterUserData extends Data
{
    public function __construct(
        #[Required, Email, Max(255)]
        public readonly string $email,
        #[StringType, Max(255)]
        public readonly string $password,
        #[ArrayType]
        public readonly array $roleNames,
        #[StringType, Max(255)]
        public string $profileableType,
        #[IntegerType, Max(255)]
        public int $profileableId,

    ) {}
}
