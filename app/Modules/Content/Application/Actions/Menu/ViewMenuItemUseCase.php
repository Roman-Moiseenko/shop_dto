<?php

namespace App\Modules\Content\Application\Actions\Menu;

use App\Modules\Content\Application\Interfaces\MenuItemRepositoryInterface;
use App\Modules\Content\Domain\Entities\MenuItemEntity;
use App\Modules\Content\Infrastructure\Exceptions\MenuNotFoundException;
use App\Modules\Shared\Domain\Entities\UserPermission;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use InvalidArgumentException;

final readonly class ViewMenuItemUseCase
{
    public function __construct(private MenuItemRepositoryInterface $itemRepository) {}

    public function execute(int $id, UserPermission $permissions): MenuItemEntity
    {
        if (!$permissions->can('content.data.view')) throw new AccessDeniedException();
        $item = $this->itemRepository->findById($id);
        if (!$item) throw new MenuNotFoundException('Пункт меню не найден');
        return $item;
    }
}
