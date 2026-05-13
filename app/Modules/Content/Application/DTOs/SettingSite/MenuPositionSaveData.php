<?php

namespace App\Modules\Content\Application\DTOs\SettingSite;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class MenuPositionSaveData extends Data
{
    public function __construct(
        #[Required, StringType]
        public readonly string $position,
        #[Required, IntegerType]
        public readonly int $menuId,
    ) {}
}
