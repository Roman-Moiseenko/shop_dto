<?php

namespace App\Modules\Content\Infrastructure\Persistence;

use App\Modules\Content\Application\Interfaces\ContentBlockRepositoryInterface;
use App\Modules\Content\Domain\Entities\ContentBlockEntity;
use App\Modules\Content\Domain\ValueObjects\ContainerType;
use App\Modules\Content\Infrastructure\Models\ContentBlock;
use DateTimeImmutable;

class ContentBlockRepository implements ContentBlockRepositoryInterface
{
    public function save(ContentBlockEntity $block): ContentBlockEntity
    {
        //Новый порядковый номер для нового блока
        if (!$block->id) {
            $maxSort = ContentBlock::where('container_type', $block->containerType->getValue())
                ->where('container_id', $block->containerId)
                ->max('sort');

            $block->sortOrder = is_null($maxSort) ? 0 : $maxSort + 1;
        }

        $model = $block->id ? ContentBlock::findOrFail($block->id) : new ContentBlock();
        $model->container_type = $block->containerType->getValue();
        $model->container_id = $block->containerId;
        $model->widget_instance_id = $block->widgetInstanceId;
        $model->sort_order = $block->sortOrder;
        $model->section = $block->section;
        $model->caption = $block->caption;
        $model->save();

        return $this->hydrate($model);
    }

    public function findById(int $id): ?ContentBlockEntity
    {
        $model = ContentBlock::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): void
    {
        $block = ContentBlock::findOrFail($id);
        $block->delete();
        $this->reorderAfterDelete($block->container_type, $block->container_id);
    }

    public function listByContainer(ContainerType $containerType, int $containerId): array
    {
        return ContentBlock::where('container_type', $containerType->getValue())
            ->where('container_id', $containerId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => $this->hydrate($m))
            ->all();
    }

    public function reorder(ContainerType $containerType, int $containerId, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            ContentBlock::where('container_type', $containerType->getValue())
                ->where('container_id', $containerId)
                ->where('id', $id)
                ->update(['sort_order' => $index]);
        }
    }
    public function updateSortOrder(int $blockId, int $newSortOrder, ContainerType $containerType, int $containerId): void
    {
        $block = ContentBlock::findOrFail($blockId);
        $oldSort = $block->sort_order;

        // Получаем все блоки этого контейнера, кроме текущего
        $blocks = ContentBlock::where('container_type', $containerType->getValue())
            ->where('container_id', $containerId)
            ->where('id', '!=', $blockId)
            ->orderBy('sort')
            ->get();

        // Удаляем старую позицию из массива
        foreach ($blocks as $item) {
            if ($item->sort_order > $oldSort) {
                $item->sort_order--;
            }
        }

        // Вставляем на новую позицию
        foreach ($blocks as $item) {
            if ($item->sort_order >= $newSortOrder) {
                $item->sort_order++;
            }
        }

        $block->sort_order = $newSortOrder;
        $block->save();

        // Сохраняем изменённые блоки
        foreach ($blocks as $item) {
            $item->save();
        }
    }
    private function hydrate(ContentBlock $model): ContentBlockEntity
    {
        $block = new ContentBlockEntity(
            new ContainerType($model->container_type),
            $model->container_id,
            $model->widget_instance_id,
            $model->sort_order,
            $model->section,
            $model->caption,
        );
        $block->id = $model->id;
        $block->createdAt = DateTimeImmutable::createFromMutable($model->created_at);
        $block->updatedAt = DateTimeImmutable::createFromMutable($model->updated_at);
        return $block;
    }

    private function reorderAfterDelete(string $containerType, int $containerId): void
    {
        $blocks = ContentBlock::where('container_type', $containerType)
            ->where('container_id', $containerId)
            ->orderBy('sort')
            ->get();

        foreach ($blocks as $index => $block) {
            $block->sort_order = $index;
            $block->save();
        }
    }
}
