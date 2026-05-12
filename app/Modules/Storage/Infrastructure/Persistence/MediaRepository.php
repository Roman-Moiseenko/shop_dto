<?php

namespace App\Modules\Storage\Infrastructure\Persistence;

use App\Modules\Shared\Domain\Services\TransactionManagerInterface;
use App\Modules\Shared\Domain\ValueObjects\Slug;
use App\Modules\Storage\Application\Interfaces\MediaRepositoryInterface;
use App\Modules\Storage\Domain\Entities\MediaEntity;
use App\Modules\Storage\Domain\Entities\MediaTagEntity;
use App\Modules\Storage\Domain\ValueObjects\MediaType;
use App\Modules\Storage\Domain\ValueObjects\TagName;
use App\Modules\Storage\Infrastructure\Models\Media;
use App\Modules\Storage\Infrastructure\Models\MediaTag;
use DateTimeImmutable;

readonly class MediaRepository implements MediaRepositoryInterface
{
    public function __construct(
        private TransactionManagerInterface $transactionManager
    ) {}

    public function save(MediaEntity $media): MediaEntity
    {
        return $this->transactionManager->execute(function () use ($media) {
            if ($media->type->isSingle()) {
                // Для одиночных типов — удаляем старую запись (если есть) и создаём новую
                Media::where([
                    'model_type' => $media->modelType,
                    'model_id'   => $media->modelId,
                    'type'       => $media->type->getValue(),
                ])->delete();

                $model = new Media();
                $model->sort = 0;
            } else {
                // Галерея
                if ($media->id) {
                    $model = Media::findOrFail($media->id);
                    $oldSort = $model->sort;
                    if ($oldSort !== $media->sort) {
                        $this->reorderGalleryAfterChange($media->modelType, $media->modelId, $media->type->getValue(), $media->id, $oldSort, $media->sort);
                        $model->sort = $media->sort;
                    }
                } else {
                    $model = new Media();
                    $maxSort = Media::where([
                        'model_type' => $media->modelType,
                        'model_id'   => $media->modelId,
                        'type'       => $media->type->getValue(),
                    ])->max('sort');
                    $model->sort = is_null($maxSort) ? 0 : $maxSort + 1;
                }
            }

            // Заполнение полей
            $model->uuid = $media->uuid;
            $model->model_type = $media->modelType;
            $model->model_id = $media->modelId;
            $model->type = $media->type->getValue();
            $model->title = $media->title;
            $model->description = $media->description;
            $model->file_name = $media->fileName;
            $model->mime_type = $media->mimeType;
            $model->disk = $media->disk;
            $model->size = $media->size;
            $model->save();

            return $this->hydrate($model);
        });

    }

    public function findById(int $id): ?MediaEntity
    {
        $model = Media::find($id)->with('tags');
        return $model ? $this->hydrate($model) : null;
    }

    public function findByUuid(string $uuid): ?MediaEntity
    {
        $model = Media::where('uuid', $uuid)->first();
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): void
    {
        $this->transactionManager->execute(function () use ($id) {
            $media = Media::findOrFail($id);
            $type = new MediaType($media->type);

            if (!$type->isSingle()) {
                // Сдвигаем sort у последующих элементов
                Media::where([
                    'model_type' => $media->model_type,
                    'model_id'   => $media->model_id,
                    'type'       => $media->type,
                ])->where('sort', '>', $media->sort)
                    ->decrement('sort');
            }

            $media->delete();
        });
    }

    public function listByEntity(string $modelType, int $modelId, array $filters = []): array
    {
        $query = Media::with('tags')
            ->where('model_type', $modelType)
            ->where('model_id', $modelId);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['tag'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('slug', $filters['tag']);
            });
        }

        // Всегда подгружаем теги
        $query->with('tags');

        return $query->orderBy('sort')
            ->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }
    public function getDistinctTagsByEntity(string $modelType, int $modelId): array
    {
        $mediaIds = Media::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->pluck('id');

        // Получаем уникальные теги, связанные с этими медиа
        return MediaTag::whereHas('medias', function ($q) use ($mediaIds) {
            $q->whereIn('media_id', $mediaIds);
        })->orderBy('name')->get()
            ->map(fn($model) => $this->hydrateTag($model))
            ->all();
    }
    public function listAll(?string $modelType = null, ?int $modelId = null): array
    {
        $query = Media::query();

        if ($modelType) {
            $query->where('model_type', $modelType);
        }
        if ($modelId) {
            $query->where('model_id', $modelId);
        }

        return $query->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }
    public function syncTags(int $mediaId, array $tagIds): void
    {
        $media = Media::findOrFail($mediaId);
        $media->tags()->sync($tagIds);
    }

    public function getTags(int $mediaId): array
    {
        $media = Media::with('tags')->findOrFail($mediaId);
        return $media->tags->all(); // коллекция моделей MediaTag
    }
    private function hydrate(Media $model): MediaEntity
    {
        $media = new MediaEntity(
            uuid: $model->uuid,
            modelType: $model->model_type,
            modelId: $model->model_id,
            type: new MediaType($model->type),
            fileName: $model->file_name,
            disk: $model->disk,
            size: $model->size,
            title: $model->title,
            description: $model->description,
            sort: $model->sort,
            mimeType: $model->mime_type,
        );
        $media->id = $model->id;

        if ($model->relationLoaded('tags') && $model->tags->isNotEmpty()) {
            $tagEntities = $model->tags->map(function ($tagModel) {
                $tag = new MediaTagEntity(
                    new TagName($tagModel->name),
                    new Slug($tagModel->slug)
                );
                $tag->id = $tagModel->id;
                return $tag;
                // Здесь можно использовать hydrate MediaTagEntity, но для простоты создаём напрямую
            })->all();
            $media->tags = $tagEntities;
        }

        return $media;
    }
    public function findByEntityType(string $modelType, int $modelId, string $type): ?MediaEntity
    {
        $model = Media::where([
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'type'       => $type,
        ])->first();

        return $model ? $this->hydrate($model) : null;
    }

    private function reorderGalleryAfterChange(string $modelType, int $modelId, string $type, int $mediaId, int $oldSort, int $newSort): void
    {
        // Получаем все элементы галереи, кроме текущего, отсортированные по sort
        $items = Media::where([
            'model_type' => $modelType,
            'model_id'   => $modelId,
            'type'       => $type,
        ])->where('id', '!=', $mediaId)
            ->orderBy('sort')
            ->get();

        // Перестраиваем массив сортов после удаления старой позиции
        $sorts = $items->pluck('sort')->toArray();

        // Удаляем старый sort из набора (он не в элементах, но в последовательности)
        // Сдвигаем все элементы, которые были > oldSort, на -1
        foreach ($items as $item) {
            if ($item->sort > $oldSort) {
                $item->sort--;
            }
        }

        // Вставляем элемент на позицию newSort
        // Элементы с sort >= newSort сдвигаем +1
        foreach ($items as $item) {
            if ($item->sort >= $newSort) {
                $item->sort++;
            }
        }

        // Сохраняем изменения
        foreach ($items as $item) {
            $item->save();
        }
    }

    private function hydrateTag(MediaTag $model): MediaTagEntity
    {
        $tag = new MediaTagEntity(
            new TagName($model->name),
            new Slug($model->slug),
        );
        $tag->id = $model->id;
        $tag->createdAt = $model->created_at ? DateTimeImmutable::createFromMutable($model->created_at) : null;
        $tag->updatedAt = $model->updated_at ? DateTimeImmutable::createFromMutable($model->updated_at) : null;
        return $tag;
    }
}
