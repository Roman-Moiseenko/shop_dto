<?php

namespace App\Modules\Storage\Application\Actions\Media;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;

readonly class ClearCacheUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private MediaFileService         $mediaFileService,
    ) {}

    /**
     * Очистить кэш для всех медиафайлов или только для заданной сущности.
     *
     * @param string|null $modelType Тип сущности (например, 'catalog.product')
     * @param int|null    $modelId   ID сущности
     */
    public function execute(
        UserPermission $permissions,
        ?string $modelType = null,
        ?int $modelId = null
    ): int {
        if (!$permissions->can('storage.media.delete')) throw new AccessDeniedException();


        $mediaList = $this->mediaRepository->listAll($modelType, $modelId);
        $count = 0;

        foreach ($mediaList as $media) {
            // Удаляем только кэш (основной + нарезки), оригинал не трогаем
            // Для этого добавим метод deleteCache в MediaFileService
            $this->mediaFileService->deleteCache($media);
            $count++;
        }

        return $count;
    }
}
