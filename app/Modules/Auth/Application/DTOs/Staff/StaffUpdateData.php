<?php

namespace App\Modules\Auth\Application\DTOs\Staff;

use App\Modules\Auth\Domain\Entities\StaffEntity;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class StaffUpdateData extends Data
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
        public readonly ?string $department = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $workPhone = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $personalPhone = null,
        #[Nullable, Email, Max(255)]
        public readonly ?string $workEmail = null,
        #[Nullable, Date]
        public readonly ?string $hireDate = null,
        #[Nullable, Date]
        public readonly ?string $birthDate = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $telegramChatId = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $maxChatId = null,
        #[Nullable, StringType]
        public readonly ?string $notes = null,
        #[BooleanType]
        public readonly bool $terminated = false,
    ) {}

    public static function fromEntity(StaffEntity $staff): static
    {
        return new self(
            $staff->fullName->getLastName(),
            $staff->fullName->getFirstName(),
            $staff->fullName->getMiddleName(),
            $staff->position,
            $staff->department,
            $staff->workPhone,
            $staff->personalPhone,
            $staff->workEmail,
            $staff->hireDate,
            $staff->birthDate,
            $staff->telegramChatId,
            $staff->maxChatId,
            $staff->notes,
            !$staff->isActive
        );
    }

}
