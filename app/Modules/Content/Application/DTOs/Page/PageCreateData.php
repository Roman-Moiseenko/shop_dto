<?php

namespace App\Modules\Content\Application\DTOs\Page;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class PageCreateData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $title,
        #[Required, StringType, Max(255)]
        public readonly string $slug,
        #[Nullable, StringType, Max(50)]
        public readonly ?string $contentType = 'simple',
        #[Nullable, StringType]
        public readonly ?string $content = null,
        #[Nullable, StringType, Max(50)]
        public readonly ?string $status = 'draft',
        #[Nullable, ArrayType]
        public readonly ?array $meta = null,
        #[Nullable, StringType, Max(50)]
        public readonly ?string $template = null,
        #[Nullable, IntegerType]
        public readonly ?int $authorId = null,
    ) {}
}
