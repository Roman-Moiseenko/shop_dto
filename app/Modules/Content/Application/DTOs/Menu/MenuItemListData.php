<?php

namespace App\Modules\Content\Application\DTOs\Menu;

use App\Modules\Content\Domain\Entities\MenuItemEntity;
use Spatie\LaravelData\Data;

class MenuItemListData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $url,
        public readonly bool $isActive,
        public readonly int $sort,
    ) {}

    public static function fromEntity(MenuItemEntity $item): self
    {
        return new self(
            id: $item->id,
            title: $item->title,
            url: $item->url,
            isActive: $item->isActive,
            sort: $item->sort,
        );
    }
}
