<?php

namespace App\Modules\Storage\Application\DTOs\Gallery;

use App\Modules\Storage\Domain\Entities\GalleryEntity;
use Spatie\LaravelData\Data;

class GalleryViewData extends Data
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

    public static function fromEntity(GalleryEntity $gallery): self
    {
        return new self(
            id: $gallery->id,
            name: $gallery->name->getValue(),
            slug: (string) $gallery->slug,
            description: $gallery->description,
            isActive: $gallery->isActive,
            createdAt: $gallery->createdAt?->format('c'),
            updatedAt: $gallery->updatedAt?->format('c'),
        );
    }
}
