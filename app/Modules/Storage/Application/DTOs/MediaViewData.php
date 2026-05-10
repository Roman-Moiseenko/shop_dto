<?php

namespace App\Modules\Storage\Application\DTOs;

use App\Modules\Storage\Domain\Entities\MediaEntity;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class MediaViewData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $id,
        public string $uuid,
        public string $modelType,
        public int $modelId,
        public string $type,
        public ?string $title,
        public ?string $description,
        public int $sort,
        public string $fileName,
        public string $mimeType,
        public string $disk,
        public int $size,
        public ?array $customProperties,
        public ?string $createdAt,
        public ?string $updatedAt,
    )
    {
    }

    public static function fromEntity(MediaEntity $media): self
    {
        return new self(
            $media->id,
            $media->uuid,
            $media->modelType,
            $media->modelId,
            $media->type,
            $media->title,
            $media->description,
            $media->sort,
            $media->fileName,
            $media->mimeType,
            $media->disk,
            $media->size,
            $media->customProperties,
            $media->createdAt?->format('c'),
            $media->updatedAt?->format('c'),
        );
    }







}
