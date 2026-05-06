<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use Illuminate\Support\Facades\Storage;

readonly class DeleteMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private FileStorageInterface $fileStorage,
    ) {}
    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('storage.media.delete')) throw new AccessDeniedException();

        $media = $this->mediaRepository->findById($id);
        if (!$media) throw new \InvalidArgumentException('Медиа не найдено');


        $this->fileStorage->deleteDirectory(dirname($media->getPath()), $media->disk);

        $this->mediaRepository->delete($id);
    }
}
