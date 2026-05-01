<?php

namespace App\Modules\Auth\Application\DTOs\Client;

use App\Modules\Auth\Application\DTOs\User\UserData;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ClientUserData extends Data
{
    public function __construct(
        #[IntegerType]
        public readonly int $id,
        #[StringType]
        public readonly string $lastName,
        #[StringType]
        public readonly string $firstName,
        #[Nullable, StringType]
        public readonly ?string $middleName,
        #[Email]
        public readonly string $email,
        #[Nullable, StringType]
        public readonly ?string $phone,
        #[Nullable, StringType]
        public readonly ?string $birthDate,
        #[Nullable, StringType]
        public readonly ?string $gender,
        // адрес
        #[Nullable, StringType]
        public readonly ?string $country,
        #[Nullable, StringType]
        public readonly ?string $region,
        #[Nullable, StringType]
        public readonly ?string $city,
        #[Nullable, StringType]
        public readonly ?string $street,
        #[Nullable, StringType]
        public readonly ?string $postalCode,
        // бан и активность
        #[Nullable, StringType]
        public readonly ?string $bannedAt,
        #[BooleanType]
        public readonly bool $isActive,
        // согласие на ПД
        #[BooleanType]
        public readonly bool $consented,
        #[Nullable, StringType]
        public readonly ?string $consentedAt,
        #[StringType]
        public readonly string $policyVersion,
        #[Nullable, StringType]
        public readonly ?string $actionIdentifier,
        #[BooleanType]
        public readonly bool $consentActive,
        // связанный пользователь (учётная запись)
        #[Nullable]
        public readonly ?UserData $user = null,
    ) {}

    /**
     * Создаёт DTO из доменной сущности ClientEntity.
     */
    public static function fromEntity(ClientEntity $clientEntity): self
    {
        $fullName = $clientEntity->fullName;
        $address = $clientEntity->address;
        $consent = $clientEntity->dataConsent;
        return new self(
            id: $clientEntity->id,
            lastName: $fullName->getLastName(),
            firstName: $fullName->getFirstName(),
            middleName: $fullName->getMiddleName(),
            email: (string) $clientEntity->email,
            phone: $clientEntity->phone ? (string) $clientEntity->phone : null,
            birthDate: $clientEntity->birthDate?->format('Y-m-d'),
            gender: $clientEntity->gender?->getValue(),
            country: $address?->country,
            region: $address?->region,
            city: $address?->city,
            street: $address?->street,
            postalCode: $address?->postalCode,
            bannedAt: $clientEntity->bannedAt?->format('c'),
            isActive: $clientEntity->isActive,
            consented: $consent?->consented ?? false,
            consentedAt: $consent?->consentedAt->format('c'),
            policyVersion: $consent?->policyVersion ?? '',
            actionIdentifier: $consent?->actionIdentifier ?? '',
            consentActive: $consent?->active ?? false,
            user: $clientEntity->user ? UserData::fromEntity($clientEntity->user) : null,
        );
    }
}
