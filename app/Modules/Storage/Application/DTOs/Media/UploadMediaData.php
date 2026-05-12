<?php

namespace App\Modules\Storage\Application\DTOs\Media;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UploadMediaData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $model_type,
        #[Required, IntegerType]
        public readonly int $model_id,
        #[Required, StringType, Max(50)]
        public readonly string $type,
        #[Nullable, StringType, Max(255)]
        public readonly ?string $title = null,
        #[Nullable, StringType]
        public readonly ?string $description = null,
        #[Nullable, IntegerType]
        public readonly ?int $sort = null,
        #[Required] // будет заменено на UploadedFile вручную
        public mixed $file = null,
    ) {}
}
