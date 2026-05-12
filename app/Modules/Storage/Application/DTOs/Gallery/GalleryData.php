<?php

namespace App\Modules\Storage\Application\DTOs\Gallery;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\BooleanType;

class GalleryData extends Data
{
    public function __construct(
        #[Required, StringType, Max(100)]
        public readonly string $name,
        #[Nullable, StringType, Max(128)]
        public readonly ?string $slug = null,
        #[Nullable, StringType]
        public readonly ?string $description = null,
        #[Nullable, BooleanType]
        public readonly ?bool $isActive = true,
    ) {}
}
