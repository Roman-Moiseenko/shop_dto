<?php

namespace App\Modules\Storage\Application\DTOs\Tag;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Max;

class MediaTagData extends Data
{
    public function __construct(
        #[Required, StringType, Max(50)]
        public readonly string $name,
        #[Required, StringType, Max(64)]
        public readonly string $slug,
    ) {}
}
