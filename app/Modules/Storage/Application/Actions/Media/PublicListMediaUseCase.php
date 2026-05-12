<?php

namespace App\Modules\Storage\Application\Actions\Media;

use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;

readonly class PublicListMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private MediaFileService         $mediaFileService,
    ) {}

    public function execute(string $modelType, int $modelId, ?string $type = null): array
    {
        $mediaList = $this->mediaRepository->listByEntity($modelType, $modelId, $type);
        $grouped = $this->groupByType($mediaList, $type);

        return $grouped;
    }

    /**
     * Группирует медиафайлы по типу и форматирует каждый элемент.
     *
     * @param array $mediaList Список MediaEntity
     * @param string|null $requestedType Тип, запрошенный клиентом (если есть)
     * @return array
     */
    private function groupByType(array $mediaList, ?string $requestedType): array
    {
        $result = [];

        // Если тип задан, гарантируем наличие ключа
        if ($requestedType) {
            $filtered = array_filter($mediaList, fn($m) => $m->type->getValue() === $requestedType);
            $result[$requestedType] = $this->formatTypeGroup($requestedType, $filtered);
        } else {
            // Группируем по типам
            $byType = [];
            foreach ($mediaList as $media) {
                $byType[$media->type->getValue()][] = $media;
            }
            foreach ($byType as $typeName => $items) {
                $formatted = $this->formatTypeGroup($typeName, $items);
                // Не добавляем пустые группы, только если тип не был указан
                if ($formatted !== null) {
                    $result[$typeName] = $formatted;
                }
            }
        }

        return $result;
    }

    /**
     * Форматирует группу медиа одного типа в зависимости от того, одиночный он или нет.
     *
     * @param string $typeName
     * @param array $items
     * @return array|array[]|null
     */
    private function formatTypeGroup(string $typeName, array $items): array|null
    {
        if (empty($items)) {
            // single → null, gallery → []
            $mediaType = new MediaType($typeName);
            return $mediaType->isSingle() ? null : [];
        }

        // Преобразуем каждый элемент в массив с URL и метаданными
        $formattedItems = array_map([$this, 'formatMediaItem'], $items);

        $mediaType = $items[0]->type; // все одного типа
        if ($mediaType->isSingle()) {
            return reset($formattedItems); // одиночный объект
        } else {
            return $formattedItems; // массив для gallery
        }
    }

    /**
     * Преобразует MediaEntity в массив с публичными ссылками.
     */
    private function formatMediaItem(MediaEntity $media): array
    {
        $this->mediaFileService->ensureCacheExists($media);

        $thumbnails = [];
        $entityThumbs = $this->mediaFileService->getThumbsConfig($media);
        foreach ($entityThumbs as $slug => $settings) {
            if ($this->mediaFileService->thumbExists($media, $slug)) {
                $thumbnails[$slug] = $this->mediaFileService->getThumbUrl($media, $slug);
            }
        }

        return [
            'uuid' => $media->uuid,
            'title' => $media->title,
            'description' => $media->description,
            'sort' => $media->sort,
            'url' => $this->mediaFileService->getCacheFullUrl($media),
            'thumbnails' => $thumbnails,
        ];
    }
}
