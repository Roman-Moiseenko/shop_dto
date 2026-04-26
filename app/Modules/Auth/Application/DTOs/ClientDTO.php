<?php

namespace App\Modules\Auth\Application\DTOs;

use Spatie\LaravelData\Data;

class ClientDTO extends Data
{
    public function __construct(
        public readonly string $lastName,
        public readonly string $firstName,
        public readonly ?string $middleName = null,
        public readonly string $phone,
        public readonly ?string $email = null,
        public readonly ?string $birthDate = null,
        public readonly ?string $gender = null,
        // Address
        public readonly ?string $country = null,
        public readonly ?string $city = null,
        public readonly ?string $street = null,
        public readonly ?string $region = null,
        public readonly ?string $postalCode = null,
        // Other
        public readonly bool $agreeToNewsletter = false,
        public readonly string $preferredLanguage = 'ru',
        public readonly ?string $externalId = null,
        // User fields
        public readonly string $name,
        public readonly string $userEmail,
        public readonly string $password,
        public readonly ?array $roleNames = ['client']
    ) {}
}
