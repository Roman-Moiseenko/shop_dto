<?php

namespace App\Modules\Storage\Application\DTOs\Media;

use App\Modules\Storage\Application\DTOs\Nullable;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class IndexMediaData extends Data
{
    public function __construct(
        #[Required, StringType]
        public readonly string $model_type,
        #[Required, IntegerType]
        public readonly int $model_id,
        #[Nullable, StringType]
        public readonly ?string $type = null,
    ) {}
}
