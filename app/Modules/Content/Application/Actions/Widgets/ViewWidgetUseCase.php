<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\Interfaces\WidgetRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetEntity;
use App\Modules\Content\Domain\Exceptions\WidgetNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

readonly class ViewWidgetUseCase
{
    public function __construct(private WidgetRepositoryInterface $widgetRepository) {}

    public function execute(int $id, UserPermission $permissions): WidgetEntity
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }
        $widget = $this->widgetRepository->findById($id);
        if (!$widget)
            throw new WidgetNotFoundException('Тип виджета не найден');

        return $widget;
    }
}
