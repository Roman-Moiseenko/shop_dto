<?php

namespace App\Modules\Storage\Infrastructure\Persistence;

use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Infrastructure\Models\Media;

class MediaRepository implements MediaRepositoryInterface
{
    public function save(MediaEntity $media): MediaEntity
    {
        if ($media->id ?? null) {
            $model = Media::findOrFail($media->id);
        } else {
            $model = new Media();
        }

        $model->uuid = $media->uuid;
        $model->model_type = $media->modelType;
        $model->model_id = $media->modelId;
        $model->type = $media->type;
        $model->title = $media->title;
        $model->description = $media->description;
        $model->sort = $media->sort;
        $model->file_name = $media->fileName;
        $model->mime_type = $media->mimeType;
        $model->disk = $media->disk;
        $model->size = $media->size;
        $model->save();

        return $this->hydrate($model);
    }

    public function findById(int $id): ?MediaEntity
    {
        $model = Media::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findByUuid(string $uuid): ?MediaEntity
    {
        $model = Media::where('uuid', $uuid)->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): void
    {
        Media::destroy($id);
    }

    public function listByEntity(string $modelType, int $modelId, ?string $type = null): array
    {
        $query = Media::where('model_type', $modelType)
            ->where('model_id', $modelId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('sort')->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }

    private function hydrate(Media $model): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $model->uuid,
            modelType: $model->model_type,
            modelId: $model->model_id,
            type: $model->type,
            fileName: $model->file_name,
            disk: $model->disk,
            size: $model->size,
            title: $model->title,
            description: $model->description,
            sort: $model->sort,
            mimeType: $model->mime_type,
        );
        $media->id = $model->id;
        return $media;
    }
}
