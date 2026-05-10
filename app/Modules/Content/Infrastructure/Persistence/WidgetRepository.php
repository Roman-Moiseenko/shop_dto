<?php

namespace App\Modules\Content\Infrastructure\Persistence;

use App\Modules\Content\Application\Interfaces\WidgetRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetEntity;
use App\Modules\Content\Domain\ValueObjects\WidgetCategory;
use App\Modules\Content\Domain\ValueObjects\WidgetSchema;
use App\Modules\Content\Infrastructure\Models\Widget;
use DateTimeImmutable;

class WidgetRepository implements WidgetRepositoryInterface
{
    public function save(WidgetEntity $widget): WidgetEntity
    {
        $model = $widget->id ? Widget::findOrFail($widget->id) : new Widget();
        $model->name = $widget->name;
        $model->slug = $widget->slug;
        $model->description = $widget->description;
        $model->category = $widget->category->getValue();
        $model->schema = $widget->schema->toArray();
        $model->save();

        return $this->hydrate($model);
    }

    public function findById(int $id): ?WidgetEntity
    {
        $model = Widget::find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): void
    {
        Widget::destroy($id);
    }

    public function all(): array
    {
        return Widget::all()->map(fn($m) => $this->hydrate($m))->all();
    }

    private function hydrate(Widget $model): WidgetEntity
    {
        $widget = new WidgetEntity(
            $model->name,
            $model->slug,
            new WidgetCategory($model->category),
            new WidgetSchema($model->schema),
            $model->description,
        );


        $widget->id = $model->id;
        $widget->createdAt = DateTimeImmutable::createFromMutable($model->created_at);
        $widget->updatedAt = DateTimeImmutable::createFromMutable($model->updated_at);

        return $widget;
    }
}
