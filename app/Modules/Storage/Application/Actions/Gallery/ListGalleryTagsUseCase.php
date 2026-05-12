<?php

namespace App\Modules\Storage\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;

final class ListGalleryTagsUseCase
{
    public function __construct(private MediaRepositoryInterface $mediaRepository) {}

    public function execute(int $galleryId, UserPermission $permissions): array
    {
        if (!$permissions->can('storage.media.view')) {
            throw new AccessDeniedException();
        }
        return $this->mediaRepository->getDistinctTagsByEntity('storage.gallery', $galleryId);
    }
}
