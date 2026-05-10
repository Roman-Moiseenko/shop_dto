<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\DTOs\Widget\WidgetUpdateData;
use App\Modules\Content\Application\Interfaces\WidgetRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetEntity;
use App\Modules\Content\Domain\ValueObjects\WidgetCategory;
use App\Modules\Content\Domain\ValueObjects\WidgetSchema;
use App\Modules\Content\Infrastructure\Exceptions\WidgetNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

class UpdateWidgetUseCase
{
    public function __construct(private WidgetRepositoryInterface $widgetRepository) {}

    public function execute(int $id, WidgetUpdateData $dto, UserPermission $permissions): WidgetEntity
    {
        if (!$permissions->can('content.settings.edit')) {
            throw new AccessDeniedException();
        }
        $widget = $this->widgetRepository->findById($id);
        if (!$widget) {
            throw new WidgetNotFoundException('Тип виджета не найден');
        }

        $widget->name = $dto->name;
        $widget->slug = $dto->slug;
        $widget->category = new WidgetCategory($dto->category);
        $widget->schema = new WidgetSchema($dto->schema);
        $widget->description = $dto->description;

        return $this->widgetRepository->save($widget);
    }
}
