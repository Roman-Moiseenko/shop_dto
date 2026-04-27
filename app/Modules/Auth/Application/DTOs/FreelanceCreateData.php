<?php

namespace App\Modules\Auth\Application\DTOs;

use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;

class FreelanceCreateData extends Data
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

    ) {}

    public static function fromEntity(FreelanceEntity $freelanceEntity): static
    {
        return new self(
            $freelanceEntity->fullName->getLastName(),
            $freelanceEntity->fullName->getFirstName(),
            $freelanceEntity->fullName->getMiddleName(),
            $freelanceEntity->position,
        );
    }

}
