<?php

namespace App\Modules\Content\Application\DTOs\Public;

use App\Modules\Content\Application\DTOs\Contact\ContactViewData;
use Spatie\LaravelData\Data;

class FooterData extends Data
{
    public function __construct(
        public readonly string $copyright,
        public readonly ?string $description,
        /** @var MenuFullData[] */
        public readonly array $menus,
        /** @var ContactPublicData[] */
        public readonly array $contacts,
    ) {}
}
