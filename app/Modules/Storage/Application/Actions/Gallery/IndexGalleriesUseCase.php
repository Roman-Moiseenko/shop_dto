<?php

namespace App\Modules\Storage\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\GalleryRepositoryInterface;

final class IndexGalleriesUseCase
{
    public function __construct(private GalleryRepositoryInterface $galleryRepository) {}

    public function execute(UserPermission $permissions): array
    {
        if (!$permissions->can('storage.media.view')) {
            throw new AccessDeniedException();
        }
        return $this->galleryRepository->all();
    }
}
