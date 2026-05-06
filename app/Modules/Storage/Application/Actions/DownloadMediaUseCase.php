<?php

namespace App\Modules\Storage\Application\Actions;

use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use App\Modules\Storage\Application\DTOs\DownloadMediaData;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Application\Services\ImageProcessor;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

readonly class DownloadMediaUseCase
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private ImageProcessor           $imageProcessor
    ) {}
    public function execute(DownloadMediaData $dto, UserPermission $permissions): MediaEntity
    {
        if (!$permissions->can('storage.media.create')) {
            throw new AccessDeniedException();
        }

        $response = Http::get($dto->url);
        if (!$response->successful()) {
            throw new \InvalidArgumentException('Не удалось загрузить файл');
        }

        $content = $response->body();
        $mimeType = $response->header('Content-Type') ?? 'image/jpeg';
        $ext = Str::after($mimeType, '/') ?: 'jpg';
        $filename = Str::uuid() . '.' . $ext;
        $disk = config('storage.local.disk');
        $basePath = config('storage.local.upload_path') . '/' . $dto->model_type . '/' . $dto->model_id . '/';
        Storage::disk($disk)->put($basePath . $filename, $content);
        $uuid = Str::uuid()->toString();

        $media = new MediaEntity(
            uuid: $uuid,
            modelType: $dto->model_type,
            modelId: $dto->model_id,
            type: $dto->type,
            fileName: $filename,
            disk: $disk,
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
