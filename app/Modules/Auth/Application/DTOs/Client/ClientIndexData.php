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

class ClientIndexData extends Data
{
    public function __construct(
        #[IntegerType]
        public readonly int $id,
        public readonly string $fullName,
        public readonly string $email,
        public readonly ?string $phone,

        public readonly ?string $gender,
        public readonly ?string $address,
        // адрес
        public readonly bool $isActive,
        public readonly bool $isConsent,
        public readonly bool $isUser,

    ) {}

    /**
     * Создаёт DTO из доменной сущности ClientEntity.
     */
    public static function fromEntity(ClientEntity $clientEntity): self
    {
        return new self(
            $clientEntity->id,
            $clientEntity->fullName->getValue(),
            (string) $clientEntity->email,
            $clientEntity->phone ? (string) $clientEntity->phone : null,
            $clientEntity->gender?->getValue(),
            $clientEntity->address?->getFullAddress() ?? '',
            $clientEntity->isActive,
            $clientEntity->dataConsent?->consented ?? false,
            !is_null($clientEntity->user),
        );
    }

    public static function from(mixed ...$payloads): static
    {
        if (count($payloads) === 1 && $payloads[0] instanceof ClientEntity) {
            return static::fromEntity($payloads[0]);
        }

        return parent::from(...$payloads);
    }
}
