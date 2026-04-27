<?php

namespace App\Modules\Auth\Application\DTOs\Staff;

use App\Modules\Auth\Domain\Entities\StaffEntity;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class StaffCreateData extends Data
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
