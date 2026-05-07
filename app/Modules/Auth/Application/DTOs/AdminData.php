<?php

namespace App\Modules\Auth\Application\DTOs;

use Dflydev\DotAccessData\Data;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;

class AdminData extends Data
{
    public function __construct(
        #[Required, Email, Max(255)]
        public readonly string $email,
        #[StringType, Max(255)]
        public readonly string $password,
    ) {

    }
}
