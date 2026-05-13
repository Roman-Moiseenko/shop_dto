<?php

namespace App\Modules\Content\Application\DTOs\SettingSite\Footer;

use Spatie\LaravelData\Data;

class FooterSettingsData extends Data
{
    public function __construct(
        public readonly string $copyright,
        public readonly ?string $description,
        /** @var array<int, array{position: string, menuId: int, menuName: string}> */
        public readonly array $menuPositions,
    ) {}
}
