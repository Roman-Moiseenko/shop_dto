<?php

namespace App\Modules\Content\Domain\Entities;

final class HeaderSettings
{
    public function __construct(
        public readonly string $siteName,
        public readonly ?string $slogan,
        public readonly ?string $logoUuid,
        /** @var array<string, string> позиция -> slug меню */
        public readonly array $menuSlugs,
        public readonly SearchSettings $searchSettings,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            siteName: $data['site_name'] ?? '',
            slogan: $data['slogan'] ?? null,
            logoUuid: $data['logo_uuid'] ?? null,
            menuSlugs: $data['menus'] ?? [],
            searchSettings: SearchSettings::fromArray($data['search'] ?? []),
        );
    }
}

