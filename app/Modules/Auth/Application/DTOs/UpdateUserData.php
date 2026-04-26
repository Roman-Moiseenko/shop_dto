<?php

namespace App\Modules\Auth\Application\DTOs;

use App\Modules\Auth\Domain\Entities\UserEntity;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UpdateUserData extends Data
{
    public function __construct(
        #[BooleanType]
        public bool $active,
        #[Required, Email]
        public string  $email,
        #[Required, StringType] // пароль обязателен только при создании
        public string $password,
        #[ArrayType]
        public array $roleNames,
    )
    {
    }

}
