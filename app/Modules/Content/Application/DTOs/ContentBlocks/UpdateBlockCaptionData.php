<?php

namespace App\Modules\Content\Application\DTOs\ContentBlocks;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UpdateBlockCaptionData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public readonly string $id,
        #[Required, StringType, Max(255)]
        public readonly string $caption,
    ) {}
}
