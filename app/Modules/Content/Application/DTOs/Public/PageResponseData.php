<?php

namespace App\Modules\Content\Application\DTOs\Public;

use Spatie\LaravelData\Data;

class PageResponseData extends Data
{
    public function __construct(
        public readonly HeaderPublicData $header,
        public readonly FooterPublicData $footer,
        public readonly PagePublicData   $page,
    ) {}
}
