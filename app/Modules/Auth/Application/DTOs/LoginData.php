<?php

namespace App\Modules\Auth\Application\DTOs;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class LoginData extends Data
{
    public function __construct(
        #[Required, Email]
        public readonly string $email,
        #[Required, Password(min: 8)]
        public readonly string $password,
        #[Nullable, BooleanType]
        public readonly bool $remember = false
    ) {}
}
