<?php

namespace App\Modules\Content\Application\DTOs\Menu;

use App\Modules\Content\Domain\Entities\MenuEntity;
use Spatie\LaravelData\Data;

class MenuIndexData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly bool $isActive,
    ) {}

    public static function fromEntity(MenuEntity $entity): self
    {
        return new self(
            id: $entity->id,
            name: $entity->name,
            slug: (string) $entity->slug,
            isActive: $entity->isActive,
        );
    }
}
