<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

final class IndexMenuItemsUseCase
{
    public function __construct(private MenuItemRepositoryInterface $itemRepository) {}

    public function execute(int $menuId, UserPermission $permissions): array
    {
        if (!$permissions->can('content.data.view')) throw new AccessDeniedException();
        return $this->itemRepository->getTree($menuId);
    }
}
