<?php

namespace App\Modules\Content\Application\DTOs\Public;

use App\Modules\Content\Application\DTOs\Menu\MenuData;
use App\Modules\Content\Application\DTOs\SettingSite\ContactData;
use Spatie\LaravelData\Data;

class FooterData extends Data
{
    public function __construct(
        public readonly string $copyright,
        public readonly ?string $description,
        /** @var MenuFullData[] */
        public readonly array $menus,
        /** @var ContactData[] */
        public readonly array $contacts,
    ) {}
}
