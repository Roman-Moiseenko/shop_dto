<?php

namespace App\Modules\Storage\Application\Actions\Media;

use App\Modules\Auth\Domain\ValueObjects\RoleName;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;

class ClientListMediaUseCase
{
    private const array ALLOWED_MODEL_TYPES = ['auth.client', 'review', 'claim'];

    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaFileService $mediaFileService,
    ) {}

    /**
     * Выполнить запрос списка клиентских медиа.
     */
    public function execute(string $modelType, int $modelId, UserPermission $permissions): array
    {
        // Проверяем тип сущности
        if (!in_array($modelType, self::ALLOWED_MODEL_TYPES, true)) {
            throw new AccessDeniedException('Недопустимый тип сущности');
        }

        // Если пользователь — не менеджер (у него нет права storage.media.view),
        // то проверяем, что modelId действительно принадлежит текущему клиенту

            //TODO Клиент может смотреть только свои файлы ??

        if (!$permissions->can('storage.media.view') && !$permissions->hasRole(RoleName::CLIENT)) {
                throw new AccessDeniedException('Доступ запрещён');
            }


        $mediaList = $this->mediaRepository->listByEntity($modelType, $modelId);

        return $this->formatForResponse($mediaList);
    }

    /**
     * Форматирует список MediaEntity в массив, сгруппированный по типу.
     */
    private function formatForResponse(array $mediaList): array
    {
        $result = [];
        foreach ($mediaList as $media) {
            $this->mediaFileService->ensureCacheExists($media);

            $thumbnails = [];
            $entityThumbs = $this->mediaFileService->getThumbsConfig($media);
            foreach ($entityThumbs as $slug => $settings) {
                if ($this->mediaFileService->thumbExists($media, $slug)) {
                    $thumbnails[$slug] = $this->mediaFileService->getThumbUrl($media, $slug);
                }
            }

            $item = [
                'uuid' => $media->uuid,
                'title' => $media->title,
                'description' => $media->description,
                'url' => $this->mediaFileService->getCacheFullUrl($media),
                'thumbnails' => $thumbnails,
            ];

            $type = $media->type->getValue();
            if ($media->type->isSingle()) {
                $result[$type] = $item;
            } else {
                $result[$type][] = $item;
            }
        }

        return $result;
    }
}
