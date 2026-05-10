<?php

namespace App\Modules\Content\Application\DTOs\Widget;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class WidgetUpdateData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $name,
        #[Required, StringType, Max(255)]
        public readonly string $slug,
        #[Required, StringType, Max(50)]
        public readonly string $category,
        #[Required, ArrayType]
        public readonly array $schema,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $description = null,
    ) {}
}
