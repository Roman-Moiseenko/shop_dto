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
class FreelanceViewData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $id,
        public readonly string $lastName,
        public readonly string        $firstName,
        public readonly ?string       $middleName = null,
        public readonly string        $position,
        public readonly ?string       $personalPhone = null,
        public readonly ?string       $personalEmail = null,
        public readonly ?string       $hireDate = null,
        public readonly ?string       $telegramChatId = null,
        public readonly ?string       $maxChatId = null,
        public readonly ?string       $notes = null,
        public readonly bool          $terminated = false,
        public readonly ?UserViewData $user = null,
    ) {}

    public static function fromEntity(FreelanceEntity $freelanceEntity): self
    {
        return new self(
            $freelanceEntity->id,
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
            !$freelanceEntity->isActive,
            $freelanceEntity->user ? UserViewData::fromEntity($freelanceEntity->user) : null,
        );
    }
}
