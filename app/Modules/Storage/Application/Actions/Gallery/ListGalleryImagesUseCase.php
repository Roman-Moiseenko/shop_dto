<?php

namespace App\Modules\Storage\Application\Actions\Gallery;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;

class ListGalleryImagesUseCase
{
    public function __construct(private MediaRepositoryInterface $mediaRepository) {}

    public function execute(int $galleryId, array $filters, UserPermission $permissions): array
    {
        if (!$permissions->can('storage.media.view')) {
            throw new AccessDeniedException();
        }

        return $this->mediaRepository->listByEntityWithTags(
            'storage.gallery',
            $galleryId,
            $filters,
        );
    }
}
