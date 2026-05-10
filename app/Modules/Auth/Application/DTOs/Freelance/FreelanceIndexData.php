<?php

namespace App\Modules\Auth\Application\DTOs\Freelance;

use App\Modules\Auth\Application\DTOs\User\UserViewData;
use App\Modules\Auth\Domain\Entities\FreelanceEntity;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

/**
 * DTO для возврата данных на фронтенд
 */
class FreelanceIndexData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $id,
        public readonly string $fullName,
        public readonly string $position,
        public readonly ?string $personalPhone = null,
        public readonly ?string $personalEmail = null,
        public readonly bool $isActive = false,
        public readonly bool $isUser = false,
    ) {}

    public static function fromEntity(FreelanceEntity $freelanceEntity): self
    {
        return new self(
            $freelanceEntity->id,
            $freelanceEntity->fullName->getValue(),
            $freelanceEntity->position,
            $freelanceEntity->personalPhone,
            $freelanceEntity->personalEmail,
            $freelanceEntity->isActive,
            !is_null($freelanceEntity->user),
        );
    }

    public static function from(mixed ...$payloads): static
    {
        if (count($payloads) === 1 && $payloads[0] instanceof FreelanceEntity) {
            return static::fromEntity($payloads[0]);
        }

        return parent::from(...$payloads);
    }
}
