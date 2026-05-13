<?php

namespace App\Modules\Content\Application\DTOs\Menu;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\IntegerType;

class ReorderMenuItemData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly int $id,
        #[Required, IntegerType]
        public readonly int $newSort,
    ) {}
}
