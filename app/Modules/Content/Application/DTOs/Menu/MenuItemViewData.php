<?php

namespace App\Modules\Content\Application\DTOs\Menu;

use App\Modules\Content\Domain\Entities\MenuItemEntity;
use Spatie\LaravelData\Data;

class MenuItemViewData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly int $menuId,
        public readonly ?int $parentId,
        public readonly string $title,
        public readonly ?string $url,
        public readonly ?string $referenceType,
        public readonly ?int $referenceId,
        public readonly ?string $iconUuid,
        public readonly ?string $style,
        public readonly bool $targetBlank,
        public readonly int $sort,
        public readonly bool $isActive,
        public readonly ?int $widgetInstanceId,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromEntity(MenuItemEntity $item): self
    {
        return new self(
            id: $item->id,
            menuId: $item->menuId,
            parentId: $item->parentId,
            title: $item->title,
            url: $item->url,
            referenceType: $item->referenceType?->getValue(),
            referenceId: $item->referenceId,
            iconUuid: $item->iconUuid,
            style: $item->style?->getValue(),
            targetBlank: $item->targetBlank,
            sort: $item->sort,
            isActive: $item->isActive,
            widgetInstanceId: $item->widgetInstanceId,
            createdAt: $item->createdAt?->format('c'),
            updatedAt: $item->updatedAt?->format('c'),
        );
    }
}
