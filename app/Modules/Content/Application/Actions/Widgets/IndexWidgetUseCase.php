<?php

namespace App\Modules\Content\Application\Actions\Widgets;

use App\Modules\Content\Application\Interfaces\WidgetRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

readonly class IndexWidgetUseCase
{
    public function __construct(private WidgetRepositoryInterface $widgetRepository) {}

    public function execute(UserPermission $permissions): array
    {
        if (!$permissions->can('content.data.view')) {
            throw new AccessDeniedException();
        }
        return $this->widgetRepository->all();
    }
}
