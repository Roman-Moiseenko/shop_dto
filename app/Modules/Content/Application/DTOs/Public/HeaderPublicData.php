<?php

namespace App\Modules\Content\Application\DTOs\Public;

use Spatie\LaravelData\Data;

class HeaderPublicData extends Data
{
    public function __construct(
        public readonly string $siteName,
        public readonly ?string $slogan,
        public readonly ?string $logoUuid,
        /** @var MenuPublicData[] */
        public readonly array $menus,
        /** @var ContactPublicData[] */
        public readonly array $contacts,
        public readonly SearchData $search,
    ) {}
}
