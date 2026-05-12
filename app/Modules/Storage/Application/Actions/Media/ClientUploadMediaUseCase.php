<?php

namespace App\Modules\Storage\Application\Actions\Media;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\Media\UploadMediaData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Ramsey\Uuid\Uuid;

readonly class ClientUploadMediaUseCase
{
    private const array ALLOWED_MODEL_TYPES = ['auth.client', 'review', 'claim'];
    public function __construct(
        private readonly MediaRepositoryInterface $mediaRepository,
        private readonly MediaFileService $mediaFileService,
    ) {}
    public function execute(UploadMediaData $dto, UserPermission $permissions): MediaEntity
    {
        // 1. Проверяем, что тип сущности разрешён для клиента
        if (!in_array($dto->model_type, self::ALLOWED_MODEL_TYPES, true)) {
            throw new AccessDeniedException('Недопустимый тип сущности для загрузки клиентом');
        }

        // 3. Проверяем наличие файла
        if (!$dto->file) {
            throw new \InvalidArgumentException('Файл обязателен для загрузки');
        }

        $type = new MediaType($dto->type);

        // 4. Для одиночного типа — удаляем предыдущий файл и запись
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

        $file = $dto->file;
        $uuid = Uuid::uuid4()->toString();
        $filename = $uuid . '.' . $file->getClientOriginalExtension();

        // 5. Сохраняем оригинал на защищённом диске
        $this->mediaFileService->storeOriginal(
            $file,
            $dto->model_type,
            $dto->model_id,
            $filename
        );

        // 6. Создаём доменную сущность
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: $dto->model_type,
            modelId: $dto->model_id,
            type: $type,
            fileName: $filename,
            disk: $this->mediaFileService->getOriginalDisk(),
            size: $file->getSize(),
            title: $dto->title,
            description: $dto->description,
            sort: 0,
            mimeType: $file->getMimeType(), // клиент не управляет сортировкой
        );

        // 7. Сохраняем в БД (получаем id)
        $media = $this->mediaRepository->save($media);

        // 8. Генерируем публичный кэш (основной + нарезки)
        $this->mediaFileService->generateCache($media);

        return $media;
    }
}
