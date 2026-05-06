<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\UploadMediaData;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\ImageProcessor;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

readonly class UploadMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private ImageProcessor           $imageProcessor,
        private readonly FileStorageInterface $fileStorage,
        private string                   $disk = 'public',           // <-- можно задать в сервис-провайдере
        private string                   $uploadBasePath = 'uploads', // <-- аналогично

    ) {}

    public function execute(UploadMediaData $dto, UserPermission $permissions): MediaEntity
    {
        if (!$permissions->can('storage.media.create')) {
            throw new AccessDeniedException();
        }

        $file = $dto->file;
        $filename = Uuid::uuid4()->toString() . '.' . $file->getClientOriginalExtension();
        $basePath = $this->uploadBasePath . '/' . $dto->model_type . '/' . $dto->model_id . '/';
        $this->fileStorage->storeUploadedFile($file, $basePath, $filename, $this->disk);
        $uuid = Uuid::uuid4()->toString();

        $media = new MediaEntity(
            uuid: $uuid,
            modelType: $dto->model_type,
            modelId: $dto->model_id,
            type: $dto->type,
            fileName: $filename,
            disk: $this->disk,
            size: $file->getSize(),
            title: $dto->title,
            description: $dto->description,
            sort: $dto->sort ?? 0,
            mimeType: $file->getMimeType(),
        );

        $media = $this->mediaRepository->save($media);
        $this->imageProcessor->process($media);

        return $media;
    }
}
