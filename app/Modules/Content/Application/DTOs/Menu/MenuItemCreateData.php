<?php

namespace App\Modules\Content\Application\DTOs\Menu;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Max;

class MenuItemCreateData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $title,
        #[Nullable, IntegerType]
        public readonly ?int $parentId = null,
        #[Nullable, StringType, Max(2048)]
        public readonly ?string $url = null,
        #[Nullable, StringType, Max(50)]
        public readonly ?string $referenceType = null,
        #[Nullable, IntegerType]
        public readonly ?int $referenceId = null,
    ) {}
}
