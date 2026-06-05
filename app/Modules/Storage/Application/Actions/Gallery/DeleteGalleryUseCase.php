<?php

namespace App\Modules\Storage\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;
use InvalidArgumentException;

final readonly class DeleteGalleryUseCase
{
    public function __construct(private GalleryRepositoryInterface $galleryRepository) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('storage.media.delete')) {
            throw new AccessDeniedException();
        }

        $gallery = $this->galleryRepository->findById($id);
        if (!$gallery) {
            throw new InvalidArgumentException('Галерея не найдена');
        }

        $this->galleryRepository->delete($id);
    }
}
