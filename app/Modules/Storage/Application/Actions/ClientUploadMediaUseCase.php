<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\UploadMediaData;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\ImageProcessor;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

readonly class ClientUploadMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private ImageProcessor           $imageProcessor,
        private readonly FileStorageInterface $fileStorage,
        private string                   $disk = 'public',
        private string                   $uploadBasePath = 'uploads',
    ) {}
    public function execute(UploadMediaData $dto, UserPermission $permissions): MediaEntity
    {
        $allowed = ['auth.client', 'review', 'claim'];
        if (!in_array($dto->model_type, $allowed)) {
            throw new AccessDeniedException('Недопустимый тип сущности');
        }
        if (!$dto->file) throw new \InvalidArgumentException('Файл обязателен для загрузки');

        // TODO: проверка, что model_id принадлежит текущему клиенту

        $file = $dto->file;
        $filename = Uuid::uuid4()->toString() . '.' . $file->getClientOriginalExtension();

        $basePath = $this->uploadBasePath. '/' . $dto->model_type . '/' . $dto->model_id . '/';
        $this->fileStorage->storeUploadedFile($file, $basePath, $filename, $this->disk);

        $uuid = Uuid::uuid4()->toString();
        $media = new MediaEntity(
            uuid: $uuid,
            modelType: $dto->model_type,
            modelId: $dto->model_id,
            type: new MediaType($dto->type),
            fileName: $filename,
            disk: $this->disk,
            size: $file->getSize(),
            title: $dto->title,
            description: $dto->description,
            sort: 0,
            mimeType: $file->getMimeType(),
        );

        $media = $this->mediaRepository->save($media);
        $this->imageProcessor->process($media);

        return $media;
    }
}
