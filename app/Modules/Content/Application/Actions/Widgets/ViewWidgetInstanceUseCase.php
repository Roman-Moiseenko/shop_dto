<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Content\Domain\Exceptions\WidgetInstanceNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

readonly class ViewWidgetInstanceUseCase
{
    public function __construct(private WidgetInstanceRepositoryInterface $instanceRepository) {}

    public function execute(int $id, UserPermission $permissions): WidgetInstanceEntity
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }
        $instance = $this->instanceRepository->findById($id);
        if (!$instance) {
            throw new WidgetInstanceNotFoundException('Экземпляр виджета не найден');
        }
        return $instance;
    }
}
