<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Infrastructure\Exceptions\MediaFileNotFoundException;
use Illuminate\Support\Facades\Storage;

readonly class ClientDeleteMediaUseCase
{
    private const array ALLOWED_MODEL_TYPES = ['auth.client', 'review', 'claim'];

    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private MediaFileService         $mediaFileService,
    )
    {
    }

    public function execute(string $uuid, UserPermission $permissions): void
    {
        // 1. Находим медиа по UUID
        $media = $this->mediaRepository->findByUuid($uuid);
        if (!$media) {
            throw new MediaFileNotFoundException('Медиа не найдено');
        }

        // 2. Проверяем допустимость типа сущности для клиента
        if (!in_array($media->modelType, self::ALLOWED_MODEL_TYPES, true)) {
            throw new \InvalidArgumentException('Удаление этого типа медиафайлов недоступно клиенту');
        }

        // 3. (Опционально) Проверяем, что model_id принадлежит текущему клиенту
        // Например: if ($media->modelId !== $permissions->getId()) throw new AccessDeniedException();

        // 4. Удаляем все файлы (оригинал + кэш)
        $this->mediaFileService->deleteAllFiles($media);

        // 5. Удаляем запись из БД (репозиторий также корректирует sort для галерей)
        $this->mediaRepository->delete($media->id);
    }
}
