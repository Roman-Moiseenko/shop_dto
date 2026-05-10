<?php

namespace App\Modules\Content\Application\DTOs\ContentBlocks;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class AddContentBlockData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly int     $instanceId,
        #[Nullable, IntegerType]
        public readonly ?int    $sort = null,
        #[Nullable, StringType, Max(100)]
        public readonly ?string $section = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $caption = null,
    ) {}
}
