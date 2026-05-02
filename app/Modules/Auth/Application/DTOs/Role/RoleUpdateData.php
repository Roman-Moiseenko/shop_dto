<?php

namespace App\Modules\Auth\Application\DTOs\Role;

use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

class RoleUpdateData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255), Unique('roles', 'name', ignore: new RouteParameterReference('role'))]
        public readonly string $name,
        #[Required, ArrayType]
        public readonly array $permissions,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $description = null,
    ) {}
}
