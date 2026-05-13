<?php

namespace App\Modules\Content\Application\DTOs\SettingSite\Header;

use Spatie\LaravelData\Data;

class HeaderSettingsData extends Data
{
    public function __construct(
        public readonly string $siteName,
        public readonly ?string $slogan,
        public readonly ?string $logoUuid,
        /** @var array<int, array{position: string, menuId: int, menuName: string}> */
        public readonly array $menuPositions,
        public readonly bool $searchEnabled,
        public readonly ?string $searchPlaceholder,
        public readonly ?string $searchActionUrl,
    ) {}
}
