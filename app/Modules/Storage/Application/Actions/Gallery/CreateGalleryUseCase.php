<?php

namespace App\Modules\Storage\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\Gallery\GalleryData;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use App\Modules\Storage\Domain\ValueObjects\GalleryName;

readonly class CreateGalleryUseCase
{
    public function __construct(
        private GalleryRepositoryInterface $galleryRepository
    ) {}

    public function execute(GalleryData $dto, UserPermission $permissions): GalleryEntity
    {
        if (!$permissions->can('storage.media.create')) {
            throw new AccessDeniedException();
        }

        $gallery = new GalleryEntity(
            new GalleryName($dto->name),
            new Slug($dto->slug ?: $dto->name),   // авто-slug
            $dto->description,
            $dto->isActive ?? true                 // по умолчанию активно
        );

        return $this->galleryRepository->save($gallery);
    }
}
