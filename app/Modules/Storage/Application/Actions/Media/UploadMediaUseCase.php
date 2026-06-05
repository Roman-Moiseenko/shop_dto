<?php

namespace App\Modules\Storage\Application\Actions\Media;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\Media\UploadMediaData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\MediaFileService;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Intervention\Image\Exceptions\AnalyzerException;
use Intervention\Image\Exceptions\EncoderException;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Ramsey\Uuid\Uuid;

readonly class UploadMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private MediaFileService         $mediaFileService,
    ) {}

    /**
     * @throws InvalidArgumentException|AnalyzerException
     * @throws EncoderException
     */
    public function execute(UploadMediaData $dto, UserPermission $permissions): MediaEntity
    {
        if (!$permissions->can('storage.media.create')) throw new AccessDeniedException();
        $type = new MediaType($dto->type);
        // 1. Для одиночного типа — удаляем предыдущий файл и запись
        if ($type->isSingle()) {
            $existing = $this->mediaRepository->findByEntityType($dto->model_type, $dto->model_id, $dto->type);
            if ($existing) {
                $this->mediaFileService->deleteAllFiles($existing);
                $this->mediaRepository->delete($existing->id);
            }
        }
        $uuid = Uuid::uuid4()->toString();
        $filename = $uuid . '.' . $dto->file->getClientOriginalExtension();
        // 2. Сохраняем оригинал на private‑диске и получаем имя файла
        $this->mediaFileService->storeOriginal($dto->file, $dto->model_type, $dto->model_id, $filename);

        // 3. Сущность медиа
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: $dto->model_type,
            modelId: $dto->model_id,
            type: $type,
            fileName: $filename,
            disk: $this->mediaFileService->getOriginalDisk(),
            size: $dto->file->getSize(),
            title: $dto->title,
            description: $dto->description,
            sort: $dto->sort ?? 0,
            mimeType: $dto->file->getMimeType(),
        );

        // 4. Сохраняем в БД через репозиторий (он выставит id, обработает sort)
        $media = $this->mediaRepository->save($media);

        // 5. Генерируем публичный кэш и все нарезки
        $this->mediaFileService->generateCache($media);

        return $media;

    }
}
