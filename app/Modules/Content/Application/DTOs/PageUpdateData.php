<?php

namespace App\Modules\Content\Application\DTOs;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
class PageUpdateData extends Data
{
    public function __construct(
        #[Nullable, StringType, Max(255)]
        public readonly ?string $title = null,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $slug = null,
        #[Nullable, StringType, Max(50)]
        public readonly ?string $contentType = null,
        #[Nullable, StringType]
        public readonly ?string $content = null,
        #[Nullable, StringType, Max(50)]
        public readonly ?string $status = null,
        #[Nullable, ArrayType]
        public readonly ?array $meta = null,
        #[Nullable, StringType, Max(50)]
        public readonly ?string $template = null,
        #[Nullable, IntegerType]
        public readonly ?int $authorId = null,
    ) {}
}
