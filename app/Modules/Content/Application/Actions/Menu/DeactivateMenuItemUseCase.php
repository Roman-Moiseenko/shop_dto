<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use InvalidArgumentException;

final readonly class DeactivateMenuItemUseCase
{
    public function __construct(private MenuItemRepositoryInterface $itemRepository) {}

    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.edit')) throw new AccessDeniedException();

        $item = $this->itemRepository->findById($id);
        if (!$item) throw new InvalidArgumentException('Пункт меню не найден');

        $item->isActive = false;
        $this->itemRepository->save($item);
    }
}
