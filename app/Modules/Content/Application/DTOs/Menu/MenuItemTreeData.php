<?php

namespace App\Modules\Content\Application\DTOs\Menu;

use App\Modules\Content\Domain\Entities\MenuItemEntity;
use Spatie\LaravelData\Data;

class MenuItemTreeData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $url,
        public readonly bool $isActive,
        public readonly int $sort,
        /** @var MenuItemTreeData[] */
        public readonly array $children = [],
    ) {}

    public static function fromEntity(MenuItemEntity $item): self
    {
        return new self(
            id: $item->id,
            title: $item->title,
            url: $item->url,
            isActive: $item->isActive,
            sort: $item->sort,
            children: array_map(
                fn($child) => self::fromEntity($child),
                $item->children
            ),
        );
    }
}
