<?php

namespace App\Modules\Content\Application\DTOs\Menu;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\IntegerType;

class ChangeMenuItemParentData extends Data
{
    public function __construct(
        #[Nullable, IntegerType]
        public readonly ?int $newParentId,
    ) {}
}
