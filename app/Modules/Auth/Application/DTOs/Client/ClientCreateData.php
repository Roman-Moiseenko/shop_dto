<?php

namespace App\Modules\Auth\Application\DTOs\Client;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ClientCreateData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $lastName,
        #[Required, StringType, Max(255)]
        public readonly string $firstName,
        #[Required, Email]
        public readonly string $email,
        #[Required, StringType, Max(255)]
        public string $policyVersion,
        //Необязательные поля
        #[Nullable, StringType, Max(255)]
        public readonly ?string $middleName = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $phone = null,
        #[Nullable, StringType, Max(255)]
        public ?string $actionIdentifier = null,
    ) {}
}
