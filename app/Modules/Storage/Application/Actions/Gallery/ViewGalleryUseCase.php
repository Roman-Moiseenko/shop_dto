<?php

namespace App\Modules\Storage\Application\Actions\Gallery;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use App\Modules\Storage\Domain\Entities\GalleryEntity;
use InvalidArgumentException;

final class ViewGalleryUseCase
{
    public function __construct(private GalleryRepositoryInterface $galleryRepository) {}

    public function execute(int $id, UserPermission $permissions): GalleryEntity
    {
        if (!$permissions->can('storage.media.view')) {
            throw new AccessDeniedException();
        }
        $gallery = $this->galleryRepository->findById($id);
        if (!$gallery) {
            throw new InvalidArgumentException('Галерея не найдена');
        }
        return $gallery;
    }
}
