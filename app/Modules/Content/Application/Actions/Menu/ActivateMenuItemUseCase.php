<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use InvalidArgumentException;

final class ActivateMenuItemUseCase
{
    public function __construct(private MenuItemRepositoryInterface $itemRepository) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.edit')) throw new AccessDeniedException();

        $item = $this->itemRepository->findById($id);
        if (!$item) throw new InvalidArgumentException('Пункт меню не найден');

        $item->isActive = true;
        $this->itemRepository->save($item);
    }
}
