<?php

namespace App\Modules\Storage\Application\Actions\Media;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\Media\DownloadMediaData;
use App\Modules\Storage\Application\Interfaces\HttpClientInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Ramsey\Uuid\Uuid;

readonly class DownloadMediaUseCase
{

    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private MediaFileService         $mediaFileService,
        private HttpClientInterface $httpClient,

    ) {}

    public function execute(DownloadMediaData $dto, UserPermission $permissions): MediaEntity
    {
        if (!$permissions->can('storage.media.create'))
            throw new AccessDeniedException();

// 1. Загружаем файл по URL
        $response = $this->httpClient->get($dto->url);
        if (!$response->successful()) {
            throw new \InvalidArgumentException('Не удалось загрузить файл');
        }

        $content = $response->body();
        $mimeType = $response->header('Content-Type') ?? 'image/jpeg';

        $type = new MediaType($dto->type);

        // 2. Для одиночного типа — удаляем предыдущий файл и запись
        if ($type->isSingle()) {
            $existing = $this->mediaRepository->findByEntityType(
                $dto->model_type,
                $dto->model_id,
                $dto->type
            );
            if ($existing) {
                $this->mediaFileService->deleteAllFiles($existing);
                $this->mediaRepository->delete($existing->id);
            }
        }

        // 3. Генерируем имя файла и сохраняем оригинал
        $uuid = Uuid::uuid4()->toString();
        $ext = $this->guessExtension($mimeType, $dto->url);
        $filename = $uuid . '.' . $ext;

        $this->mediaFileService->storeOriginalFromContent(
            $content,
            $dto->model_type,
            $dto->model_id,
            $filename
        );

        // 4. Создаём доменную сущность
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: $dto->model_type,
            modelId: $dto->model_id,
            type: $type,
            fileName: $filename,
            disk: $this->mediaFileService->getOriginalDisk(),
            size: strlen($content),
            title: $dto->title,
            description: $dto->description,
            sort: $dto->sort ?? 0,
            mimeType: $mimeType,
        );

        // 5. Сохраняем в БД
        $media = $this->mediaRepository->save($media);

        // 6. Генерируем публичный кэш (основной + нарезки)
        $this->mediaFileService->generateCache($media);

        return $media;
    }

    /**
     * Угадать расширение файла на основе MIME-типа или URL.
     */
    private function guessExtension(string $mimeType, string $url): string
    {
        // Пробуем извлечь из URL
        $pathExt = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (!empty($pathExt) && in_array($pathExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            return $pathExt;
        }

        // Иначе определяем по MIME-типу
        return match (true) {
            str_contains($mimeType, 'jpeg') => 'jpg',
            str_contains($mimeType, 'png') => 'png',
            str_contains($mimeType, 'gif') => 'gif',
            str_contains($mimeType, 'webp') => 'webp',
            str_contains($mimeType, 'svg') => 'svg',
            default => 'jpg',
        };
    }
}
