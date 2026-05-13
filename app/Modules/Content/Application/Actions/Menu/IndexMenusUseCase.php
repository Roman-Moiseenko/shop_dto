<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\Interfaces\MenuRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;

final readonly class IndexMenusUseCase
{
    public function __construct(private MenuRepositoryInterface $menuRepository) {}

    public function execute(UserPermission $permissions): array
    {
        if (!$permissions->can('content.data.view')) throw new AccessDeniedException();
        return $this->menuRepository->all();
    }
}
