<?php

namespace App\Modules\Auth\Application\DTOs\Client;

use App\Modules\Auth\Application\DTOs\User\UserViewData;
use App\Modules\Auth\Domain\Entities\ClientEntity;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ClientViewData extends Data
{
    public function __construct(
        #[IntegerType]
        public readonly int $id,
        public readonly string $lastName,
        public readonly string $firstName,
        public readonly ?string $middleName,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $birthDate,
        public readonly ?string $gender,
        // адрес
        public readonly ?string $country,
        public readonly ?string $region,
        public readonly ?string $city,
        public readonly ?string $street,
        public readonly ?string $postalCode,
        // бан и активность
        public readonly ?string       $bannedAt,
        public readonly bool          $isActive,
        // согласие на ПД
        public readonly bool          $consented,
        public readonly ?string       $consentedAt,
        public readonly string        $policyVersion,
        public readonly ?string       $actionIdentifier,
        public readonly bool          $consentActive,
        // связанный пользователь (учётная запись)
        public readonly ?UserViewData $user = null,
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
            user: $clientEntity->user ? UserViewData::fromEntity($clientEntity->user) : null,
        );
    }
}
