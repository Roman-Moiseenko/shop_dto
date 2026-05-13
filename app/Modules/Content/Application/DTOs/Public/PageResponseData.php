<?php

namespace App\Modules\Content\Application\DTOs\Public;

use Spatie\LaravelData\Data;

class PageResponseData extends Data
{
    public function __construct(
        public readonly HeaderData $header,
        public readonly FooterData $footer,
        public readonly PagePublicData $page,
    ) {}
}
