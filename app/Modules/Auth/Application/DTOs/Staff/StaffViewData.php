<?php

namespace App\Modules\Auth\Application\DTOs\Staff;

use App\Modules\Auth\Application\DTOs\User\UserViewData;
use App\Modules\Auth\Domain\Entities\StaffEntity;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * DTO для возврата данных на фронтенд, без валидации
 */
class StaffViewData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $id,
        public readonly string $lastName,
        public readonly string $firstName,
        public readonly ?string $middleName = null,
        public readonly string $position,
        public readonly ?string       $department = null,
        public readonly ?string       $workPhone = null,
        public readonly ?string       $personalPhone = null,
        public readonly ?string       $workEmail = null,
        public readonly ?string       $hireDate = null,
        public readonly ?string       $birthDate = null,
        public readonly ?string       $telegramChatId = null,
        public readonly ?string       $maxChatId = null,
        public readonly ?string       $notes = null,
        public readonly bool          $terminated = false,
        public readonly ?UserViewData $user = null,
    ) {}

    public static function fromEntity(StaffEntity $staff): self
    {
        return new self(
            $staff->id,
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
            !$staff->isActive,
            $staff->user ? UserViewData::fromEntity($staff->user) : null,
        );
    }
}
