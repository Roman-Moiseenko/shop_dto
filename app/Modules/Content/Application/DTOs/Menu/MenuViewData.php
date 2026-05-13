<?php

namespace App\Modules\Content\Application\DTOs\Menu;

use App\Modules\Content\Domain\Entities\MenuEntity;
use Spatie\LaravelData\Data;

class MenuViewData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly bool $isActive,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromEntity(MenuEntity $entity): self
    {
        return new self(
            id: $entity->id,
            name: $entity->name,
            slug: (string) $entity->slug,
            description: $entity->description,
            isActive: $entity->isActive,
            createdAt: $entity->createdAt?->format('c'),
            updatedAt: $entity->updatedAt?->format('c'),
        );
    }
}
