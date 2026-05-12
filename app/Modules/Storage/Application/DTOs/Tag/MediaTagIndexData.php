<?php

namespace App\Modules\Storage\Application\DTOs\Tag;

use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use Spatie\LaravelData\Data;

class MediaTagIndexData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
    ) {}

    public static function fromEntity(MediaTagEntity $entity): self
    {
        return new self(
            id: $entity->id,
            name: $entity->name->getValue(),
            slug: (string) $entity->slug,
        );
    }
}
