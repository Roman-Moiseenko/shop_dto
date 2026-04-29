<?php

namespace App\Modules\Auth\Application\DTOs\Client;

use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ClientUpdateData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $lastName,
        #[Required, StringType, Max(255)]
        public readonly string $firstName,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $middleName = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $phone = null,
        #[Nullable, Email, Max(255)]
        public readonly ?string $email = null,  // контактный email клиента
        #[Nullable, Date]
        public readonly ?string $birthDate = null,
        #[Nullable, StringType, Max(10)]
        public readonly ?string $gender = null,
        // Адрес
        #[Nullable, StringType, Max(255)]
        public readonly ?string $country = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $region = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $city = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $street = null,
        #[Nullable, StringType, Max(20)]
        public readonly ?string $postalCode = null,
    ) {}
}
