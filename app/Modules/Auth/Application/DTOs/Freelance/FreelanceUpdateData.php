<?php

namespace App\Modules\Auth\Application\DTOs\Freelance;

use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class FreelanceUpdateData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $lastName,
        #[Required, StringType, Max(255)]
        public readonly string $firstName,
        #[Required, StringType, Max(255)]
        public readonly string $position,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $middleName = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $personalPhone = null,
        #[Nullable, Email, Max(255)]
        public readonly ?string $personalEmail = null,
        #[Nullable, Date]
        public readonly ?string $hireDate = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $telegramChatId = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $maxChatId = null,
        #[Nullable, StringType]
        public readonly ?string $notes = null,
        #[BooleanType]
        public readonly bool $terminated = false,
    ) {}

    public static function fromEntity(FreelanceEntity $freelanceEntity): static
    {
        return new self(
            $freelanceEntity->fullName->getLastName(),
            $freelanceEntity->fullName->getFirstName(),
            $freelanceEntity->fullName->getMiddleName(),
            $freelanceEntity->position,
            $freelanceEntity->personalPhone,
            $freelanceEntity->personalEmail,
            $freelanceEntity->hireDate,
            $freelanceEntity->telegramChatId,
            $freelanceEntity->maxChatId,
            $freelanceEntity->notes,
            !$freelanceEntity->isActive
        );
    }

}
