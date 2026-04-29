<?php

namespace App\Modules\Auth\Application\DTOs\User;

use App\Modules\Auth\Domain\Entities\UserEntity;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
        #[BooleanType]
        public bool $active,
        #[Required, IntegerType]
        public int $id,
        #[Required, Email]
        public string $email,
        #[Required, ArrayType]
        public array $roleNames,
    )
    {
    }

    public static function fromEntity(UserEntity $entity): self
    {
        return new self(
            !$entity->isBanned,
            $entity->id,
            $entity->email,
            $entity->roles
        );
    }

}
