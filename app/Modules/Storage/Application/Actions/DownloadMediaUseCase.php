<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\DownloadMediaData;
use App\Modules\Storage\Application\Interfaces\FileStorageInterface;
use App\Modules\Storage\Application\Interfaces\HttpClientInterface;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\ImageProcessor;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

readonly class DownloadMediaUseCase
{

    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private ImageProcessor $imageProcessor,
        private FileStorageInterface $fileStorage,
        private HttpClientInterface $httpClient,   //
        private string $disk = 'public',
        private string $uploadBasePath = 'uploads',
    ) {}

    public function execute(DownloadMediaData $dto, UserPermission $permissions): MediaEntity
    {
        if (!$permissions->can('storage.media.create')) {
            throw new AccessDeniedException();
        }

        $response = $this->httpClient->get($dto->url);
        if (!$response->successful())
            throw new \InvalidArgumentException('Не удалось загрузить файл');

        $content = $response->body();
        $mimeType = $response->header('Content-Type') ?? 'image/jpeg';
        $ext = pathinfo(parse_url($dto->url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = Uuid::uuid4()->toString() . '.' . $ext;

        $basePath = $this->uploadBasePath . '/' . $dto->model_type . '/' . $dto->model_id . '/';
        $this->fileStorage->put($basePath . $filename, $content, $this->disk);

        $media = new MediaEntity(
            uuid: Uuid::uuid4()->toString(),
            modelType: $dto->model_type,
            modelId: $dto->model_id,
            type: new MediaType($dto->type),
            fileName: $filename,
            disk: $this->disk,
            size: strlen($content),
            title: $dto->title,
            description: $dto->description,
            sort: $dto->sort ?? 0,
            mimeType: $mimeType,
        );

        $media = $this->mediaRepository->save($media);
        $this->imageProcessor->process($media);

        return $media;
    }
}
