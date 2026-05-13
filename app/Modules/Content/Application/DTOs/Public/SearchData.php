<?php

namespace App\Modules\Content\Application\DTOs\Public;

use Spatie\LaravelData\Data;

class SearchData extends Data
{
    public function __construct(
        public readonly bool $enabled,
        public readonly string $placeholder,
        public readonly string $actionUrl,
    ) {}
}
