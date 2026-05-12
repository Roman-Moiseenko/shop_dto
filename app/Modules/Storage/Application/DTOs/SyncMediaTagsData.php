<?php

namespace App\Modules\Storage\Application\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\ArrayType;

class SyncMediaTagsData extends Data
{
    public function __construct(
        #[Required, ArrayType]
        public readonly array $tagIds,    // массив ID тегов, которые должны остаться
    ) {}
}
