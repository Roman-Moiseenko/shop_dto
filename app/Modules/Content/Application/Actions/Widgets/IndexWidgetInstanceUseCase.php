<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\Interfaces\WidgetInstanceRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

class IndexWidgetInstanceUseCase
{
    public function __construct(private WidgetInstanceRepositoryInterface $instanceRepository) {}

    public function execute(?int $widgetId, UserPermission $permissions): array
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }
        return $this->instanceRepository->all($widgetId);
    }
}
