<?php

namespace App\Modules\Content\Infrastructure\Persistence;

use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Domain\ValueObjects\MenuItemStyle;
use App\Modules\Content\Domain\ValueObjects\ReferenceType;
use App\Modules\Content\Infrastructure\Models\MenuItem;
use App\Modules\Shared\Application\Interfaces\TransactionManagerInterface;
use DateTimeImmutable;

class MenuItemRepository implements MenuItemRepositoryInterface
{
    public function __construct(private  readonly  TransactionManagerInterface $transactionManager,
                                private readonly WidgetInstanceRepositoryInterface $widgetInstanceRepo)
    {
    }

    public function save(MenuItemEntity $item): MenuItemEntity
    {
        return $this->transactionManager->execute(function () use ($item) {
            $model = $item->id ? MenuItem::findOrFail($item->id) : new MenuItem();

            // Автоматическая установка sort при создании (если не задан)
            if (!$item->id && $item->sort === 0) {
                $maxSort = MenuItem::where('menu_id', $item->menuId)
                    ->where('parent_id', $item->parentId)
                    ->max('sort');
                $item->sort = is_null($maxSort) ? 0 : $maxSort + 1;
            }

            $model->menu_id = $item->menuId;
            $model->parent_id = $item->parentId;
            $model->title = $item->title;
            $model->url = $item->url;
            $model->reference_type = $item->referenceType?->getValue();
            $model->reference_id = $item->referenceId;
            $model->icon_uuid = $item->iconUuid;
            $model->style = $item->style?->getValue();
            $model->target_blank = $item->targetBlank;
            $model->sort = $item->sort;
            $model->is_active = $item->isActive;
            $model->widget_instance_id = $item->widgetInstanceId;
            $model->save();

            return $this->hydrate($model);
        });
    }

    public function findById(int $id): ?MenuItemEntity
    {
        $model = MenuItem::with('children', 'widgetInstance.widget')->find($id);
        return $model ? $this->hydrate($model) : null;
    }

    public function delete(int $id): void
    {
        $this->transactionManager->execute(function () use ($id) {
            $item = MenuItem::findOrFail($id);
            $menuId = $item->menu_id;
            $parentId = $item->parent_id;
            $oldSort = $item->sort;

            $item->delete();

            // Пересчёт sort у оставшихся элементов с тем же родителем
            $this->shiftSortAfterDeletion($menuId, $parentId, $oldSort);
        });
    }

    public function listByMenu(int $menuId): array
    {
        return MenuItem::where('menu_id', $menuId)
            ->orderBy('sort')
            ->get()
            ->map(fn($model) => $this->hydrate($model))
            ->all();
    }

    public function changeParent(int $itemId, ?int $newParentId): void
    {
        $this->transactionManager->execute(function () use ($itemId, $newParentId) {
            $item = MenuItem::findOrFail($itemId);
            $oldParentId = $item->parent_id;
            $menuId = $item->menu_id;

            // Удаляем из старого родителя
            $this->shiftSortAfterDeletion($menuId, $oldParentId, $item->sort);

            // Вычисляем новый sort в конце списка нового родителя
            $maxSort = MenuItem::where('menu_id', $menuId)
                ->where('parent_id', $newParentId)
                ->max('sort');
            $newSort = is_null($maxSort) ? 0 : $maxSort + 1;

            $item->parent_id = $newParentId;
            $item->sort = $newSort;
            $item->save();
        });
    }

    public function updateSortOrder(int $itemId, int $newSort): void
    {
        $this->transactionManager->execute(function () use ($itemId, $newSort) {
            $item = MenuItem::findOrFail($itemId);
            $menuId = $item->menu_id;
            $parentId = $item->parent_id;
            $oldSort = $item->sort;

            // Сдвигаем промежуточные элементы
            if ($newSort > $oldSort) {
                MenuItem::where('menu_id', $menuId)
                    ->where('parent_id', $parentId)
                    ->where('sort', '>', $oldSort)
                    ->where('sort', '<=', $newSort)
                    ->decrement('sort');
            } elseif ($newSort < $oldSort) {
                MenuItem::where('menu_id', $menuId)
                    ->where('parent_id', $parentId)
                    ->where('sort', '>=', $newSort)
                    ->where('sort', '<', $oldSort)
                    ->increment('sort');
            }

            $item->sort = $newSort;
            $item->save();
        });
    }

    // Вспомогательный метод для пересчёта после удаления
    private function shiftSortAfterDeletion(int $menuId, ?int $parentId, int $removedSort): void
    {
        MenuItem::where('menu_id', $menuId)
            ->where('parent_id', $parentId)
            ->where('sort', '>', $removedSort)
            ->decrement('sort');
    }

    public function getTree(int $menuId): array
    {
        $allItems = MenuItem::where('menu_id', $menuId)
            ->orderBy('sort')
            ->get()
            ->map(fn($model) => $this->hydrate($model))
            ->keyBy('id');

        $tree = [];

        /** @var MenuItemEntity $item */

        foreach ($allItems as $item) {
            if ($item->parentId === null) {
                $tree[] = $item;
            } else {
                /** @var MenuItemEntity $parent */
                $parent = $allItems->get($item->parentId);
                if ($parent) {
                    $parent->addChild($item);
                } else {
                    // Если родитель не найден (например, битые данные), поместим в корень
                    $tree[] = $item;
                }
            }
        }

        return $tree;
    }

    private function hydrate(MenuItem $model): MenuItemEntity
    {
        $item = new MenuItemEntity(
            menuId: $model->menu_id,
            title: $model->title,
            parentId: $model->parent_id,
            url: $model->url,
            referenceType: $model->reference_type ? new ReferenceType($model->reference_type) : null,
            referenceId: $model->reference_id,
            iconUuid: $model->icon_uuid,
            style: $model->style ? new MenuItemStyle($model->style) : null,
            targetBlank: $model->target_blank,
            sort: $model->sort,
            isActive: $model->is_active,
            widgetInstanceId: $model->widget_instance_id,
        );
        $item->id = $model->id;
        $item->createdAt = DateTimeImmutable::createFromMutable($model->created_at);
        $item->updatedAt = DateTimeImmutable::createFromMutable($model->updated_at);

        // Дочерние элементы
        if ($model->relationLoaded('children') && $model->children->isNotEmpty()) {
            $children = $model->children->map(fn($child) => $this->hydrate($child))->all();
            $item->children = $children;
        }

        // Связанный виджет
        if ($model->relationLoaded('widgetInstance') && $model->widgetInstance && $this->widgetInstanceRepo) {
            $widgetInstanceEntity = $this->widgetInstanceRepo->hydrateWidgetInstance($model->widgetInstance);
            $item->widgetInstance = $widgetInstanceEntity;
        }

        return $item;
    }
}
