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
        $model = $block->id ? ContentBlock::findOrFail($block->id) : new ContentBlock();
        $model->container_type = $block->containerType->getValue();
        $model->container_id = $block->containerId;
        $model->widget_instance_id = $block->widgetInstanceId;
        $model->sort_order = $block->sortOrder;
        $model->section = $block->section;
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
        ContentBlock::destroy($id);
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

    private function hydrate(ContentBlock $model): ContentBlockEntity
    {
        $block = new ContentBlockEntity(
            new ContainerType($model->container_type),
            $model->container_id,
            $model->widget_instance_id,
            $model->sort_order,
            $model->section,
        );
        $block->id = $model->id;
        $block->createdAt = DateTimeImmutable::createFromMutable($model->created_at);
        $block->updatedAt = DateTimeImmutable::createFromMutable($model->updated_at);
        return $block;
    }
}
