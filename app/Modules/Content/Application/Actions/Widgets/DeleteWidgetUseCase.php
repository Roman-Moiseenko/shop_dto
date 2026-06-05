<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\Interfaces\WidgetRepositoryInterface;
use App\Modules\Content\Domain\Exceptions\WidgetNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

readonly class DeleteWidgetUseCase
{
    public function __construct(private WidgetRepositoryInterface $widgetRepository) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('content.settings.delete')) {
            throw new AccessDeniedException();
        }
        $widget = $this->widgetRepository->findById($id);
        if (!$widget) {
            throw new WidgetNotFoundException('Тип виджета не найден');
        }
        $this->widgetRepository->delete($id);
    }
}
