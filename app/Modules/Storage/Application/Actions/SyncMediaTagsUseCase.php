<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\SyncMediaTagsData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Exceptions\MediaFileNotFoundException;

final readonly class SyncMediaTagsUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository
    ) {}

    public function execute(int $mediaId, SyncMediaTagsData $dto, UserPermission $permissions): void
    {
        if (!$permissions->can('storage.media.edit')) {
            throw new AccessDeniedException();
        }

        $media = $this->mediaRepository->findById($mediaId);
        if (!$media) {
            throw new MediaFileNotFoundException('Медиафайл не найден');
        }

        $this->mediaRepository->syncTags($mediaId, $dto->tagIds);
    }
}
