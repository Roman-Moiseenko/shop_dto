<?php

namespace App\Modules\Storage\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\DTOs\Gallery\GalleryData;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use App\Modules\Storage\Domain\ValueObjects\GalleryName;
use InvalidArgumentException;

class UpdateGalleryUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $galleryRepository
    ) {}

    public function execute(int $id, GalleryData $dto, UserPermission $permissions): GalleryEntity
    {
        if (!$permissions->can('storage.media.edit')) {
            throw new AccessDeniedException();
        }

        $gallery = $this->galleryRepository->findById($id);
        if (!$gallery) {
            throw new InvalidArgumentException('Галерея не найдена');
        }

        $gallery->name = new GalleryName($dto->name);
        // Если slug не передан или пуст, генерируем из нового имени
        $gallery->slug = new Slug($dto->slug ?: $dto->name);
        if ($dto->description !== null) {
            $gallery->description = $dto->description;
        }
        if ($dto->isActive !== null) {
            $dto->isActive ? $gallery->activate() : $gallery->deactivate();
        }

        return $this->galleryRepository->save($gallery);
    }
}
