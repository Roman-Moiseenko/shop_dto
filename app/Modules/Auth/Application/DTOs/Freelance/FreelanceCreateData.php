<?php

namespace App\Modules\Auth\Application\DTOs\Freelance;

use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

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
