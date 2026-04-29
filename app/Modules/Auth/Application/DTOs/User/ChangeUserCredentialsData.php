<?php

namespace App\Modules\Auth\Application\DTOs\User;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ChangeUserCredentialsData extends Data
{
    public function __construct(
        #[Required, Email]
        public readonly string $currentEmail,   // текущий email пользователя
        #[Required, StringType]
        public readonly string $currentPassword, // для подтверждения личности
        #[Nullable, Email]
        public readonly ?string $newEmail = null,
        #[Nullable, StringType, Min(8)]
        public readonly ?string $newPassword = null,
    ) {}
}
