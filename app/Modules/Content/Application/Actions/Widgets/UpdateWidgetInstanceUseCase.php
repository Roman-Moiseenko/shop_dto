<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\DTOs\Widget\WidgetInstanceData;
use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Content\Infrastructure\Exceptions\WidgetInstanceNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

class UpdateWidgetInstanceUseCase
{
    public function __construct(private WidgetInstanceRepositoryInterface $instanceRepository) {}

    public function execute(int $id, WidgetInstanceData $dto, UserPermission $permissions): WidgetInstanceEntity
    {
        if (!$permissions->can('content.data.edit')) {
            throw new AccessDeniedException();
        }

        $instance = $this->instanceRepository->findById($id);
        if (!$instance) {
            throw new WidgetInstanceNotFoundException('Экземпляр виджета не найден');
        }

        $instance->params = $dto->params;
        $instance->title = $dto->title;

        return $this->instanceRepository->save($instance);
    }
}
