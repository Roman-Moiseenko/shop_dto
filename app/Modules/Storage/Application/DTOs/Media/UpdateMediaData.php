<?php

namespace App\Modules\Storage\Application\DTOs\Media;

use App\Modules\Storage\Application\DTOs\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class UpdateMediaData extends Data
{
    public function __construct(
        #[Nullable, StringType, Max(255)]
        public readonly ?string $title = null,
        #[Nullable, StringType]
        public readonly ?string $description = null,
        #[Nullable, IntegerType]
        public readonly ?int $sort = null,
    ) {}
}
