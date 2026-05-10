<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\DTOs\Widget\WidgetInstanceData;
use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Content\Domain\Entities\WidgetInstanceEntity;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

readonly class CreateWidgetInstanceUseCase
{
    public function __construct(private WidgetInstanceRepositoryInterface $instanceRepository) {}

    public function execute(WidgetInstanceData $dto, UserPermission $permissions): WidgetInstanceEntity
    {
        if (!$permissions->can('content.data.create')) throw new AccessDeniedException();


        $instance = new WidgetInstanceEntity(
            $dto->widget_id,
            $dto->params,
            $dto->title
        );

        return $this->instanceRepository->save($instance);
    }
}
