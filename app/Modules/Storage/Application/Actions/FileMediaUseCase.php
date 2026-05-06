<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Infrastructure\Exceptions\MediaFileNotFoundException;

readonly class FileMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private FileStorageInterface     $fileStorage,
    ) {}
    public function execute(string $uuid, UserPermission $permissions): string
    {
        if (!$permissions->can('storage.media.view')) throw new AccessDeniedException();

        $media = $this->mediaRepository->findByUuid($uuid);
        if (!$media) throw new \InvalidArgumentException('Медиа не найдено');
        $path = $media->getPath(); // относительный путь на диске
        $disk = $media->disk;

        if (!$this->fileStorage->exists($path, $disk)) {
            throw new MediaFileNotFoundException('Файл не найден');
        }

        return $this->fileStorage->fullPath($path, $disk);
    }
}
