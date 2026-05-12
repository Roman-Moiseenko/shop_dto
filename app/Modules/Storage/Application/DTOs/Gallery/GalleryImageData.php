<?php

namespace App\Modules\Storage\Application\DTOs\Gallery;

use App\Modules\Storage\Application\DTOs\Tag\MediaTagIndexData;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use Spatie\LaravelData\Data;

class GalleryImageData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly string $type,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly int $sort,
        public readonly string $fileName,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly string $url,
        public readonly ?string $thumbUrl,
        /** @var MediaTagIndexData[] */
        public readonly array $tags,
    ) {}

    public static function fromEntity(MediaEntity $media): self
    {
        return new self(
            id: $media->id,
            uuid: $media->uuid,
            type: $media->type->getValue(),
            title: $media->title,
            description: $media->description,
            sort: $media->sort,
            fileName: $media->fileName,
            mimeType: $media->mimeType,
            size: $media->size,
            url: $media->getUrl(),
            thumbUrl: $media->getUrl('thumb'), // или вызывать через сервис
            tags: MediaTagIndexData::collect($media->tags ?? []),
        );
    }
}
