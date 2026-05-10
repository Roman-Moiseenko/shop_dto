<?php

namespace App\Modules\Content\Infrastructure\Persistence;

use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Content\Infrastructure\Models\WidgetInstance;
use DateTimeImmutable;

class WidgetInstanceRepository implements WidgetInstanceRepositoryInterface
{
    public function save(WidgetInstanceEntity $instance): WidgetInstanceEntity
    {
        $model = $instance->id ? WidgetInstance::findOrFail($instance->id) : new WidgetInstance();
        $model->widget_id = $instance->widgetId;
        $model->uuid = $instance->uuid;
        $model->title = $instance->title;
        $model->params = $instance->params;
        $model->save();

        return $this->hydrate($model);
    }

    public function findById(int $id): ?WidgetInstanceEntity
    {
        $model = WidgetInstance::with('widget')->find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function findByUuid(string $uuid): ?WidgetInstanceEntity
    {
        $model = WidgetInstance::with('widget')->where('uuid', $uuid)->first();
        return $model ? $this->hydrate($model) : null;
    }
    public function all(?int $widgetId = null): array
    {
        $query = WidgetInstance::with('widget');
        if ($widgetId !== null) {
            $query->where('widget_id', $widgetId);
        }

        return $query->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }
    public function delete(int $id): void
    {
        WidgetInstance::destroy($id);
    }

    private function hydrate(WidgetInstance $model): WidgetInstanceEntity
    {
        $instance = new WidgetInstanceEntity(
            $model->widget_id,
            $model->params ?? [],
            $model->title,
        );
        $instance->id = $model->id;
        // uuid уже сгенерирован, обновим из БД (конструктор создаёт новый)
        $instance->uuid = $model->uuid;
        $instance->createdAt = DateTimeImmutable::createFromMutable($model->created_at);
        $instance->updatedAt = DateTimeImmutable::createFromMutable($model->updated_at);
        $instance->widgetName = $model->widget->name;
        $instance->widgetSlug = $model->widget->slug;
        return $instance;
    }

    public function hydrateWidgetInstance(WidgetInstance $model): WidgetInstanceEntity
    {
        return $this->hydrate($model);
    }
}
