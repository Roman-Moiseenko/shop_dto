<?php

namespace App\Modules\Content\Application\DTOs;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ReorderSingleBlockData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly string $id,
        #[Required, IntegerType]
        public readonly string $sort,
    ) {}
}
