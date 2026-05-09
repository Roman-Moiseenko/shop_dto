<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Infrastructure\Exceptions\WidgetInstanceNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

class DeleteWidgetInstanceUseCase
{
    public function __construct(private WidgetInstanceRepositoryInterface $instanceRepository) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.delete')) {
            throw new AccessDeniedException();
        }

        $instance = $this->instanceRepository->findById($id);
        if (!$instance) {
            throw new WidgetInstanceNotFoundException('Экземпляр виджета не найден');
        }

        $this->instanceRepository->delete($id);
    }
}
