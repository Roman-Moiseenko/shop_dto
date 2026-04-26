<?php

namespace App\Modules\Auth\Application\DTOs;

use App\Modules\Auth\Domain\Entities\StaffEntity;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
class StaffCreateData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $lastName,
        #[Required, StringType, Max(255)]
        public readonly string $firstName,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $middleName = null,
        #[Required, StringType, Max(255)]
        public readonly string $position,
    ) {}

    public static function fromEntity(StaffEntity $staff): static
    {
        return new self(
            $staff->fullName->getLastName(),
            $staff->fullName->getFirstName(),
            $staff->fullName->getMiddleName(),
            $staff->position,
        );
    }

}
