<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use Illuminate\Support\Facades\Storage;

readonly class DeleteMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
    ) {}
    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('storage.media.delete')) throw new AccessDeniedException();

        $media = $this->mediaRepository->findById($id);
        if (!$media) throw new \InvalidArgumentException('Медиа не найдено');

        // Удаление файлов
        Storage::disk($media->disk)->deleteDirectory(dirname($media->getPath()));
        $this->mediaRepository->delete($id);
    }
}
