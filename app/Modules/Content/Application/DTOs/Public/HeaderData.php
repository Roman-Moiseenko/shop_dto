<?php

namespace App\Modules\Content\Application\DTOs\Public;

use App\Modules\Content\Application\DTOs\Menu\MenuData;
use App\Modules\Content\Application\DTOs\SettingSite\ContactData;
use Spatie\LaravelData\Data;

class HeaderData extends Data
{
    public function __construct(
        public readonly string $siteName,
        public readonly ?string $slogan,
        public readonly ?string $logoUuid,
        /** @var MenuFullData[] */
        public readonly array $menus,
        /** @var ContactData[] */
        public readonly array $contacts,
        public readonly SearchData $search,
    ) {}
}
