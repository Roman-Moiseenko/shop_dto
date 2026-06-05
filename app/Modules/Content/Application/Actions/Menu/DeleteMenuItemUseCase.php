<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;

readonly class DeleteMenuItemUseCase
{
    public function __construct(private MenuItemRepositoryInterface $itemRepository) {}
    public function execute(int $id, UserPermission $permissions): void
    {
        if (!$permissions->can('content.data.delete')) throw new AccessDeniedException();
        $item = $this->itemRepository->findById($id);
        if (!$item) throw new MenuNotFoundException('Пункт меню не найден');
        $this->itemRepository->delete($id); // внутри пересчитывает sort у соседей
    }
}
