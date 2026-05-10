<?php

namespace App\Modules\Auth\Application\DTOs\Role;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\Permission\Models\Role;

class RoleViewData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $id,
        public string $name,
        public ?string $description,
        public bool $is_system,
        public array $permissions,
    ) {}

    public static function fromEntity(Role $role): self
    {
        return new self(
            $role->id,
            $role->name,
            $role->description,
            $role->is_system,
            $role->permissions->pluck('name')->toArray(),
        );
    }

    public static function from(mixed ...$payloads): static
    {
        if (count($payloads) === 1 && $payloads[0] instanceof Role) {
            return static::fromEntity($payloads[0]);
        }

        return parent::from(...$payloads);
    }
}
