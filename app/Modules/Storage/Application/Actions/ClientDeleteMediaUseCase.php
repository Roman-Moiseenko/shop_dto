<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use Illuminate\Support\Facades\Storage;

readonly class ClientDeleteMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private FileStorageInterface $fileStorage,
    ) {}

    public function execute(string $uuid, UserPermission $permissions): void
    {
        $media = $this->mediaRepository->findByUuid($uuid);

        if (!$media) {
            throw new \InvalidArgumentException('Медиа не найдено');
        }

        // Только определённые типы сущностей могут удаляться клиентом
        $allowedModels = ['auth.client', 'review', 'claim'];
        if (!in_array($media->modelType, $allowedModels, true)) {
            throw new AccessDeniedException('Удаление этого типа медиафайлов недоступно клиенту');
        }


        // Здесь можно добавить проверку принадлежности model_id клиенту,
        // используя $permissions->getId() или вызов внешнего сервиса

        // Удаляем физические файлы и запись в БД
        $this->fileStorage->deleteDirectory(dirname($media->getPath()), $media->disk);

        $this->mediaRepository->delete($media->id);
    }
}
