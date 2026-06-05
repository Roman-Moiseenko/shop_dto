<?php

namespace App\Modules\Storage\Application\Actions\Media;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;

readonly class DeleteMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private MediaFileService $mediaFileService,
    ) {}
    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('storage.media.delete')) throw new AccessDeniedException();


        $media = $this->mediaRepository->findById($id);
        if (!$media) {
            throw new \InvalidArgumentException('Медиа не найдено');
        }

        // Удаляем все файлы (оригинал + кэш)
        $this->mediaFileService->deleteAllFiles($media);

        // Удаляем запись из БД (репозиторий также корректирует sort для галерей)
        $this->mediaRepository->delete($id);
    }
}
