<?php

namespace App\Modules\Auth\Application\DTOs\User;

use App\Modules\Auth\Domain\Entities\UserEntity;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UserViewData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $id,
        public bool $active,
        public string $email,
        public array $roleNames,
        public bool $isVerified,
    )
    {
    }

    public static function fromEntity(UserEntity $entity): self
    {
        return new self(
            $entity->id,
            !$entity->isBanned,
            $entity->email,
            $entity->roles,
            $entity->isEmailVerified(),
        );
    }

}
