<?php

namespace App\Modules\Auth\Application\DTOs\Staff;

use App\Modules\Auth\Application\DTOs\User\UserViewData;
use App\Modules\Auth\Domain\Entities\StaffEntity;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class StaffIndexData extends Data
{

    public function __construct(
        #[Required, IntegerType]
        public int $id,
        public readonly string $fullName,
        public readonly string $position,
        public readonly ?string $department = null,
        public readonly ?string $workPhone = null,
        public readonly ?string $workEmail = null,
        public readonly bool $isActive = false,
        public readonly bool $isUser = false,
    ) {}

    public static function fromEntity(StaffEntity $staff): self
    {
        return new self(
            $staff->id,
            $staff->fullName->getValue(),
            $staff->position,
            $staff->department,
            $staff->workPhone,
            $staff->workEmail,
            $staff->isActive,
            !is_null($staff->user),
        );
    }
    // Переопределение для поддержки Entity и автоматической коллекции mixed ...$payloads
    /**
     * Перехватываем вызов Data::from() для автоматического маппинга сущности.
     *
     * @param mixed ...$payloads
     * @return static
     */
    public static function from(mixed ...$payloads): static
    {
        if (count($payloads) === 1 && $payloads[0] instanceof StaffEntity) {
            return static::fromEntity($payloads[0]);
        }

        return parent::from(...$payloads);
    }
}
